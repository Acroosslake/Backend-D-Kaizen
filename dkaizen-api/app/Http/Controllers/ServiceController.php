<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    // 1. GET: Lista todos los servicios
    public function index()
    {
        return response()->json(Service::all());
    }

    // 2. POST: Crea un nuevo servicio
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer', // Minutos
            'price' => 'required|numeric',
            'status' => 'in:Activo,Inactivo'
        ]);

        $service = Service::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Servicio creado exitosamente',
            'data' => $service
        ], 201);
    }

    // 3. GET: Muestra un solo servicio por su ID
    public function show($id)
    {
        $service = Service::find($id);
        if (!$service) return response()->json(['message' => 'No encontrado'], 404);
        
        return response()->json($service);
    }

    // 4. PUT/PATCH: Actualiza un servicio
    public function update(Request $request, $id)
    {
        $service = Service::find($id);
        if (!$service) return response()->json(['message' => 'No encontrado'], 404);

        $service->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Servicio actualizado',
            'data' => $service
        ]);
    }

    // 5. DELETE: Borra un servicio
    public function destroy($id)
    {
        $service = Service::find($id);
        if (!$service) return response()->json(['message' => 'No encontrado'], 404);

        $service->delete();
        return response()->json(['success' => true, 'message' => 'Servicio eliminado']);
    }
}