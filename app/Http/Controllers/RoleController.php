<?php

namespace App\Http\Controllers;

use App\Models\Role;

class RoleController extends Controller
{
    /**
     * Listar todos los roles disponibles del sistema.
     */
    public function index()
    {
        $roles = Role::select('id', 'name', 'description')->get();
        return response()->json($roles);
    }
}