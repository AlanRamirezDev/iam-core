<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Autenticar al usuario y devolver el token JWT.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        $user = \App\Models\User::withTrashed()->where('email', $request->email)->first();

        if ($user && \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            if ($user->trashed()) {
                return response()->json(['error' => 'Tu cuenta está inactiva. Contacta a un administrador.'], 403);
            }
        }

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'No autorizado. Credenciales incorrectas.'], 401);
        }

        event(new \Illuminate\Auth\Events\Login('api', Auth::guard('api')->user(), false));

        return $this->respondWithToken($token);
    }

    /**
     * Obtener los datos del usuario autenticado actual.
     */
    public function me()
    {
        return response()->json(Auth::guard('api')->user()->load('roles:id,name'));
    }

    /**
     * Cerrar sesión (Invalidar el token JWT).
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json(['message' => 'Sesión cerrada exitosamente']);
    }

    /**
     * Dar formato a la respuesta JSON con el token.
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => Auth::guard('api')->user()->load('roles:id,name')
        ]);
    }
}