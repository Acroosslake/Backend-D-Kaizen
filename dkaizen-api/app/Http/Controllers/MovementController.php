<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovementController extends Controller
{
    //Ver todo el historial de movimientos
    public function index()
    {
        $movements = Movement::with('product')->get();
        return response()->json($movements);
    }

    //Registrar un movimiento (¡y actualizar el stock automáticamente!)
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'movement_type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'movement_date' => 'required|date',
        ]);

        // Iniciamos una transacción para que si algo falla, no se guarde a medias
        DB::beginTransaction();

        try {
            // Creamos el registro del movimiento
            $movement = Movement::create($request->all());

            // Buscamos el producto para actualizarle su stock
            $product = Product::find($request->product_id);

            if ($request->movement_type === 'in') {
                $product->stock += $request->quantity; // Sumamos si entra
            } else {
                // Verificamos que no quede stock negativo
                if ($product->stock < $request->quantity) {
                    return response()->json(['message' => 'No hay suficiente stock para esta salida'], 400);
                }
                $product->stock -= $request->quantity; // Restamos si sale
            }
            
            $product->save(); // Guardamos el nuevo stock del producto

            DB::commit(); // Confirmamos que todo salió bien

            return response()->json([
                'success' => true,
                'data' => $movement,
                'message' => 'Movimiento registrado y stock actualizado'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack(); // Si hay un error, deshacemos todo
            return response()->json(['message' => 'Error al procesar el movimiento'], 500);
        }
    }

    //Ver un movimiento específico
    public function show($id)
    {
        $movement = Movement::with('product')->find($id);

        if (!$movement) {
            return response()->json(['message' => 'Movimiento no encontrado'], 404);
        }

        return response()->json($movement);
    }

    // (Omitimos update y destroy por seguridad: en contabilidad e inventarios 
    // reales, los movimientos no se borran ni editan, se hacen contra-movimientos).
}