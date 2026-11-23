<?php

namespace App\Services\Notifications\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class WhatsAppProvider
{
    /**
     * Send WhatsApp notification (or mock in development mode).
     */
    public function send(User $user, string $message): bool
    {
        $phone = $user->phone ?? '01700000000';
        Log::info("[NOTIFICATION - WHATSAPP] To {$phone} (User {$user->name}): {$message}");
        return true;
    }
}
