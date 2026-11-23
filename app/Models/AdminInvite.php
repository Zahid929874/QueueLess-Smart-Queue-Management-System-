<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminInvite extends Model
{
    protected $fillable = [
        'token_hash',
        'institutional_mail',
        'tier',
        'managed_service_unit_ids',
        'expires_at',
        'used_at',
        'used_by',
    ];

    protected function casts(): array
    {
        return [
            'managed_service_unit_ids' => 'array',
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    public function isValid(): bool
    {
        return !$this->used_at && $this->expires_at->isFuture();
    }
}
