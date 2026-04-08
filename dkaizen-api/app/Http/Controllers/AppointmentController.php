<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Barber; // Asegúrate de importar Barber
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    /**
     * 1. LISTAR CITAS (Filtra por Rol y por Barbero)
     */
    public function index(Request $request) // Añade Request para leer los filtros
    {
        $user = auth('api')->user();

        // Iniciamos la consulta base
        $query = Appointment::with(['user', 'service', 'barber.user']);

        // FILTRO DE SEGURIDAD POR ROL
        if ($user->role === 'admin') {
            // El admin ve todo, pero aquí podemos aplicar filtros adicionales
            // si el Admin quiere ver la agenda de UN barbero específico.
        } elseif ($user->role === 'barber') {
            // El barbero solo ve sus propias citas.
            // Para esto necesitamos saber cuál es el barber_id asociado al user_id
            $barber = $user->barber;
            if (!$barber) {
                return response()->json(['message' => 'Tu usuario no está vinculado a una barbería.'], 403);
            }
            $query->where('barber_id', $barber->id);
        } else {
            // El cliente solo ve sus propias citas
            $query->where('user_id', $user->id);
        }

        // NUEVO FILTRO PARA LA VISTA DEL ADMIN (Ver citas de un barbero específico)
        if ($request->has('barber_id')) {
            $query->where('barber_id', $request->barber_id);
        }

        // Ordenamos y obtenemos los datos
        $appointments = $query->orderBy('appointment_date', 'desc')->get();

        return response()->json($appointments);
    }

    /**
     * 2. CREAR CITA (No cambia)
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_id'       => 'required|exists:services,id',
            'barber_id'        => 'required|exists:barbers,id',
            'appointment_date' => 'required|date|after:now',
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
            'message' => '¡Tu cita ha sido reservada con éxito en D\'Kaizen!',
            'data'    => $appointment
        ], 201);
    }

    /**
     * 3. VER DETALLE (No cambia)
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
     * 4. ACTUALIZAR (No cambia, el admin puede cambiar el status a 'completed')
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $user = auth('api')->user();

        if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
            return response()->json(['message' => 'No tienes permiso.'], 403);
        }

        if ($user->role !== 'admin') {
            $request->validate([
                'appointment_date' => 'sometimes|date|after:now',
                'notes'            => 'nullable|string',
            ]);
            $appointment->update($request->only(['appointment_date', 'notes']));
        } else {
            // El admin puede cambiar el status a 'completed' (visto en imagen 5 y 6)
            $appointment->update($request->all());
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Cita actualizada correctamente',
            'data'    => $appointment->load(['service', 'barber.user'])
        ]);
    }

    /**
     * 5. ELIMINAR (No cambia)
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

        $appointment->delete();
        
        return response()->json(['message' => 'Cita eliminada correctamente']);
    }
}