<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    /**
     * 1. LISTAR CITAS (Filtrado por Rol)
     * Ordenadas por fecha para que las más recientes aparezcan primero.
     */
    public function index()
    {
        $user = auth('api')->user();

        if ($user->role === 'admin') {
            // El admin ve todo el panorama de la barbería
            $appointments = Appointment::with(['user', 'service', 'barber.user'])
                ->orderBy('appointment_date', 'asc')
                ->get();
        } else {
            // El cliente solo ve su historial personal
            $appointments = Appointment::with(['service', 'barber.user'])
                ->where('user_id', $user->id)
                ->orderBy('appointment_date', 'asc')
                ->get();
        }

        return response()->json($appointments);
    }

    /**
     * 2. CREAR CITA (Seguridad nivel Master)
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_id'       => 'required|exists:services,id',
            'barber_id'        => 'required|exists:barbers,id',
            'appointment_date' => 'required|date|after:now', // Evita viajes al pasado
            'notes'            => 'nullable|string|max:500',
        ]);

        // Creamos la cita forzando el user_id del token y el status inicial
        $appointment = Appointment::create([
            'user_id'          => auth('api')->id(),
            'service_id'       => $request->service_id,
            'barber_id'        => $request->barber_id,
            'appointment_date' => $request->appointment_date,
            'notes'            => $request->notes,
            'status'           => 'pending', 
        ]);

        // Cargamos las relaciones para que el Frontend pueda mostrar: 
        // "Cita con Julian V. para Corte Premium confirmada"
        $appointment->load(['service', 'barber.user']);
        
        return response()->json([
            'success' => true,
            'message' => '¡Tu cita ha sido reservada con éxito en D\'Kaizen!',
            'data'    => $appointment
        ], 201);
    }

    /**
     * 3. VER DETALLE (Blindado)
     */
    public function show($id)
    {
        $appointment = Appointment::with(['user', 'service', 'barber.user'])->find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $user = auth('api')->user();
        
        // Verificación de propiedad: Solo tú o el admin pueden ver el detalle
        if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
            return response()->json(['message' => 'Acceso denegado.'], 403);
        }

        return response()->json($appointment);
    }

    /**
     * 4. ACTUALIZAR (Con lógica de negocio)
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $user = auth('api')->user();

        // Solo el dueño o el admin editan
        if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
            return response()->json(['message' => 'No tienes permiso para modificar esta cita.'], 403);
        }

        // Si es cliente, solo permitimos cambiar fecha/hora o notas. 
        // El status solo lo debería cambiar el Admin (confirmar/completar).
        if ($user->role !== 'admin') {
            $request->validate([
                'appointment_date' => 'sometimes|date|after:now',
                'notes'            => 'nullable|string',
            ]);
            $appointment->update($request->only(['appointment_date', 'notes']));
        } else {
            // El admin puede cambiar todo, incluyendo el status
            $appointment->update($request->all());
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Cita actualizada correctamente',
            'data'    => $appointment->load(['service', 'barber.user'])
        ]);
    }

    /**
     * 5. CANCELAR / ELIMINAR
     */
    public function destroy($id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $user = auth('api')->user();

        if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
            return response()->json(['message' => 'No puedes cancelar esta cita.'], 403);
        }

        // Podrías optar por un borrado lógico (cambiar status a 'canceled') 
        // o borrado físico (delete) como tienes aquí:
        $appointment->delete();
        
        return response()->json(['message' => 'Cita eliminada correctamente']);
    }
}