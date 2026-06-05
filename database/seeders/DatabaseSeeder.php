<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Limpieza de tablas principales para evitar duplicados si se corre el seeder múltiples veces
        DB::statement('TRUNCATE TABLE audit_logs, role_user, users, roles RESTART IDENTITY CASCADE;');

        // Creación de roles
        $adminRole = DB::table('roles')->insertGetId(['name' => 'admin', 'created_at' => now(), 'updated_at' => now()]);
        $operadorRole = DB::table('roles')->insertGetId(['name' => 'operador', 'created_at' => now(), 'updated_at' => now()]);
        $auditorRole = DB::table('roles')->insertGetId(['name' => 'auditor', 'created_at' => now(), 'updated_at' => now()]);

        // Sembrar los usuarios de prueba con las contraseñas demo
        $admin = User::create(['name' => 'Administrador', 'email' => 'admin@iam.test', 'password' => 'Ppassword123*']);
        $admin->roles()->attach($adminRole);

        $operador = User::create(['name' => 'Operador IAM', 'email' => 'operador@iam.test', 'password' => 'Ppassword123*']);
        $operador->roles()->attach($operadorRole);

        $auditor = User::create(['name' => 'Auditor Externo', 'email' => 'auditor@iam.test', 'password' => 'Ppassword123*']);
        $auditor->roles()->attach($auditorRole);

        // Sembrar los 5 Registros Demo (todos los estados)
        $logsDemo = [
            [
                'user_id' => $admin->id,
                'action' => 'LOGIN_SUCCESS',
                'ip_address' => '192.168.1.100',
                'payload' => json_encode(['user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64) AppleWebKit/537.36']),
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'user_id' => $admin->id,
                'action' => 'USER_CREATED',
                'ip_address' => '192.168.1.100',
                'payload' => json_encode(['target_email' => 'nuevo.empleado@iam.test', 'user_agent' => 'Mozilla/5.0']),
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(4),
            ],
            [
                'user_id' => $admin->id,
                'action' => 'USER_DELETED',
                'ip_address' => '10.0.0.15',
                'payload' => json_encode(['target_email' => 'empleado.antiguo@iam.test', 'user_agent' => 'Mozilla/5.0', 'info' => 'Baja temporal solicitada por RH']),
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'user_id' => $admin->id,
                'action' => 'USER_RESTORED',
                'ip_address' => '192.168.1.100',
                'payload' => json_encode(['target_email' => 'empleado.antiguo@iam.test', 'user_agent' => 'Mozilla/5.0', 'info' => 'Reactivación de cuenta autorizada']),
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'user_id' => null,
                'action' => 'USER_DELETED',
                'ip_address' => '203.0.113.50',
                'payload' => json_encode(['target_email' => 'cuenta.eliminada@iam.test', 'user_agent' => 'Unknown API Client']),
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ]
        ];

        \Illuminate\Support\Facades\DB::table('audit_logs')->insert($logsDemo);

    }
}
