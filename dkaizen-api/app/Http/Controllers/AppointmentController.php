<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    // 1. Mostrar todas las citas (Con los datos del cliente y el servicio)
    public function index()
    {
        $appointments = Appointment::with(['user', 'service'])->get();
        return response()->json($appointments);
    }

    // 2. Crear una nueva cita
    public function store(Request $request)
    {
        // Validamos que el usuario y el servicio de verdad existan en la base de datos
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date',
        ]);

        $appointment = Appointment::create($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $appointment
        ], 201);
    }

    // 3. Mostrar una sola cita específica
    public function show($id)
    {
        $appointment = Appointment::with(['user', 'service'])->find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        return response()->json($appointment);
    }

    // 4. Actualizar una cita (ej: cambiar la fecha o el estado)
    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $appointment->update($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $appointment
        ]);
    }

    // 5. Eliminar una cita (Cancelar)
    public function destroy($id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $appointment->delete();
        
        return response()->json(['message' => 'Cita cancelada correctamente']);
    }
}