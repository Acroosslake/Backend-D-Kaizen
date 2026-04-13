<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes - D'KAIZEN BARBER
|--------------------------------------------------------------------------
*/

// --- 1. RUTAS PÚBLICAS ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'loginWithGoogle']);
Route::get('/services', [ServiceController::class, 'index']); 
Route::get('/barbers', [BarberController::class, 'index']);
Route::get('/appointments/occupied', [AppointmentController::class, 'getOccupiedSlots']);

// --- 2. ZONA PROTEGIDA CON JWT ---
Route::middleware('auth:api')->group(function () {
    
    Route::get('/me', function () { return response()->json(auth()->user()); });

    // ✅ RUTA PARA ACTUALIZAR EL PERFIL
    Route::put('/user/update', [AuthController::class, 'updateProfile']);

    Route::post('/logout', [AuthController::class, 'logout']);

    // 📩 RUTAS DE VERIFICACIÓN DE EMAIL (Fundamentales para el registro)
    // Esta es la ruta que Laravel busca por nombre para armar el link del correo
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        // Redirigimos al frontend avisando que ya quedó verificado
        return redirect(env('FRONTEND_URL') . '/login?verified=1');
    })->middleware(['signed'])->name('verification.verify');

    // Por si el cliente no recibió el correo y quiere otro
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Enlace de verificación enviado.']);
    })->middleware(['throttle:6,1'])->name('verification.send');

    // --- SUB-ZONA: SOLO ADMINS ---
    Route::middleware('role:admin')->group(function () {
        Route::get('/stats', [StatsController::class, 'index']);
        Route::apiResource('users', UserController::class);
        Route::put('/appointments/{id}/no-show', [AppointmentController::class, 'noShow']); 
        Route::get('/admin/appointments', [AppointmentController::class, 'index']);
        Route::apiResource('services', ServiceController::class)->except(['index']);
        Route::apiResource('products', ProductController::class)->except(['index']);
        Route::apiResource('barbers', BarberController::class)->except(['index', 'show']);
        Route::get('/available-users', [BarberController::class, 'availableUsers']);
    });

    // --- SUB-ZONA: SOLO CLIENTES ---
    Route::middleware('role:client')->group(function () {
        Route::post('/appointments', [AppointmentController::class, 'store']);
    });

    // --- RUTAS COMPARTIDAS ---
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::put('/appointments/{id}', [AppointmentController::class, 'update']);
    Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']);
    Route::get('/products', [ProductController::class, 'index']);
});