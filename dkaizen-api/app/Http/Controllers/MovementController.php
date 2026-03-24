<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use Illuminate\Http\Request;

class MovementController extends Controller
{
    // GET: VIP - Ver flujo de caja
    public function index()
    {
        // Traemos los movimientos ordenados desde el más reciente
        $movements = Movement::orderBy('created_at', 'desc')->get();
        return response()->json($movements, 200);
    }

    // POST: VIP - Registrar un ingreso o un gasto
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:income,expense', // income = Ingreso, expense = Gasto
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255', // ej: "Pago recibo de la luz" o "Venta Cera"
        ]);

        $movement = Movement::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Movimiento financiero registrado',
            'data' => $movement
        ], 201);
    }

    // GET: VIP - Reporte rápido de balance (Bonus nivel Senior)
    public function balance()
    {
        $income = Movement::where('type', 'income')->sum('amount');
        $expense = Movement::where('type', 'expense')->sum('amount');
        
        return response()->json([
            'total_ingresos' => $income,
            'total_gastos' => $expense,
            'balance_neto' => $income - $expense
        ], 200);
    }

    public function destroy($id)
    {
        Movement::destroy($id);
        return response()->json(['message' => 'Movimiento anulado']);
    }
}