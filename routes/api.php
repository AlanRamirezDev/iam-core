<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuditLogController;

// --- Rutas de Autenticación ---
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

// --- Rutas Operativas del Sistema ---
Route::middleware('auth:api')->group(function () {
    Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware('role:admin');

});