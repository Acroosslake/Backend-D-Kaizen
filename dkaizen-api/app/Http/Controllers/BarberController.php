<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use Illuminate\Http\Request;

class BarberController extends Controller
{
    // 1. Mostrar todos los barberos (con sus datos personales de Usuario)
    public function index()
    {
        $barbers = Barber::with('user')->get();
        return response()->json($barbers);
    }

    // 2. Contratar (Crear) un nuevo barbero
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
            'data' => $barber
        ], 201);
    }

    // 3. Ver el perfil de un solo barbero
    public function show($id)
    {
        $barber = Barber::with('user')->find($id);

        if (!$barber) {
            return response()->json(['message' => 'Barbero no encontrado'], 404);
        }

        return response()->json($barber);
    }

    // 4. Actualizar datos (ej: le cambiaron el turno o la EPS)
    public function update(Request $request, $id)
    {
        $barber = Barber::find($id);

        if (!$barber) {
            return response()->json(['message' => 'Barbero no encontrado'], 404);
        }

        $barber->update($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $barber
        ]);
    }

    // 5. Despedir (Eliminar) un barbero
    public function destroy($id)
    {
        $barber = Barber::find($id);

        if (!$barber) {
            return response()->json(['message' => 'Barbero no encontrado'], 404);
        }

        $barber->delete();
        
        return response()->json(['message' => 'Barbero eliminado correctamente']);
    }
}