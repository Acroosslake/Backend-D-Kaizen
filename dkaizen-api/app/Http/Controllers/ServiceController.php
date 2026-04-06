public function store(Request $request)
{
    $validated = $request->validate([
        'name'        => 'required|string|max:255',
        'description' => 'nullable|string',
        'price'       => 'required|numeric|min:0',
        'duration'    => 'required|integer', 
        'status'      => 'boolean', // 👈 Validamos como booleano
        'image'       => 'nullable|string', 
    ]);

    // Si no envían el status, por defecto lo ponemos en true (activo)
    $data = array_merge($validated, ['status' => $request->get('status', true)]);

    $service = Service::create($data);
    
    return response()->json([
        'success' => true,
        'data'    => $service
    ], 201); 
}

public function update(Request $request, string $id)
{
    $service = Service::findOrFail($id);
    
    $validated = $request->validate([
        'name'        => 'sometimes|string|max:255',
        'description' => 'nullable|string',
        'price'       => 'sometimes|numeric|min:0',
        'duration'    => 'sometimes|integer',
        'status'      => 'sometimes|boolean', // 👈 Para poder apagar/encender
        'image'       => 'sometimes|string',
    ]);

    $service->update($validated);
    
    return response()->json([
        'success' => true,
        'data'    => $service
    ], 200);
}