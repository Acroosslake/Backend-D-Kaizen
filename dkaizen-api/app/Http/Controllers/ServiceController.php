<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * 1. LISTAR SERVICIOS (El que usa Reservas.jsx)
     */
    public function index()
    {
        // Traemos todos los campos necesarios. 
        // Es VITAL que incluya 'status' para que el filtro de React funcione.
        $services = Service::all(); 

        return response()->json($services);
    }

    /**
     * 2. CREAR SERVICIO
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'duration'    => 'required|integer', 
            'status'      => 'boolean',
            'image'       => 'nullable|string', 
        ]);

        // Aseguramos que el status sea booleano real
        $data = $request->all();
        if (!isset($data['status'])) {
            $data['status'] = true;
        }

        $service = Service::create($data);
        
        return response()->json([
            'success' => true,
            'data'    => $service
        ], 201); 
    }

    /**
     * 3. MOSTRAR UN SERVICIO
     */
    public function show(string $id)
    {
        $service = Service::find($id);
        if (!$service) return response()->json(['message' => 'No encontrado'], 404);
        return response()->json($service);
    }

    /**
     * 4. ACTUALIZAR SERVICIO
     */
    public function update(Request $request, string $id)
    {
        $service = Service::findOrFail($id);
        
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'sometimes|numeric|min:0',
            'duration'    => 'sometimes|integer',
            'status'      => 'sometimes|boolean',
            'image'       => 'sometimes|string',
        ]);

        $service->update($validated);
        
        return response()->json([
            'success' => true,
            'data'    => $service
        ], 200);
    }

    /**
     * 5. ELIMINAR SERVICIO
     */
    public function destroy($id)
    {
        $service = Service::find($id);
        if (!$service) return response()->json(['message' => 'No encontrado'], 404);
        
        $service->delete();
        return response()->json(['message' => 'Servicio eliminado correctamente']);
    }
}