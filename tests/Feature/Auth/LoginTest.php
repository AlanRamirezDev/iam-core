<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('El sistema bloquea la IP tras 4 intentos fallidos de login', function () {
    $payload = [
        'email' => 'atacante@iam.test',
        'password' => 'clave_falsa_123'
    ];

    // Simular 4 peticiones que el atacante hace en menos de 1 minuto
    for ($i = 0; $i < 4; $i++) {
        $this->postJson('/api/auth/login', $payload)
             ->assertStatus(401);
    }

    $this->postJson('/api/auth/login', $payload)
         ->assertStatus(429);
});

test('Un usuario inactivo no puede iniciar sesión y recibe un error 403', function () {
    // Se construye un usuario de prueba en la base de datos temporal
    $user = \App\Models\User::factory()->create([
        'password' => bcrypt('password_segura')
    ]);

    // Se da de baja lógicamente
    $user->delete();

    // Se afirma que al intentar loguearse con sus credenciales correctas, recibe un 403
    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password_segura',
    ])->assertStatus(403);
});