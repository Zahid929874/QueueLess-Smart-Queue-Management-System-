<?php

namespace App\Services\Notifications\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class WebPushProvider
{
    /**
     * Send Web Push notification (or mock in development mode).
     */
    public function send(User $user, string $title, string $message, array $payload = []): bool
    {
        // Development / simulation logger
        Log::info("[NOTIFICATION - WEB PUSH] To User #{$user->id} ({$user->name}): '{$title}' - {$message}", $payload);
        return true;
    }
}
