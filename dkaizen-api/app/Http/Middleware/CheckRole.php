<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role  <-- Este es el parámetro que enviamos desde las rutas
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Verificamos si hay un usuario logueado
        // 2. Comparamos su rol con el que pide la ruta
        if (!auth()->check() || auth()->user()->role !== $role) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso, fiera. Acceso solo para: ' . $role
            ], 403);
        }

        return $next($request);
    }
}