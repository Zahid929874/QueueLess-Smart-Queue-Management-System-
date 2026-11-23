<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\QueueTicket;
use App\Models\User;
use App\Services\Notifications\Providers\SmsProvider;
use App\Services\Notifications\Providers\WebPushProvider;
use App\Services\Notifications\Providers\WhatsAppProvider;

class NotificationService
{
    public function __construct(
        protected WebPushProvider $webPushProvider,
        protected SmsProvider $smsProvider,
        protected WhatsAppProvider $whatsAppProvider
    ) {}

    /**
     * Send multi-channel notification and persist record in database.
     */
    public function notify(User $user, string $title, string $message, ?QueueTicket $ticket = null, string $channel = 'IN_APP'): Notification
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'queue_ticket_id' => $ticket?->id,
            'channel' => $channel,
            'title' => $title,
            'message' => $message,
            'status' => 'SENT',
            'is_read' => false,
        ]);

        // Dispatch to external channel mock/real
        match ($channel) {
            'WEB_PUSH' => $this->webPushProvider->send($user, $title, $message, ['ticket_id' => $ticket?->id]),
            'SMS' => $this->smsProvider->send($user, $message),
            'WHATSAPP' => $this->whatsAppProvider->send($user, $message),
            default => null,
        };

        return $notification;
    }

    /**
     * Helper to notify student when ticket is created.
     */
    public function notifyJoined(QueueTicket $ticket): void
    {
        $user = $ticket->studentProfile->user;
        $title = "Queue Token Generated: {$ticket->ticket_number}";
        $message = "You have joined the queue for {$ticket->service->name} at {$ticket->department->name}. Your initial estimated wait is {$ticket->estimated_wait_time_min} minutes.";

        $this->notify($user, $title, $message, $ticket, 'IN_APP');
        $this->notify($user, $title, $message, $ticket, 'SMS');
    }

    /**
     * Helper to notify student when their turn is approaching (e.g. 3rd in line).
     */
    public function notifyApproaching(QueueTicket $ticket, int $position): void
    {
        $user = $ticket->studentProfile->user;
        $title = "Your Turn is Approaching!";
        $message = "Ticket {$ticket->ticket_number}: You are now #{$position} in queue at {$ticket->department->name}. Please stay nearby the service area.";

        $this->notify($user, $title, $message, $ticket, 'WEB_PUSH');
        $this->notify($user, $title, $message, $ticket, 'IN_APP');
    }

    /**
     * Helper to notify student when their token is called.
     */
    public function notifyCalled(QueueTicket $ticket): void
    {
        $user = $ticket->studentProfile->user;
        $counterName = $ticket->counter?->name ?? 'Service Counter';
        $title = "Your Ticket is Called!";
        $message = "NOW CALLING Ticket {$ticket->ticket_number}: Please proceed immediately to {$counterName} at {$ticket->department->name}.";

        $this->notify($user, $title, $message, $ticket, 'WEB_PUSH');
        $this->notify($user, $title, $message, $ticket, 'SMS');
        $this->notify($user, $title, $message, $ticket, 'IN_APP');
    }
}
