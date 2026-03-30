<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\SanctionController;
use App\Http\Controllers\PasswordResetController;

// RUTAS PÚBLICAS (No requieren Token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']); 
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
Route::post('/auth/google', [AuthController::class, 'loginWithGoogle']);

// Rutas de lectura: Cualquiera puede VER la lista de barberos y servicios
Route::apiResource('services', ServiceController::class)->only(['index', 'show']);
Route::apiResource('barbers', BarberController::class)->only(['index', 'show']);


// RUTAS PROTEGIDAS (Requieren Token VIP)
Route::middleware('auth:api')->group(function () {
    
    // Rutas de sesión que necesitan saber quién eres
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', function () {
        return response()->json(auth('api')->user());
    });

    // Rutas de escritura: Solo usuarios con Token pueden CREAR, EDITAR o BORRAR
    Route::apiResource('services', ServiceController::class)->except(['index', 'show']);
    Route::apiResource('barbers', BarberController::class)->except(['index', 'show']);


    Route::apiResource('appointments', AppointmentController::class);
    Route::apiResource('feedback', FeedbackController::class);
    Route::apiResource('sanctions', SanctionController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('movements', MovementController::class);
});