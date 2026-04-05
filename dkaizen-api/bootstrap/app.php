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
use App\Http\Controllers\StatsController; // No olvides importar este

// --- RUTAS PÚBLICAS ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']); 
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
Route::post('/auth/google', [AuthController::class, 'loginWithGoogle']);

// Lectura pública
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/barbers', [BarberController::class, 'index']);

// --- RUTAS PROTEGIDAS (Cualquier usuario logueado) ---
Route::middleware('auth:api')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']); // Crea este método en tu AuthController

    // Rutas para CLIENTES (Citas, feedback, etc.)
    Route::get('/mis-citas', [AppointmentController::class, 'index']);
    Route::apiResource('appointments', AppointmentController::class);
    Route::apiResource('feedback', FeedbackController::class);

    // --- RUTAS SOLO PARA ADMINS (is_admin) ---
    Route::middleware('is_admin')->group(function () {
        Route::get('/stats', [StatsController::class, 'index']);
        
        // El Admin puede gestionar todo
        Route::apiResource('services', ServiceController::class)->except(['index', 'show']);
        Route::apiResource('barbers', BarberController::class)->except(['index', 'show']);
        Route::apiResource('sanctions', SanctionController::class);
        Route::apiResource('products', ProductController::class);
        Route::apiResource('movements', MovementController::class);
    });

});

// RUTA DE PRUEBA (Mantenla limpia, sin middlewares adentro)
Route::get('/test-db', function () {
    return response()->json(['status' => 'Conexión OK']);
});