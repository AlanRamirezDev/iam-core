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
        // Obtenemos el historial completo
        $logs = AuditLog::with('user:id,name,email')
                        ->latest()
                        ->get();
        return response()->json(['data' => $logs]);
    }
}