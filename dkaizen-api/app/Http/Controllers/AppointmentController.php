<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * 1. LISTAR CITAS (Filtra por Rol y por Barbero)
     */
    public function index(Request $request)
    {
        $user = auth('api')->user();
        $query = Appointment::with(['user', 'service', 'barber.user']);

        if ($user->role === 'admin') {
            if ($request->has('barber_id')) {
                $query->where('barber_id', $request->barber_id);
            }
        } elseif ($user->role === 'barber') {
            $barber = $user->barber;
            if (!$barber) {
                return response()->json(['message' => 'Usuario no vinculado a barbería.'], 403);
            }
            $query->where('barber_id', $barber->id);
        } else {
            $query->where('user_id', $user->id);
        }

        return response()->json($query->orderBy('appointment_date', 'desc')->get());
    }

    /**
     * 2. DISPONIBILIDAD REAL (Evita choques de horarios)
     * Este es el que llamaremos desde Reservas.jsx
     */
    public function getOccupiedSlots(Request $request)
    {
        $request->validate([
            'barber_id' => 'required|exists:barbers,id',
            'date'      => 'required|date_format:Y-m-d'
        ]);

        // Buscamos citas que no estén canceladas para ese barbero y ese día
        $occupied = Appointment::where('barber_id', $request->barber_id)
            ->whereDate('appointment_date', $request->date)
            ->whereNotIn('status', ['cancelled', 'no-show'])
            ->get()
            ->map(function($app) {
                // Devolvemos solo la hora en formato HH:mm (ej: 14:00)
                return Carbon::parse($app->appointment_date)->format('H:i');
            });

        return response()->json($occupied);
    }

    /**
     * 3. CREAR CITA (Con validación de choque de horario)
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_id'       => 'required|exists:services,id',
            'barber_id'        => 'required|exists:barbers,id',
            'appointment_date' => 'required|date|after:now',
            'notes'            => 'nullable|string|max:500',
        ]);

        // 🛡️ VALIDACIÓN CRÍTICA: ¿El barbero ya tiene alguien a esa misma hora?
        $exists = Appointment::where('barber_id', $request->barber_id)
            ->where('appointment_date', $request->appointment_date)
            ->whereNotIn('status', ['cancelled', 'no-show'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Este horario acaba de ser tomado por otro cliente. Por favor elige otro.'
            ], 422);
        }

        $service = Service::findOrFail($request->service_id);

        $appointment = Appointment::create([
            'user_id'          => auth('api')->id(),
            'service_id'       => $request->service_id,
            'barber_id'        => $request->barber_id,
            'appointment_date' => $request->appointment_date,
            'total_price'      => $service->price, 
            'notes'            => $request->notes,
            'status'           => 'pending', 
        ]);

        return response()->json([
            'success' => true,
            'message' => '¡Cita reservada con éxito!',
            'data'    => $appointment->load(['service', 'barber.user'])
        ], 201);
    }

    /**
     * 4. VER DETALLE
     */
    public function show($id)
    {
        $appointment = Appointment::with(['user', 'service', 'barber.user'])->findOrFail($id);
        $user = auth('api')->user();
        
        if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
            return response()->json(['message' => 'Acceso denegado.'], 403);
        }

        return response()->json($appointment);
    }

    /**
     * 5. ACTUALIZAR (Ingresos y Limpieza de Deuda)
     */
    public function update(Request $request, $id)
    {
        // ✅ Traemos el usuario con la cita para poder limpiarle la deuda
        $appointment = Appointment::with(['service', 'user'])->findOrFail($id);
        $user = auth('api')->user();

        if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
            return response()->json(['message' => 'No tienes permiso.'], 403);
        }

        if ($user->role === 'admin') {
            if ($request->status === 'completed') {
                $appointment->total_price = $appointment->service->price;
                
                // ✅ LÓGICA DE DEUDA: Si el admin envía 'clear_debt' = true, limpiamos la multa
                if ($request->clear_debt && $appointment->user) {
                    $appointment->user->penalty_fee = 0;
                    $appointment->user->save();
                }
            }
            // Actualizamos la cita normalmente
            $appointment->update($request->all());
        } else {
            $request->validate([
                'appointment_date' => 'sometimes|date|after:now',
                'notes'            => 'nullable|string',
                'status'           => 'sometimes|string|in:cancelled'
            ]);
            $appointment->update($request->only(['appointment_date', 'notes', 'status']));
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Cita actualizada correctamente',
            'data'    => $appointment->load(['service', 'barber.user'])
        ]);
    }

    /**
     * 6. NO ASISTIÓ
     */
    public function noShow(Request $request, $id)
    {
        try {
            $appointment = Appointment::with('user')->findOrFail($id);
            $appointment->status = 'no-show';
            $appointment->save();

            if ($appointment->user) {
                $user = $appointment->user;
                $montoMulta = (float) $request->input('penalty_fee', 0);
                $deudaActual = (float) ($user->penalty_fee ?? 0);

                if ($montoMulta > 0) {
                    $user->penalty_fee = $deudaActual + $montoMulta;
                    $user->save();
                }
            }

            return response()->json(['success' => true, 'message' => 'Multa aplicada.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 7. ELIMINAR / CANCELAR
     */
    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $user = auth('api')->user();

        // Solo el dueño o el admin pueden borrar
        if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $appointment->delete();
        return response()->json(['message' => 'Cita eliminada correctamente']);
    }
}