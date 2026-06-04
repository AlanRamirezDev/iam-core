<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Listar usuarios paginados con sus roles.
     */
    public function index()
    {
        $users = User::with('roles:id,name')->latest()->paginate(10);
        return response()->json($users);
    }

    /**
     * Crear un nuevo usuario y asignarle roles.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255',
                Rule::unique('users') 
            ],
            'password' => [
                'required', 
                Password::min(8)->mixedCase()->numbers()->symbols() 
            ],
            'roles' => 'required|array'
        ], [
            // Manejo de errores
            'email.unique' => 'Este correo ya pertenece a una cuenta en la plataforma. Comunícate con un administrador para reactivarla.',
            'password.min' => 'La contraseña debe tener mínimo 8 caracteres.',
            'password.mixed' => 'La contraseña debe incluir mayúsculas y minúsculas.',
            'password.numbers' => 'La contraseña debe incluir al menos un número.',
            'password.symbols' => 'La contraseña debe incluir al menos un símbolo (ej. @, $, !).'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (!empty($validated['roles'])) {
            $roleIds = Role::whereIn('name', $validated['roles'])->pluck('id');
            $user->roles()->sync($roleIds);
        }

        // REGISTRO: ALTA DE USUARIO
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'USER_CREATED',
            'ip_address' => $request->ip(),
            'payload' => [
                'target_user_id' => $user->id,
                'target_email' => $user->email,
                'assigned_roles' => $validated['roles'],
                'user_agent' => $request->userAgent()
            ]
        ]);

        return response()->json($user->load('roles:id,name'), 201);
    }

    /**
     * Eliminar un usuario del sistema (Soft Delete).
     */
    public function destroy(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'No puedes eliminar tu propia cuenta de administrador.'], 403);
        }

        // REGISTRO: BAJA DE USUARIO
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'USER_DELETED',
            'ip_address' => $request->ip(),
            'payload' => [
                'target_user_id' => $user->id,
                'target_email' => $user->email,
                'user_agent' => $request->userAgent()
            ]
        ]);

        $user->delete();
        return response()->json(null, 204);
    }
}