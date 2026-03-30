<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barber; // Asegúrate de importar el modelo
use Illuminate\Http\Request;

class BarberController extends Controller
{
    // GET: Listar todos los barberos
    public function index()
    {
        $barbers = Barber::all();
        return response()->json($barbers, 200);
    }

    // POST: Crear un nuevo barbero
    public function store(Request $request)
    {
        // Aquí luego agregaremos validaciones (ej. que el nombre sea obligatorio)
        $barber = Barber::create($request->all());
        return response()->json($barber, 201);
    }

    // GET: Ver un barbero en específico
    public function show(string $id)
    {
        $barber = Barber::findOrFail($id);
        return response()->json($barber, 200);
    }

    // PUT/PATCH: Actualizar un barbero
    public function update(Request $request, string $id)
    {
        $barber = Barber::findOrFail($id);
        $barber->update($request->all());
        return response()->json($barber, 200);
    }

    // DELETE: Eliminar un barbero
    public function destroy(string $id)
    {
        $barber = Barber::findOrFail($id);
        $barber->delete();
        return response()->json(null, 204);
    }
}