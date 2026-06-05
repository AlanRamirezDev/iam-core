<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('Un operador recibe un error 403 al intentar dar de baja a otro usuario', function () {
    // Se inserta el rol en la base de datos
    $roleId = DB::table('roles')->insertGetId([
        'name' => 'operador',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Se crea al usuario "Operador" y se vincula con la relación nativa de Laravel
    $operador = User::factory()->create();
    $operador->roles()->attach($roleId);

    // Se crea a un usuario "Objetivo" (el que se intentará dar de baja)
    $usuarioObjetivo = User::factory()->create();

    // Se simula estar autenticado como Operador y se lanza la petición DELETE
    $this->actingAs($operador, 'api')
         ->deleteJson("/api/users/{$usuarioObjetivo->id}")
         ->assertStatus(403);
});