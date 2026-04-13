<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
public function index()
{
    try {
        // Forzamos zona horaria de Colombia para que "Hoy" sea real
        $hoy = \Carbon\Carbon::now('-05:00')->format('Y-m-d');

        // 1. Ingresos Diarios (Usamos 'appointment_date')
        $ingresosHoy = Appointment::whereDate('appointment_date', $hoy)
            ->where('status', 'completed')
            ->sum('total_price') ?? 0;

        // 2. Ingresos Mensuales
        $ingresosMes = Appointment::whereMonth('appointment_date', date('m'))
            ->whereYear('appointment_date', date('Y'))
            ->where('status', 'completed')
            ->sum('total_price') ?? 0;

        // 3. Citas Activas
        $citasActivas = Appointment::whereDate('appointment_date', $hoy)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        // 4. Movimientos
        $movimientos = Appointment::with(['user', 'service', 'barber.user'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function($a) {
                return [
                    'id' => $a->id,
                    'corte' => $a->service->name ?? "Servicio",
                    'cliente' => $a->user->name ?? 'Cliente',
                    'barber' => $a->barber->user->name ?? 'Staff',
                    'precio' => '$' . number_format($a->total_price ?? 0),
                    'hora' => $a->created_at ? $a->created_at->diffForHumans() : 'Reciente'
                ];
            });

        return response()->json([
            'ingresos' => (float)$ingresosHoy,
            'ingresosMes' => (float)$ingresosMes,
            'citas' => $citasActivas,
            'movimientos' => $movimientos,
            'grafica' => [] 
        ]);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}