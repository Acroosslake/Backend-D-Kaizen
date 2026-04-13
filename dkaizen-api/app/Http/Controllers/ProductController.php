<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    //Mostrar todo el catálogo de productos
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }

    //Crear un nuevo producto
    public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'price'          => 'required|numeric|min:0',
            'stock'          => 'required|integer|min:0',
            'category'       => 'required|string',
            'purchase_price' => 'nullable|numeric|min:0', // ✅ Opcional
        ]);

        $validated['status'] = 'active';
        
        // Si no viene precio de compra, le ponemos 0 para que no llore la DB
        if (!isset($validated['purchase_price'])) {
            $validated['purchase_price'] = 0;
        }

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'data'    => $product
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}

    //Ver un producto específico
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        return response()->json($product);
    }

    //Actualizar un producto (ej: le subiste el precio a la cera)
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $product->update($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    //Eliminar un producto del catálogo
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $product->delete();
        
        return response()->json(['message' => 'Producto eliminado correctamente']);
    }
}