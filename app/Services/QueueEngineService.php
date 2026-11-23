<?php

namespace App\Services;

use App\Models\Counter;
use App\Models\Department;
use App\Models\QueueEvent;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\ServiceSession;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Exception;
use Illuminate\Support\Facades\DB;

class QueueEngineService
{
    public function __construct(
        protected QueuePredictionService $predictionService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Generate the next daily human-readable token number for a department (e.g. ACC-001, REG-014).
     */
    public function generateTokenNumber(Department $department): array
    {
        $today = now()->startOfDay();

        // Count tickets created today in this department
        $lastSequence = QueueTicket::where('department_id', $department->id)
            ->where('created_at', '>=', $today)
            ->max('sequence_number') ?? 0;

        $nextSequence = $lastSequence + 1;
        $formattedNumber = sprintf('%s-%03d', strtoupper($department->code), $nextSequence);

        return [
            'sequence' => $nextSequence,
            'token_number' => $formattedNumber,
        ];
    }

    /**
     * Student joins a queue.
     * Prevents duplicate active queuing in the same department.
     */
    public function joinQueue(StudentProfile $studentProfile, Service $service, ?string $notes = null): QueueTicket
    {
        $department = $service->department;

        if (!$department->is_active) {
            throw new Exception("Department '{$department->name}' is currently closed.");
        }

        if (!$service->is_active) {
            throw new Exception("Service '{$service->name}' is currently unavailable.");
        }

        // Concurrency check: Ensure student doesn't already hold an active ticket in this department
        $existing = QueueTicket::where('student_profile_id', $studentProfile->id)
            ->where('department_id', $department->id)
            ->whereIn('status', ['WAITING', 'CALLED', 'SERVING'])
            ->first();

        if ($existing) {
            throw new Exception("You already hold active token '{$existing->ticket_number}' in {$department->name}.");
        }

        return DB::transaction(function () use ($studentProfile, $service, $department, $notes) {
            $tokenInfo = $this->generateTokenNumber($department);

            // Count people currently waiting ahead in this department
            $peopleAhead = QueueTicket::where('department_id', $department->id)
                ->where('status', 'WAITING')
                ->count();

            // Calculate estimated wait time using M/M/c algorithm
            $waitEstimation = $this->predictionService->estimateWaitTime($department, $service, $peopleAhead);

            $ticket = QueueTicket::create([
                'ticket_number' => $tokenInfo['token_number'],
                'sequence_number' => $tokenInfo['sequence'],
                'department_id' => $department->id,
                'service_id' => $service->id,
                'student_profile_id' => $studentProfile->id,
                'status' => 'WAITING',
                'estimated_wait_time_min' => $waitEstimation['estimated_wait_min'],
                'joined_at' => now(),
                'notes' => $notes,
            ]);

            // Record Queue Event
            QueueEvent::create([
                'queue_ticket_id' => $ticket->id,
                'actor_id' => $studentProfile->user_id,
                'event_type' => 'JOINED',
                'payload' => [
                    'position' => $peopleAhead + 1,
                    'estimated_wait_min' => $ticket->estimated_wait_time_min,
                ],
            ]);

            AuditService::log('TICKET_JOINED', $studentProfile->user, 'QueueTicket', $ticket->id, [
                'token' => $ticket->ticket_number,
                'department' => $department->code,
                'service' => $service->name,
            ]);

            // Dispatch notification
            $this->notificationService->notifyJoined($ticket);

            return $ticket;
        });
    }

    /**
     * Counter Operator calls the next waiting student.
     * Uses pessimistic row-locking (lockForUpdate) to strictly eliminate race conditions
     * between simultaneous operators.
     */
    public function callNext(Counter $counter, User $operator): ?QueueTicket
    {
        return DB::transaction(function () use ($counter, $operator) {
            $departmentId = $counter->department_id;

            // Atomic selection of next ticket in line
            $ticket = QueueTicket::where('department_id', $departmentId)
                ->where('status', 'WAITING')
                ->orderBy('created_at', 'asc')
                ->lockForUpdate() // Exclusively locks row against competing operators
                ->first();

            if (!$ticket) {
                return null; // Queue is empty
            }

            // Assign counter and update status to CALLED
            $ticket->update([
                'status' => 'CALLED',
                'counter_id' => $counter->id,
                'operator_id' => $operator->id,
                'called_at' => now(),
            ]);

            $counter->update(['status' => 'BUSY']);

            QueueEvent::create([
                'queue_ticket_id' => $ticket->id,
                'actor_id' => $operator->id,
                'event_type' => 'CALLED',
                'payload' => [
                    'counter_id' => $counter->id,
                    'counter_number' => $counter->counter_number,
                ],
            ]);

            AuditService::log('TICKET_CALLED', $operator, 'QueueTicket', $ticket->id, [
                'token' => $ticket->ticket_number,
                'counter' => $counter->name,
            ]);

            // Notify student
            $this->notificationService->notifyCalled($ticket);

            // Check if upcoming students are approaching (e.g. 3rd in queue)
            $this->notifyApproachingStudents($counter->department);

            return $ticket;
        });
    }

    /**
     * Operator recalls a called token (e.g. student didn't respond to first call).
     */
    public function recallToken(QueueTicket $ticket, Counter $counter, User $operator): QueueTicket
    {
        if ($ticket->status !== 'CALLED') {
            throw new Exception("Only tickets in 'CALLED' status can be recalled.");
        }

        $ticket->update(['called_at' => now()]);

        QueueEvent::create([
            'queue_ticket_id' => $ticket->id,
            'actor_id' => $operator->id,
            'event_type' => 'RECALLED',
            'payload' => ['counter_id' => $counter->id],
        ]);

        $this->notificationService->notifyCalled($ticket);

        return $ticket;
    }

    /**
     * Operator begins active service for the called token.
     */
    public function startService(QueueTicket $ticket, Counter $counter, User $operator): QueueTicket
    {
        if ($ticket->status !== 'CALLED') {
            throw new Exception("Ticket must be in 'CALLED' state before starting service.");
        }

        return DB::transaction(function () use ($ticket, $counter, $operator) {
            $ticket->update([
                'status' => 'SERVING',
                'served_at' => now(),
            ]);

            $counter->update(['status' => 'BUSY']);

            // Create Service Session for duration tracking
            ServiceSession::create([
                'queue_ticket_id' => $ticket->id,
                'counter_id' => $counter->id,
                'operator_id' => $operator->id,
                'started_at' => now(),
            ]);

            QueueEvent::create([
                'queue_ticket_id' => $ticket->id,
                'actor_id' => $operator->id,
                'event_type' => 'SERVICE_STARTED',
                'payload' => ['counter_id' => $counter->id],
            ]);

            AuditService::log('SERVICE_STARTED', $operator, 'QueueTicket', $ticket->id);

            return $ticket;
        });
    }

    /**
     * Operator completes active service.
     */
    public function completeService(QueueTicket $ticket, Counter $counter, User $operator): QueueTicket
    {
        if (!in_array($ticket->status, ['CALLED', 'SERVING'])) {
            throw new Exception("Ticket is not actively being served.");
        }

        return DB::transaction(function () use ($ticket, $counter, $operator) {
            $completedAt = now();
            $ticket->update([
                'status' => 'COMPLETED',
                'completed_at' => $completedAt,
            ]);

            $counter->update(['status' => 'AVAILABLE']);

            // Close session & compute duration
            $session = ServiceSession::where('queue_ticket_id', $ticket->id)->first();
            if ($session) {
                $duration = $completedAt->diffInSeconds($session->started_at ?? $ticket->called_at);
                $expectedSeconds = ($ticket->service->estimated_duration_min ?? 5) * 60;
                $slaStatus = ($duration > ($expectedSeconds * 1.5)) ? 'BREACHED_SLA' : 'WITHIN_SLA';

                $session->update([
                    'ended_at' => $completedAt,
                    'duration_seconds' => $duration,
                    'sla_status' => $slaStatus,
                ]);
            }

            QueueEvent::create([
                'queue_ticket_id' => $ticket->id,
                'actor_id' => $operator->id,
                'event_type' => 'SERVICE_COMPLETED',
                'payload' => [
                    'counter_id' => $counter->id,
                    'duration_seconds' => $session?->duration_seconds ?? 0,
                ],
            ]);

            AuditService::log('SERVICE_COMPLETED', $operator, 'QueueTicket', $ticket->id);

            return $ticket;
        });
    }

    /**
     * Mark called student as NO_SHOW.
     */
    public function markNoShow(QueueTicket $ticket, Counter $counter, User $operator): QueueTicket
    {
        if ($ticket->status !== 'CALLED') {
            throw new Exception("Only CALLED tickets can be marked as NO_SHOW.");
        }

        return DB::transaction(function () use ($ticket, $counter, $operator) {
            $ticket->update([
                'status' => 'NO_SHOW',
                'completed_at' => now(),
            ]);

            $counter->update(['status' => 'AVAILABLE']);

            QueueEvent::create([
                'queue_ticket_id' => $ticket->id,
                'actor_id' => $operator->id,
                'event_type' => 'NO_SHOW',
                'payload' => ['counter_id' => $counter->id],
            ]);

            AuditService::log('TICKET_NO_SHOW', $operator, 'QueueTicket', $ticket->id);

            return $ticket;
        });
    }

    /**
     * Student or Admin cancels ticket.
     */
    public function cancelTicket(QueueTicket $ticket, ?User $actor = null, ?string $reason = null): QueueTicket
    {
        if (in_array($ticket->status, ['COMPLETED', 'CANCELLED', 'NO_SHOW'])) {
            throw new Exception("Ticket is already finished or cancelled.");
        }

        return DB::transaction(function () use ($ticket, $actor, $reason) {
            $ticket->update([
                'status' => 'CANCELLED',
                'completed_at' => now(),
                'notes' => $reason ? ($ticket->notes . " [Cancelled: {$reason}]") : $ticket->notes,
            ]);

            if ($ticket->counter_id && $ticket->status === 'CALLED') {
                $ticket->counter?->update(['status' => 'AVAILABLE']);
            }

            QueueEvent::create([
                'queue_ticket_id' => $ticket->id,
                'actor_id' => $actor?->id,
                'event_type' => 'CANCELLED',
                'payload' => ['reason' => $reason],
            ]);

            AuditService::log('TICKET_CANCELLED', $actor, 'QueueTicket', $ticket->id, ['reason' => $reason]);

            return $ticket;
        });
    }

    /**
     * Transfer ticket to another department or service.
     */
    public function transferTicket(QueueTicket $ticket, Department $targetDepartment, ?Service $targetService, User $operator): QueueTicket
    {
        return DB::transaction(function () use ($ticket, $targetDepartment, $targetService, $operator) {
            $ticket->update([
                'status' => 'TRANSFERRED',
                'completed_at' => now(),
            ]);

            if ($ticket->counter_id) {
                $ticket->counter?->update(['status' => 'AVAILABLE']);
            }

            // Create new ticket in target department
            $service = $targetService ?? $targetDepartment->services()->first();
            $newTokenInfo = $this->generateTokenNumber($targetDepartment);

            $newTicket = QueueTicket::create([
                'ticket_number' => $newTokenInfo['token_number'],
                'sequence_number' => $newTokenInfo['sequence'],
                'department_id' => $targetDepartment->id,
                'service_id' => $service->id,
                'student_profile_id' => $ticket->student_profile_id,
                'status' => 'WAITING',
                'estimated_wait_time_min' => 5,
                'joined_at' => now(),
                'notes' => "Transferred from {$ticket->department->code} ({$ticket->ticket_number})",
            ]);

            QueueEvent::create([
                'queue_ticket_id' => $ticket->id,
                'actor_id' => $operator->id,
                'event_type' => 'TRANSFERRED',
                'payload' => [
                    'target_department_id' => $targetDepartment->id,
                    'new_ticket_number' => $newTicket->ticket_number,
                ],
            ]);

            AuditService::log('TICKET_TRANSFERRED', $operator, 'QueueTicket', $ticket->id, [
                'target_department' => $targetDepartment->code,
                'new_ticket' => $newTicket->ticket_number,
            ]);

            return $newTicket;
        });
    }

    /**
     * Notify students approaching front of queue.
     */
    protected function notifyApproachingStudents(Department $department): void
    {
        $waitingTickets = QueueTicket::with('studentProfile.user')
            ->where('department_id', $department->id)
            ->where('status', 'WAITING')
            ->orderBy('created_at', 'asc')
            ->limit(5)
            ->get();

        foreach ($waitingTickets as $index => $t) {
            $position = $index + 1;
            if ($position === 3) {
                $this->notificationService->notifyApproaching($t, $position);
            }
        }
    }
}
