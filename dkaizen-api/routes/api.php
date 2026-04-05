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
use App\Http\Controllers\StatsController;

/*
|--------------------------------------------------------------------------
| API Routes - D'KAIZEN BARBER (JWT Edition)
|--------------------------------------------------------------------------
*/

// --- 1. RUTAS PÚBLICAS ---
// Cualquiera puede acceder a estas sin estar logueado
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']); 
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
Route::post('/auth/google', [AuthController::class, 'loginWithGoogle']);

// Consultas públicas (para que los clientes vean antes de entrar)
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/barbers', [BarberController::class, 'index']);


// --- 2. ZONA PROTEGIDA CON JWT ---
// Cambiamos 'auth:sanctum' por 'auth:api' para que Laravel use tu token JWT
Route::middleware('auth:api')->group(function () {
    
    // Rutas básicas de usuario logueado
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', function () {
        return response()->json(auth()->user());
    });

    // --- SUB-ZONA: SOLO ADMINS ---
    // Usamos el alias 'is_admin' que registraste en bootstrap/app.php
    Route::middleware('is_admin')->group(function () {
        Route::get('/stats', [StatsController::class, 'index']);
        
        // Gestión de Barberos y Servicios (Crear, Editar, Borrar)
        Route::apiResource('services', ServiceController::class)->except(['index', 'show']);
        Route::apiResource('barbers', BarberController::class)->except(['index', 'show']);
        
        // Gestión de inventario y disciplina
        Route::apiResource('sanctions', SanctionController::class);
        Route::apiResource('movements', MovementController::class);
        Route::apiResource('products', ProductController::class)->except(['index']);
    });

    // --- SUB-ZONA: CLIENTES Y ACCIONES COMUNES ---
    // Rutas para que el cliente maneje sus propias cosas
    Route::get('/mis-citas', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::post('/feedback', [FeedbackController::class, 'store']);

    // Rutas compartidas (Ambos roles pueden ver productos o una cita específica)
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);

});