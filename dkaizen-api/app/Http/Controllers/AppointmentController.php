<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * 2. CREAR CITA (Captura el precio actual para el Dashboard)
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_id'       => 'required|exists:services,id',
            'barber_id'        => 'required|exists:barbers,id',
            'appointment_date' => 'required|date|after:now',
            'notes'            => 'nullable|string|max:500',
        ]);

        $service = Service::findOrFail($request->service_id);

        $appointment = Appointment::create([
            'user_id'          => auth('api')->id(),
            'service_id'       => $request->service_id,
            'barber_id'        => $request->barber_id,
            'appointment_date' => $request->appointment_date,
            'total_price'      => $service->price, // ✅ Precio congelado al momento de reservar
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
     * 3. VER DETALLE
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
     * 4. ACTUALIZAR (Sincronización crítica de Ingresos)
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::with('service')->findOrFail($id);
        $user = auth('api')->user();

        // 🛡️ Seguridad básica
        if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
            return response()->json(['message' => 'No tienes permiso.'], 403);
        }

        // Lógica de Admin: Completar o cancelar
        if ($user->role === 'admin') {
            // ✅ Si se completa, nos aseguramos que el precio esté actualizado para las Stats
            if ($request->status === 'completed') {
                $appointment->total_price = $appointment->service->price;
            }
            $appointment->update($request->all());
        } else {
            // Lógica de Cliente: Solo fecha y notas
            $request->validate([
                'appointment_date' => 'sometimes|date|after:now',
                'notes'            => 'nullable|string',
            ]);
            $appointment->update($request->only(['appointment_date', 'notes']));
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Cita actualizada correctamente',
            'data'    => $appointment->load(['service', 'barber.user'])
        ]);
    }

    /**
     * 5. NO ASISTIÓ (Cita + Multa en una sola acción)
     */
public function noShow(Request $request, $id)
{
    try {
        // 1. Buscamos la cita. Usamos findOrFail para que si no existe de un error claro.
        $appointment = Appointment::with('user')->findOrFail($id);

        // 2. Cambiamos el estado de la cita
        $appointment->status = 'no-show';
        $appointment->save();

        // 3. Aplicamos la multa al usuario vinculado
        if ($appointment->user) {
            $user = $appointment->user;
            
            // Convertimos a float para asegurar que la suma matemática sea correcta
            $montoMulta = (float) $request->input('penalty_fee', 0);
            $deudaActual = (float) ($user->penalty_fee ?? 0);

            if ($montoMulta > 0) {
                $user->penalty_fee = $deudaActual + $montoMulta;
                $user->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Inasistencia procesada y multa aplicada.'
        ]);

    } catch (\Exception $e) {
        // ⚠️ Si falla, esto nos devolverá el error real en la consola de Chrome
        return response()->json([
            'success' => false,
            'message' => 'Error interno: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * 6. ELIMINAR
     */
    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $user = auth('api')->user();

        if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
            return response()->json(['message' => 'No puedes cancelar esta cita.'], 403);
        }

        $appointment->delete();
        return response()->json(['message' => 'Cita eliminada correctamente']);
    }
}