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

// --- 1. RUTAS PÚBLICAS (Cualquiera entra) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']); 
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
Route::post('/auth/google', [AuthController::class, 'loginWithGoogle']);

// Lectura de barberos y servicios para que los clientes elijan
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/barbers', [BarberController::class, 'index']);


// --- 2. ZONA PROTEGIDA (Necesitas Token) ---
Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', function () {
        return response()->json(auth()->user());
    });

    // --- SOLO ADMIN (Usamos 'is_admin' que registraste en app.php) ---
    Route::middleware('is_admin')->group(function () {
        Route::get('/stats', [StatsController::class, 'index']);
        
        // Gestión de negocio
        Route::post('/services', [ServiceController::class, 'store']);
        Route::put('/services/{service}', [ServiceController::class, 'update']);
        Route::delete('/services/{service}', [ServiceController::class, 'destroy']);
        
        Route::apiResource('sanctions', SanctionController::class);
        Route::apiResource('movements', MovementController::class);
        Route::apiResource('products', ProductController::class)->except(['index']);
    });

    // --- SOLO CLIENTE ---
    // Nota: Aquí podrías crear un middleware 'is_client' igual que el de admin
    // O simplemente dejar las rutas de cliente aquí si no hay conflicto
    Route::get('/mis-citas', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::post('/feedback', [FeedbackController::class, 'store']);

    // --- RUTAS COMPARTIDAS ---
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);

});