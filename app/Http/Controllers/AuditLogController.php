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
        $logs = AuditLog::with('user:id,name,email')
                        ->latest()
                        ->paginate(15);

        return response()->json($logs);
    }
}