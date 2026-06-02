<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;

// --- Rutas de Autenticación ---
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

// --- Rutas Operativas del Sistema ---
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    // Bitácora de Auditoría
    Route::get('audit-logs', [AuditLogController::class, 'index']);
    // Gestión de Roles (Solo lectura)
    Route::get('roles', [RoleController::class, 'index']);
    // Gestión de Usuarios
    Route::apiResource('users', UserController::class)->only(['index', 'store', 'destroy']);

});