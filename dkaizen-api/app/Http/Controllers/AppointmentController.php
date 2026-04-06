<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    /**
     * 1. LISTAR CITAS (Inteligente: Filtra por Rol)
     */
    public function index()
    {
        $user = auth('api')->user();

        // Iniciamos la consulta con las relaciones necesarias
        $query = Appointment::with(['user', 'service', 'barber.user']);

        if ($user->role === 'admin') {
            // El admin ve TODO el panorama de D'Kaizen
            $appointments = $query->orderBy('appointment_date', 'desc')->get();
        } else {
            // El cliente SOLO ve su historial personal
            $appointments = $query->where('user_id', $user->id)
                                  ->orderBy('appointment_date', 'desc')
                                  ->get();
        }

        return response()->json($appointments);
    }

    /**
     * 2. CREAR CITA
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_id'       => 'required|exists:services,id',
            'barber_id'        => 'required|exists:barbers,id',
            'appointment_date' => 'required|date|after:now', // Sin viajes al pasado
            'notes'            => 'nullable|string|max:500',
        ]);

        $appointment = Appointment::create([
            'user_id'          => auth('api')->id(),
            'service_id'       => $request->service_id,
            'barber_id'        => $request->barber_id,
            'appointment_date' => $request->appointment_date,
            'notes'            => $request->notes,
            'status'           => 'pending', 
        ]);

        $appointment->load(['service', 'barber.user']);
        
        return response()->json([
            'success' => true,
            'message' => '¡Cita reservada con éxito en D\'Kaizen!',
            'data'    => $appointment
        ], 201);
    }

    /**
     * 3. VER DETALLE (Con protección)
     */
    public function show($id)
    {
        $appointment = Appointment::with(['user', 'service', 'barber.user'])->find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $user = auth('api')->user();
        
        if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
            return response()->json(['message' => 'Acceso denegado.'], 403);
        }

        return response()->json($appointment);
    }

    /**
     * 4. ACTUALIZAR (Lógica de Admin vs Cliente)
     * Aquí es donde el botón "Completar" hace su magia.
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $user = auth('api')->user();

        // Seguridad: ¿Es el dueño o es el Admin?
        if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
            return response()->json(['message' => 'No tienes permiso.'], 403);
        }

        if ($user->role !== 'admin') {
            // El cliente solo cambia fecha o notas
            $request->validate([
                'appointment_date' => 'sometimes|date|after:now',
                'notes'            => 'nullable|string',
            ]);
            $appointment->update($request->only(['appointment_date', 'notes']));
        } else {
            // El admin puede cambiar todo (incluyendo status: 'completed', 'canceled')
            $appointment->update($request->all());
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Cita actualizada correctamente',
            'data'    => $appointment->load(['service', 'barber.user'])
        ]);
    }

    /**
     * 5. ELIMINAR
     */
    public function destroy($id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $user = auth('api')->user();

        if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
            return response()->json(['message' => 'No puedes eliminar esta cita.'], 403);
        }

        $appointment->delete();
        
        return response()->json(['message' => 'Cita eliminada correctamente']);
    }
}