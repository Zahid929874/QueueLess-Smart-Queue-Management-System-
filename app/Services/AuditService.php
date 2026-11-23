<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log a security or operational event.
     */
    public static function log(string $action, ?User $actor = null, ?string $targetType = null, ?int $targetId = null, ?array $details = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => $actor?->id ?? auth()->id(),
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => Request::userAgent() ?? 'System',
            'details' => $details,
            'created_at' => now(),
        ]);
    }
}
