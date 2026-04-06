<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use Illuminate\Http\Request;

class BarberController extends Controller
{
    // 1. Mostrar todos los barberos (PARA EL FRONTEND)
    public function index()
    {
        // Añadimos 'specialty' y 'status' a la consulta
        // Solo enviamos los que están activos (status = 1) para que no agenden con alguien que no está
        $barbers = Barber::with(['user' => function($query) {
            $query->select('id', 'name', 'email'); 
        }])
        ->where('status', true) // 👈 Solo barberos activos
        ->get(['id', 'user_id', 'specialty', 'status', 'entry_time', 'exit_time']);

        return response()->json($barbers);
    }

    // 2. Contratar (Crear) un nuevo barbero (Solo Admin)
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|unique:barbers,user_id',
            'rh' => 'required|string|max:3',
            'eps' => 'required|string|max:30',
            'specialty' => 'nullable|string', // 👈 Agregamos validación para especialidad
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

    // 3. Ver el perfil de un solo barbero
    public function show($id)
    {
        $barber = Barber::with(['user' => function($query) {
            $query->select('id', 'name', 'email');
        }])->find($id);

        if (!$barber) {
            return response()->json(['message' => 'Barbero no encontrado'], 404);
        }

        return response()->json($barber);
    }

    // ... (update y destroy se mantienen igual)
}