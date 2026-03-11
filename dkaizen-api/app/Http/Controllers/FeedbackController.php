<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    //Mostrar todas las reseñas (Con los datos del Cliente y el Barbero)
    public function index()
    {
        $feedbacks = Feedback::with(['user', 'barber'])->get();
        return response()->json($feedbacks);
    }

    //Crear una nueva reseña
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'barber_id' => 'required|exists:barbers,id',
            'comments' => 'nullable|string',
            'rating' => 'required|in:1,2,3,4,5',
        ]);

        $feedback = Feedback::create($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $feedback
        ], 201);
    }

    //Ver una reseña específica
    public function show($id)
    {
        $feedback = Feedback::with(['user', 'barber'])->find($id);

        if (!$feedback) {
            return response()->json(['message' => 'Reseña no encontrada'], 404);
        }

        return response()->json($feedback);
    }

    //Actualizar una reseña (ej: el cliente se arrepintió y le bajó las estrellas)
    public function update(Request $request, $id)
    {
        $feedback = Feedback::find($id);

        if (!$feedback) {
            return response()->json(['message' => 'Reseña no encontrada'], 404);
        }

        $feedback->update($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $feedback
        ]);
    }

    //Eliminar una reseña (Moderación)
    public function destroy($id)
    {
        $feedback = Feedback::find($id);

        if (!$feedback) {
            return response()->json(['message' => 'Reseña no encontrada'], 404);
        }

        $feedback->delete();
        
        return response()->json(['message' => 'Reseña eliminada correctamente']);
    }
}
}
