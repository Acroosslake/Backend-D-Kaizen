<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes - D'KAIZEN BARBER
|--------------------------------------------------------------------------
*/

// --- 1. RUTAS PÚBLICAS (Sin candado) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'loginWithGoogle']);
Route::get('/services', [ServiceController::class, 'index']); 
Route::get('/barbers', [BarberController::class, 'index']);
Route::get('/appointments/occupied', [AppointmentController::class, 'getOccupiedSlots']); // ✅ Esta está perfecta aquí para el radar de horas

// --- 2. ZONA PROTEGIDA CON JWT (Solo gente logueada) ---
Route::middleware('auth:api')->group(function () {
    
    Route::get('/me', function () { return response()->json(auth()->user()); });
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- SUB-ZONA: SOLO ADMINS ---
    Route::middleware('role:admin')->group(function () {
        Route::get('/stats', [StatsController::class, 'index']);
        Route::apiResource('users', UserController::class);
        
        // ✂️ Gestión de Citas & Inasistencias
        Route::put('/appointments/{id}/no-show', [AppointmentController::class, 'noShow']); 
        Route::get('/admin/appointments', [AppointmentController::class, 'index']);
        
        // 🛠️ Gestión de Servicios
        Route::apiResource('services', ServiceController::class)->except(['index']);
        
        // 📦 Gestión de Inventario
        Route::apiResource('products', ProductController::class)->except(['index']);
        
        // Staff & Disponibilidad
        Route::apiResource('barbers', BarberController::class)->except(['index', 'show']);
        Route::get('/available-users', [BarberController::class, 'availableUsers']);
    });

    // --- SUB-ZONA: SOLO CLIENTES ---
    Route::middleware('role:client')->group(function () {
        Route::post('/appointments', [AppointmentController::class, 'store']);
    });

    // --- RUTAS COMPARTIDAS (Auth requerida para ambos roles) ---
    Route::get('/appointments', [AppointmentController::class, 'index']);
    
    // ✅ AQUÍ VAN LAS DE ACTUALIZAR Y CANCELAR
    Route::put('/appointments/{id}', [AppointmentController::class, 'update']);
    Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']);
    
    Route::get('/products', [ProductController::class, 'index']); // Ambos ven el stock
});