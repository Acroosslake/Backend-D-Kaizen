<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BarberController extends Controller
{
    /**
     * 1. Mostrar todos los barberos (PARA EL FRONTEND)
     * He añadido un try-catch para que si falla, te diga exactamente por qué.
     */
    public function index()
    {
        try {
            // Traemos los barberos con su usuario. 
            // Quitamos el filtro 'where status' momentáneamente para asegurar que cargue algo.
            $barbers = Barber::with(['user' => function($query) {
                $query->select('id', 'name', 'email'); 
            }])->get();

            return response()->json($barbers);
        } catch (\Exception $e) {
            // Si esto falla, verás el error real en la consola de Chrome (Network)
            return response()->json([
                'message' => 'Error al obtener barberos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 2. Contratar (Crear) un nuevo barbero (Solo Admin)
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id|unique:barbers,user_id',
                'rh' => 'required|string|max:3',
                'eps' => 'required|string|max:30',
                'specialty' => 'nullable|string',
                'contract_type' => 'required|in:fijo,temporal,prestacion',
                'entry_time' => 'required',
                'exit_time' => 'required',
            ]);

            $barber = Barber::create($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Barbero registrado exitosamente',
                'data' => $barber
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * 3. Ver el perfil de un solo barbero
     */
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

    /**
     * 4. Actualizar datos (Solo Admin)
     */
    public function update(Request $request, $id)
    {
        $barber = Barber::find($id);

        if (!$barber) {
            return response()->json(['message' => 'Barbero no encontrado'], 404);
        }

        $barber->update($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Datos actualizados',
            'data' => $barber
        ]);
    }

    /**
     * 5. Eliminar barbero (Solo Admin)
     */
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