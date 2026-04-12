<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Sanction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today();

        // 1. Ingresos de hoy (Suma de precios de citas completadas)
        $ingresosHoy = Appointment::whereDate('date', $hoy)
            ->where('status', 'completed')
            ->sum('total_price');

        // 2. Citas Activas (Pendientes o confirmadas para hoy)
        $citasActivas = Appointment::whereDate('date', $hoy)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        // 3. Alertas (Sanciones activas)
        $alertas = Sanction::where('status', 'active')->count();

        // 4. Últimos Movimientos (Citas recientes con nombres)
        $movimientos = Appointment::with(['user', 'barber.user'])
            ->latest()
            ->take(3)
            ->get()
            ->map(function($a) {
                return [
                    'id' => $a->id,
                    'corte' => "Servicio de Barbería", // O $a->service->name si tienes la relación
                    'cliente' => $a->user->name,
                    'barber' => $a->barber->user->name ?? 'Staff',
                    'precio' => '$' . number_format($a->total_price),
                    'hora' => $a->created_at->diffForHumans()
                ];
            });

        // 5. Data para la Gráfica (Últimos 7 días)
        $grafica = Appointment::select(
                DB::raw('DATE(date) as fecha'),
                DB::raw('SUM(total_price) as total')
            )
            ->where('date', '>=', Carbon::now()->subDays(6))
            ->where('status', 'completed')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        return response()->json([
            'ingresos' => $ingresosHoy,
            'citas' => $citasActivas,
            'alertas' => $alertas,
            'movimientos' => $movimientos,
            'grafica' => $grafica
        ]);
    }
}