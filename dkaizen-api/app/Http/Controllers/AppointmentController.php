<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    // 1. Mostrar las citas (Inteligente según el rol)
    public function index()
    {
        $user = auth('api')->user();

        if ($user->role === 'admin') {
            // El admin ve TODAS las citas con los datos completos
            $appointments = Appointment::with(['user', 'service', 'barber.user'])->get();
        } else {
            // El cliente SOLO ve sus propias citas
            $appointments = Appointment::with(['service', 'barber.user'])
                                       ->where('user_id', $user->id)
                                       ->get();
        }

        return response()->json($appointments);
    }

    // 2. Crear una nueva cita (Segura contra suplantación)
    public function store(Request $request)
    {
        // Ya no pedimos el user_id en el validador
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'barber_id' => 'required|exists:barbers,id',
            'appointment_date' => 'required|date|after:now', // No permitir fechas pasadas
        ]);

        $data = $request->all();
        // ¡Magia de seguridad! Forzamos el ID del usuario que tiene la sesión iniciada
        $data['user_id'] = auth('api')->id();
        $data['status'] = 'pending'; // Estado inicial

        $appointment = Appointment::create($data);
        
        return response()->json([
            'success' => true,
            'message' => '¡Tu cita ha sido reservada con éxito!',
            'data' => $appointment
        ], 201);
    }

    // 3. Mostrar una cita específica (Con blindaje de propiedad)
    public function show($id)
    {
        $appointment = Appointment::with(['user', 'service', 'barber.user'])->find($id);

        if (!$appointment) return response()->json(['message' => 'Cita no encontrada'], 404);

        $user = auth('api')->user();
        // Si no es admin y la cita no es suya, lo rebotamos
        if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
            return response()->json(['message' => 'Acceso denegado. Esta cita no te pertenece.'], 403);
        }

        return response()->json($appointment);
    }

    // 4. Actualizar una cita (Con blindaje de propiedad)
    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) return response()->json(['message' => 'Cita no encontrada'], 404);

        $user = auth('api')->user();
        if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
            return response()->json(['message' => 'Acceso denegado. No puedes modificar esta cita.'], 403);
        }

        $appointment->update($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Cita actualizada correctamente',
            'data' => $appointment
        ]);
    }

    // 5. Eliminar (Cancelar) una cita (Con blindaje de propiedad)
    public function destroy($id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) return response()->json(['message' => 'Cita no encontrada'], 404);

        $user = auth('api')->user();
        if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
            return response()->json(['message' => 'Acceso denegado. No puedes cancelar esta cita.'], 403);
        }

        $appointment->delete();
        
        return response()->json(['message' => 'Cita cancelada correctamente']);
    }
}