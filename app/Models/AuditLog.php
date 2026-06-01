<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'action', 'ip_address', 'payload'];

    /**
     * Los atributos que deben ser casteados a tipos nativos.
     */
    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * Relación inversa: el log pertenece a un usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}