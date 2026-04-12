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
| API Routes - D'KAIZEN BARBER (JWT Edition )
|--------------------------------------------------------------------------
*/

// --- 1. RUTAS PÚBLICAS ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']); 
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
Route::post('/auth/google', [AuthController::class, 'loginWithGoogle']);

Route::get('/services', [ServiceController::class, 'index']);
Route::get('/barbers', [BarberController::class, 'index']);


// --- 2. ZONA PROTEGIDA CON JWT ---
Route::middleware('auth:api')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', function () {
        return response()->json(auth()->user());
    });
    Route::put('/user/update', [AuthController::class, 'updateProfile']);

    // --- RUTAS DE CITAS (COMPARTIDAS) ---
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']);


    // --- SUB-ZONA: SOLO ADMINS ---
    Route::middleware('role:admin')->group(function () {
        Route::get('/stats', [StatsController::class, 'index']);
        
        // Gestión de Barberos y Servicios (CRUD completo)
        Route::apiResource('services', ServiceController::class)->except(['index', 'show']);
        Route::apiResource('barbers', BarberController::class)->except(['index', 'show']);
        
        // Ruta para cargar usuarios que aún no son barberos (Panel Staff)
        Route::get('/available-users', [BarberController::class, 'availableUsers']);

        // Gestión de inventario y disciplina
        Route::apiResource('sanctions', SanctionController::class);
        Route::apiResource('movements', MovementController::class);
        Route::apiResource('products', ProductController::class)->except(['index']);

        // Ruta específica para la agenda completa del admin
        Route::get('/admin/appointments', [AppointmentController::class, 'index']);
    });


    // --- SUB-ZONA: SOLO CLIENTES ---
    Route::middleware('role:client')->group(function () {
        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::post('/feedback', [FeedbackController::class, 'store']);
    });


    // --- OTRAS RUTAS COMPARTIDAS ---
    Route::get('/products', [ProductController::class, 'index']);

}); // <-- Cierre del grupo auth:api