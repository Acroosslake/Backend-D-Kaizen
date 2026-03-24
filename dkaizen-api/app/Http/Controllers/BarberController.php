<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use Illuminate\Http\Request;

class BarberController extends Controller
{
    // 1. Mostrar todos los barberos (VERSIÓN SEGURA PÚBLICA)
    public function index()
    {
        // Solo traemos el ID, horario, y los datos básicos del usuario vinculado.
        // Ocultamos contratos, EPS y RH por privacidad.
        $barbers = Barber::with(['user' => function($query) {
            $query->select('id', 'name', 'email'); 
        }])->get(['id', 'user_id', 'entry_time', 'exit_time']);

        return response()->json($barbers);
    }

    // 2. Contratar (Crear) un nuevo barbero (Solo Admin)
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|unique:barbers,user_id',
            'rh' => 'required|string|max:3',
            'eps' => 'required|string|max:30',
            'contract_type' => 'required|in:fijo,temporal,prestacion',
            'entry_time' => 'required',
            'exit_time' => 'required',
        ]);

        $barber = Barber::create($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Barbero registrado exitosamente en el equipo',
            'data' => $barber
        ], 201);
    }

    // 3. Ver el perfil de un solo barbero (VERSIÓN SEGURA PÚBLICA)
    public function show($id)
    {
        // Misma protección que en el index
        $barber = Barber::with(['user' => function($query) {
            $query->select('id', 'name', 'email');
        }])->find($id);

        if (!$barber) {
            return response()->json(['message' => 'Barbero no encontrado'], 404);
        }

        return response()->json([
            'id' => $barber->id,
            'user' => $barber->user,
            'entry_time' => $barber->entry_time,
            'exit_time' => $barber->exit_time
        ]);
    }

    // 4. Actualizar datos (Solo Admin)
    public function update(Request $request, $id)
    {
        $barber = Barber::find($id);

        if (!$barber) {
            return response()->json(['message' => 'Barbero no encontrado'], 404);
        }

        $barber->update($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Datos del barbero actualizados',
            'data' => $barber
        ]);
    }

    // 5. Despedir (Eliminar) un barbero (Solo Admin)
    public function destroy($id)
    {
        $barber = Barber::find($id);

        if (!$barber) {
            return response()->json(['message' => 'Barbero no encontrado'], 404);
        }

        $barber->delete();
        
        return response()->json(['message' => 'Barbero eliminado correctamente del sistema']);
    }
}