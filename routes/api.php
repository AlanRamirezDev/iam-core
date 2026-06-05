<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;

// --- Rutas de Autenticación ---
Route::prefix('auth')->group(function () {
    // Limitador de peticiones (4 intentos, 1 minuto de bloqueo)
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:4,1');

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

// --- GRUPO 1: Auditoría ---
Route::middleware(['auth:api', 'role:admin,auditor'])->group(function () {
    Route::get('audit-logs', [AuditLogController::class, 'index']);
});

// --- GRUPO 2: Gestión de Accesos ---
Route::middleware(['auth:api', 'role:admin,auditor,operador'])->group(function () {
    Route::get('roles', [RoleController::class, 'index']);
    Route::get('users', [UserController::class, 'index']);
});

// --- GRUPO 3: Gestión de Accesos (Write) ---
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::post('users', [UserController::class, 'store']);
    Route::delete('users/{user}', [UserController::class, 'destroy']);
    Route::post('users/{id}/restore', [UserController::class, 'restore']);
});

Route::post('/demo/simulate', function () {
    // Rotación de logs
    $maxRecords = 50;
    $currentCount = \Illuminate\Support\Facades\DB::table('audit_logs')->count();
    
    if ($currentCount > $maxRecords) {
        $idsToDelete = \Illuminate\Support\Facades\DB::table('audit_logs')
            ->whereNotIn('id', [1, 2, 3, 4, 5]) // Proteger los 5 registros diferentes demo
            ->orderBy('created_at', 'asc')
            ->limit($currentCount - $maxRecords + 10)
            ->pluck('id');
            
        \Illuminate\Support\Facades\DB::table('audit_logs')->whereIn('id', $idsToDelete)->delete();
    }

    // Obtener los IDs reales para evitar errores
    $allUserIds = \Illuminate\Support\Facades\DB::table('users')->pluck('id')->toArray();
    
    // Obtenemos estrictamente los IDs de quienes tienen el rol de admin
    $adminIds = \Illuminate\Support\Facades\DB::table('role_user')
        ->join('roles', 'role_user.role_id', '=', 'roles.id')
        ->where('roles.name', 'admin')
        ->pluck('role_user.user_id')
        ->toArray();

    $nonAdminIds = \Illuminate\Support\Facades\DB::table('role_user')
        ->join('roles', 'role_user.role_id', '=', 'roles.id')
        ->where('roles.name', '!=', 'admin')
        ->pluck('role_user.user_id')
        ->toArray();
    
    if (empty($adminIds) || empty($nonAdminIds)) {
        return response()->json(['error' => 'Faltan usuarios o roles para simular tráfico diversificado.'], 400);
    }

    $eventos = ['LOGIN_SUCCESS', 'USER_CREATED', 'USER_DELETED', 'USER_RESTORED'];
    $ips = ['192.168.1.105', '203.0.113.42', '10.0.0.5', '172.16.254.1', '8.8.8.8'];

    // Inyección de datos demo en orden cronológico
    for ($i = 0; $i < 3; $i++) {
        $evento = $eventos[array_rand($eventos)];
        
        // LÓGICA DE NEGOCIO: Si es acción crítica, el autor es un Admin. Si no, el autor puede ser un Operador o Auditor.
        if (in_array($evento, ['USER_CREATED', 'USER_DELETED', 'USER_RESTORED'])) {
            $autorId = $adminIds[array_rand($adminIds)];
        } else {
            $autorId = $nonAdminIds[array_rand($nonAdminIds)];
        }

        \Illuminate\Support\Facades\DB::table('audit_logs')->insert([
            'user_id' => $autorId, 
            'action' => $evento,
            'ip_address' => $ips[array_rand($ips)],
            'payload' => json_encode([
                'target_email' => 'usuario.prueba' . rand(10, 99) . '@iam.local',
                'user_agent' => 'Mozilla/5.0 (Simulator Bot) AppleWebKit/537.36',
                'info' => 'Evento inyectado: Simulación de tráfico de red para evaluación técnica.'
            ]),
            'created_at' => now()->addSeconds($i),
            'updated_at' => now()->addSeconds($i),
        ]);
    }

    return response()->json(['message' => 'Tráfico simulado inyectado con éxito']);
})->middleware(['auth:api', 'role:admin']);