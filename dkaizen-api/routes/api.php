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
// Cualquiera puede ver servicios y barberos sin loguearse
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']); 
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
Route::post('/auth/google', [AuthController::class, 'loginWithGoogle']);

Route::get('/services', [ServiceController::class, 'index']);
Route::get('/barbers', [BarberController::class, 'index']);


// --- 2. ZONA PROTEGIDA CON JWT ---
// Todo lo que esté aquí adentro requiere Token
Route::middleware('auth:api')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Ver mis propios datos
    Route::get('/me', function () {
        return response()->json(auth()->user());
    });

    /**
     * ✅ RUTA UNIVERSAL: ACTUALIZAR PERFIL
     * Ubicada aquí para que Clientes, Barberos y Admins 
     * puedan modificar su nombre y teléfono.
     */
    Route::put('/user/update', [AuthController::class, 'updateProfile']);


    // --- SUB-ZONA: SOLO ADMINS ---
    // El portero 'role:admin' solo vigila este bloque
    Route::middleware('role:admin')->group(function () {
        Route::get('/stats', [StatsController::class, 'index']);
        
        // Gestión de Barberos y Servicios (CRUD completo)
        Route::apiResource('services', ServiceController::class)->except(['index', 'show']);
        Route::apiResource('barbers', BarberController::class)->except(['index', 'show']);
        
        // Gestión de inventario y disciplina
        Route::apiResource('sanctions', SanctionController::class);
        Route::apiResource('movements', MovementController::class);
        Route::apiResource('products', ProductController::class)->except(['index']);
    });


    // --- SUB-ZONA: SOLO CLIENTES ---
    Route::middleware('role:client')->group(function () {
        Route::get('/mis-citas', [AppointmentController::class, 'index']);
        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::post('/feedback', [FeedbackController::class, 'store']);
    });


    // --- RUTAS COMPARTIDAS ADICIONALES ---
    // Ambos roles pueden ver productos y detalles de citas
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);

});