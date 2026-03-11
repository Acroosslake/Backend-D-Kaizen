<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\MovementController;



// ESTAS DEBEN ESTAR AFUERA (Públicas)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ESTAS DEBEN ESTAR ADENTRO (Protegidas)
Route::middleware('auth:api')->group(function () {
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/me', function () {
        return response()->json(auth('api')->user());
    });
});

//esta linea se agrega para ahorranos trabajo ya que sin esta Para hacer un CRUD completo, normalmente tendrías que programar 5 rutas (endpoints) distintas a mano, indicando el método HTTP exacto para cada acción. despues la pondremos
Route::apiResource('services', ServiceController::class);
Route::apiResource('appointments', AppointmentController::class);
Route::apiResource('barbers', BarberController::class);
Route::apiResource('feedback', FeedbackController::class);
Route::apiResource('sanctions', SanctionController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('movements', MovementController::class);