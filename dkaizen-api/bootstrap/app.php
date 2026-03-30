<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

// 1. IMPORTANTE: Importamos tu middleware de Admin aquí arriba
use App\Http\Middleware\CheckAdmin;

// Importamos el error nativo de Laravel
use Illuminate\Auth\AuthenticationException;

// Importamos los errores de JWT
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        // 2. REGISTRAMOS EL ALIAS DE TU GUARDIA VIP AQUÍ
        $middleware->alias([
            'is_admin' => CheckAdmin::class
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        
        // 1. EL ESCUDO PRINCIPAL: Atrapa el intento de redirección a "Login"
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            // Si la ruta empieza con 'api/', siempre devolvemos JSON con error 401
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false, 
                    'message' => 'No autorizado. El token es inválido, expiró o no fue enviado.'
                ], 401);
            }
        });

        // 2. Escudos secundarios por si usamos JWT manualmente después
        $exceptions->render(function (TokenInvalidException $e, Request $request) {
            return response()->json(['success' => false, 'message' => 'Token inválido o modificado. Acceso denegado.'], 401);
        });

        $exceptions->render(function (TokenExpiredException $e, Request $request) {
            return response()->json(['success' => false, 'message' => 'El token ha expirado. Por favor inicia sesión de nuevo.'], 401);
        });

        $exceptions->render(function (JWTException $e, Request $request) {
            return response()->json(['success' => false, 'message' => 'Error procesando el Token de seguridad.'], 401);
        });

    })->create();