<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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