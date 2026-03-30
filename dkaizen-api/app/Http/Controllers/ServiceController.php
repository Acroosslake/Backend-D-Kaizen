<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service; 
use Illuminate\Http\Request;

class ServiceController extends Controller


{   //GET: Lista todos los servicios
    public function index()
    {
        return response()->json(Service::all(), 200);
    }

        //POST: Crea un nuevo servicio
    public function store(Request $request)
    {
        // Validación básica profesional
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer', // duración en minutos
        ]);

        $service = Service::create($validated);
        return response()->json([
            'message' => 'Servicio creado con éxito',
            'data' => $service
        ], 21);
    }
     //GET: Muestra un solo servicio por su ID
    public function show(string $id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Servicio no encontrado'], 404);
        }
        return response()->json($service, 200);
    }
    }
    
    //PUT/PATCH: Actualiza un servicio
    public function update(Request $request, string $id)
    {
        $service = Service::findOrFail($id);
        $service->update($request->all());
        return response()->json(['message' => 'Servicio actualizado', 'data' => $service], 200);
    }

      // DELETE: Borra un servicio
    public function destroy(string $id)
    {
        Service::findOrFail($id)->delete();
        return response()->json(['message' => 'Servicio eliminado'], 200);
    }
}