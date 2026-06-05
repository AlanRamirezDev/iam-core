<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Obtener la bitácora de auditoría paginada..
     */
    public function index()
    {
        // Solo traer un máximo de 50 registros
        $logs = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'data' => $logs
        ]);
    }
}