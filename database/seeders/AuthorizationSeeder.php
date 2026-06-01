<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AuthorizationSeeder extends Seeder
{
    /**
     * Correr los registros de la base de datos.
     */
    public function run(): void
    {
        // 1. Crear los roles principales del sistema
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Acceso total al sistema, gestión de usuarios y roles.']
        );

        $auditorRole = Role::firstOrCreate(
            ['name' => 'auditor'],
            ['description' => 'Acceso exclusivo para la lectura y auditoría de la bitácora de logs.']
        );

        $operatorRole = Role::firstOrCreate(
            ['name' => 'operator'],
            ['description' => 'Acceso operativo estándar para las funciones del negocio.']
        );

        // 2. Crear un usuario Administrador de prueba
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@iam.local'],
            [
                'name' => 'Alan Admin',
                'password' => Hash::make('password123'),
            ]
        );

        // 3. Vincular el rol al usuario si no lo tiene asignado
        if (!$adminUser->roles()->where('name', 'admin')->exists()) {
            $adminUser->roles()->attach($adminRole->id);
        }
    }
}