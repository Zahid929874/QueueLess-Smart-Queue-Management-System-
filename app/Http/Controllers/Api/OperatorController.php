<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use App\Models\Department;
use App\Models\OperatorAssignment;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Services\QueueEngineService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    public function __construct(
        protected QueueEngineService $queueEngine
    ) {}

    /**
     * Get counters list
     */
    public function counters(Request $request): JsonResponse
    {
        $departmentId = $request->input('department_id');
        $query = Counter::with('department');

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $counters = $query->get();

        return response()->json([
            'success' => true,
            'data' => $counters,
        ]);
    }

    /**
     * Assign operator to a counter
     */
    public function assignCounter(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'counter_id' => 'required|exists:counters,id',
        ]);

        $user = $request->user();
        $counter = Counter::findOrFail($validated['counter_id']);

        // End previous active assignments for this operator
        OperatorAssignment::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->update([
                'status' => 'COMPLETED',
                'ended_at' => now(),
            ]);

        // Create new active assignment
        $assignment = OperatorAssignment::create([
            'user_id' => $user->id,
            'counter_id' => $counter->id,
            'started_at' => now(),
            'status' => 'ACTIVE',
        ]);

        $counter->update(['status' => 'AVAILABLE']);

        return response()->json([
            'success' => true,
            'message' => "Assigned to {$counter->name} ({$counter->department->name}).",
            'data' => [
                'assignment' => $assignment->load('counter.department'),
            ],
        ]);
    }

    /**
     * Operator console state: current ticket, waiting queue, and next ticket
     */
    public function queue(Request $request): JsonResponse
    {
        $user = $request->user();
        $assignment = $user->activeAssignment?->load('counter.department.services');

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'No active counter assignment found. Please select a counter.',
            ], 400);
        }

        $counter = $assignment->counter;
        $department = $counter->department;

        // Current ticket at this counter
        $currentTicket = QueueTicket::with(['service', 'studentProfile.user', 'session'])
            ->where('counter_id', $counter->id)
            ->whereIn('status', ['CALLED', 'SERVING'])
            ->latest('called_at')
            ->first();

        // Waiting queue for department
        $waitingQueue = QueueTicket::with(['service', 'studentProfile.user'])
            ->where('department_id', $department->id)
            ->where('status', 'WAITING')
            ->orderBy('created_at', 'asc')
            ->get();

        $nextTicket = $waitingQueue->first();
        $departmentApplicationCounts = Department::where('is_active', true)
            ->withCount([
                'queueTickets as waiting_applications_count' => function ($query) {
                    $query->where('status', 'WAITING');
                },
            ])
            ->get()
            ->map(fn (Department $department) => [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code,
                'count' => $department->waiting_applications_count,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'operator' => $user,
                'counter' => $counter,
                'department' => $department,
                'current_ticket' => $currentTicket,
                'next_ticket' => $nextTicket,
                'waiting_count' => $waitingQueue->count(),
                'waiting_queue' => $waitingQueue,
                'department_application_counts' => $departmentApplicationCounts,
            ],
        ]);
    }

    /**
     * Call Next ticket
     */
    public function callNext(Request $request): JsonResponse
    {
        $user = $request->user();
        $assignment = $user->activeAssignment;

        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'No active counter.'], 400);
        }

        $counter = $assignment->counter;

        // Check if operator already has an unfinished ticket
        $activeTicket = QueueTicket::where('counter_id', $counter->id)
            ->whereIn('status', ['CALLED', 'SERVING'])
            ->first();

        if ($activeTicket) {
            return response()->json([
                'success' => false,
                'message' => "Please complete or mark no-show for token {$activeTicket->ticket_number} before calling next.",
            ], 422);
        }

        try {
            $ticket = $this->queueEngine->callNext($counter, $user);

            if (!$ticket) {
                return response()->json([
                    'success' => true,
                    'message' => 'Queue is empty. No waiting students at this time.',
                    'data' => ['ticket' => null],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Called token {$ticket->ticket_number} to {$counter->name}.",
                'data' => ['ticket' => $ticket->load(['service', 'studentProfile.user'])],
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Recall currently called ticket
     */
    public function recall(Request $request): JsonResponse
    {
        $user = $request->user();
        $assignment = $user->activeAssignment;
        $counter = $assignment->counter;

        $ticket = QueueTicket::where('counter_id', $counter->id)
            ->where('status', 'CALLED')
            ->first();

        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'No called ticket to recall.'], 404);
        }

        $ticket = $this->queueEngine->recallToken($ticket, $counter, $user);

        return response()->json([
            'success' => true,
            'message' => "Token {$ticket->ticket_number} has been recalled.",
            'data' => ['ticket' => $ticket],
        ]);
    }

    /**
     * Start serving student
     */
    public function start(Request $request): JsonResponse
    {
        $user = $request->user();
        $assignment = $user->activeAssignment;
        $counter = $assignment->counter;

        $ticket = QueueTicket::where('counter_id', $counter->id)
            ->where('status', 'CALLED')
            ->first();

        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'No called ticket to start.'], 404);
        }

        $ticket = $this->queueEngine->startService($ticket, $counter, $user);

        return response()->json([
            'success' => true,
            'message' => "Service started for token {$ticket->ticket_number}. Timer running.",
            'data' => ['ticket' => $ticket->load('session')],
        ]);
    }

    /**
     * Complete service
     */
    public function complete(Request $request): JsonResponse
    {
        $user = $request->user();
        $assignment = $user->activeAssignment;
        $counter = $assignment->counter;

        $ticket = QueueTicket::where('counter_id', $counter->id)
            ->whereIn('status', ['CALLED', 'SERVING'])
            ->first();

        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'No active ticket to complete.'], 404);
        }

        if ($request->filled('notes')) {
            $ticket->update(['notes' => trim($ticket->notes . " [Resolution: " . $request->input('notes') . "]")]);
        }

        $ticket = $this->queueEngine->completeService($ticket, $counter, $user);

        return response()->json([
            'success' => true,
            'message' => "Service completed for token {$ticket->ticket_number}.",
            'data' => ['ticket' => $ticket->load('session')],
        ]);
    }

    /**
     * Mark student as No-Show
     */
    public function noShow(Request $request): JsonResponse
    {
        $user = $request->user();
        $assignment = $user->activeAssignment;
        $counter = $assignment->counter;

        $ticket = QueueTicket::where('counter_id', $counter->id)
            ->where('status', 'CALLED')
            ->first();

        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'No called ticket to mark as no-show.'], 404);
        }

        $ticket = $this->queueEngine->markNoShow($ticket, $counter, $user);

        return response()->json([
            'success' => true,
            'message' => "Token {$ticket->ticket_number} marked as No-Show.",
            'data' => ['ticket' => $ticket],
        ]);
    }

    /**
     * Transfer ticket
     */
    public function transfer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'service_id' => 'nullable|exists:services,id',
        ]);

        $user = $request->user();
        $assignment = $user->activeAssignment;
        $counter = $assignment->counter;

        $ticket = QueueTicket::where('counter_id', $counter->id)
            ->whereIn('status', ['CALLED', 'SERVING'])
            ->first();

        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'No active ticket to transfer.'], 404);
        }

        $targetDept = Department::findOrFail($validated['department_id']);
        $targetService = !empty($validated['service_id']) ? Service::find($validated['service_id']) : null;

        $newTicket = $this->queueEngine->transferTicket($ticket, $targetDept, $targetService, $user);

        return response()->json([
            'success' => true,
            'message' => "Ticket transferred to {$targetDept->name}. New token is {$newTicket->ticket_number}.",
            'data' => ['new_ticket' => $newTicket],
        ]);
    }

    /**
     * Issue an instant walk-in ticket at the counter
     */
    public function walkInTicket(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'guest_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
        ]);

        $service = Service::with('department')->findOrFail($validated['service_id']);

        $guestUser = \App\Models\User::firstOrCreate(
            ['email' => 'walkin@queueless.local'],
            [
                'name' => 'Walk-In Visitor',
                'password' => bcrypt('walkin-guest-pass'),
                'role' => 'STUDENT',
                'is_active' => true,
            ]
        );

        $studentProfile = \App\Models\StudentProfile::firstOrCreate(
            ['user_id' => $guestUser->id],
            [
                'student_id_number' => 'WALK-IN-' . rand(1000, 9999),
                'department_name' => 'Campus Visitor',
                'batch' => 'N/A',
            ]
        );

        $notes = ($validated['guest_name'] ? "Guest: {$validated['guest_name']}. " : '') . ($validated['notes'] ?? '');
        $ticket = $this->queueEngine->joinQueue($studentProfile, $service, $notes);

        return response()->json([
            'success' => true,
            'message' => "Walk-in ticket {$ticket->ticket_number} successfully issued.",
            'data' => ['ticket' => $ticket->load(['service', 'department'])],
        ], 201);
    }

    /**
     * Toggle active counter status between AVAILABLE and BREAK
     */
    public function toggleStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $assignment = OperatorAssignment::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'No active counter assignment found.'], 400);
        }

        $counter = $assignment->counter;
        $counter->status = match ($counter->status) {
            'AVAILABLE' => 'BREAK',
            'BREAK' => 'AVAILABLE',
            default => 'AVAILABLE',
        };
        $counter->save();

        return response()->json([
            'success' => true,
            'message' => "Desk status toggled to {$counter->status}.",
            'data' => ['counter' => $counter],
        ]);
    }
}
