<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Verificamos que el usuario tenga sesión (token válido) y que su rol sea 'admin'
        if (auth()->user() && auth()->user()->role === 'admin') {
            return $next($request); // Todo en orden, déjalo pasar a la ruta
        }

        // 2. Si no cumple, lo rebotamos inmediatamente
        return response()->json([
            'error' => 'Acceso denegado. No tienes permisos de administrador.'
        ], 403);
    }
}