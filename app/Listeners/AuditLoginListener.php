<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLoginListener
{
    /**
     * Inyectamos el Request actual para capturar datos de red.
     */
    public function __construct(public Request $request)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        AuditLog::create([
            'user_id' => $event->user->id,
            'action' => 'LOGIN_SUCCESS',
            'ip_address' => $this->request->ip(),
            'payload' => [
                'user_agent' => $this->request->userAgent(),
                'guard' => $event->guard
            ]
        ]);
    }
}