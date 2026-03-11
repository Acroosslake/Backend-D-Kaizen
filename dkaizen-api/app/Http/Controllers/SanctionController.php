<?php

namespace App\Http\Controllers;

use App\Models\Sanction;
use Illuminate\Http\Request;

class SanctionController extends Controller
{
    //Mostrar todas las sanciones (Con datos del cliente y la cita)
    public function index()
    {
        $sanctions = Sanction::with(['user', 'appointment'])->get();
        return response()->json($sanctions);
    }

    //Crear (Aplicar) una nueva sanción
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'appointment_id' => 'required|exists:appointments,id',
            'amount' => 'required|integer|min:0',
            'sanction_type' => 'required|in:ausencia,cancelacion_tardia,otro',
        ]);

        $sanction = Sanction::create($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $sanction
        ], 201);
    }

    //Ver una sanción específica
    public function show($id)
    {
        $sanction = Sanction::with(['user', 'appointment'])->find($id);

        if (!$sanction) {
            return response()->json(['message' => 'Sanción no encontrada'], 404);
        }

        return response()->json($sanction);
    }

    //Actualizar una sanción (ej: perdonarle la multa al cliente)
    public function update(Request $request, $id)
    {
        $sanction = Sanction::find($id);

        if (!$sanction) {
            return response()->json(['message' => 'Sanción no encontrada'], 404);
        }

        $sanction->update($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $sanction
        ]);
    }

    // 5. Eliminar una sanción
    public function destroy($id)
    {
        $sanction = Sanction::find($id);

        if (!$sanction) {
            return response()->json(['message' => 'Sanción no encontrada'], 404);
        }

        $sanction->delete();
        
        return response()->json(['message' => 'Sanción eliminada correctamente']);
    }
}