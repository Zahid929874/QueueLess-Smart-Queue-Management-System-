<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Services\QueueEngineService;
use App\Services\QueuePredictionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function __construct(
        protected QueueEngineService $queueEngine,
        protected QueuePredictionService $predictionService
    ) {}

    /**
     * Join digital queue
     */
    public function join(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $studentProfile = $user->studentProfile;

        if (!$studentProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Only registered students with a student profile can join queues.',
            ], 403);
        }

        $service = Service::with('department')->findOrFail($validated['service_id']);

        try {
            $ticket = $this->queueEngine->joinQueue($studentProfile, $service, $validated['notes'] ?? null);

            return response()->json([
                'success' => true,
                'message' => "Successfully joined queue. Your token is {$ticket->ticket_number}.",
                'data' => [
                    'ticket' => $ticket->load(['department', 'service']),
                ],
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get active ticket for current student with real-time dynamic wait estimation & position
     */
    public function active(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentProfile = $user->studentProfile;

        if (!$studentProfile) {
            return response()->json([
                'success' => true,
                'data' => ['active_ticket' => null],
            ]);
        }

        $ticket = QueueTicket::with(['department', 'service', 'counter'])
            ->where('student_profile_id', $studentProfile->id)
            ->whereIn('status', ['WAITING', 'CALLED', 'SERVING'])
            ->latest()
            ->first();

        if (!$ticket) {
            return response()->json([
                'success' => true,
                'data' => ['active_ticket' => null],
            ]);
        }

        // Calculate people ahead & dynamic live wait time
        $peopleAhead = 0;
        $position = 1;

        if ($ticket->status === 'WAITING') {
            $peopleAhead = QueueTicket::where('department_id', $ticket->department_id)
                ->where('status', 'WAITING')
                ->where('id', '<', $ticket->id)
                ->count();

            $position = $peopleAhead + 1;

            $prediction = $this->predictionService->estimateWaitTime(
                $ticket->department,
                $ticket->service,
                $peopleAhead
            );
            $ticket->estimated_wait_time_min = $prediction['estimated_wait_min'];
            $activeCounters = $prediction['active_counters'];
            $utilizationPct = $prediction['utilization_pct'];
        } else {
            $activeCounters = $ticket->department->counters()->whereIn('status', ['AVAILABLE', 'BUSY'])->count();
            $utilizationPct = 100;
        }

        // Currently serving token at the department/counter
        $currentServing = QueueTicket::where('department_id', $ticket->department_id)
            ->whereIn('status', ['CALLED', 'SERVING'])
            ->latest('called_at')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'active_ticket' => $ticket,
                'position' => $position,
                'people_ahead' => $peopleAhead,
                'estimated_wait_min' => $ticket->estimated_wait_time_min,
                'active_counters' => $activeCounters,
                'utilization_pct' => $utilizationPct,
                'current_serving_token' => $currentServing?->ticket_number ?? 'None',
                'current_serving_counter' => $currentServing?->counter?->name ?? 'None',
            ],
        ]);
    }

    /**
     * Inspect ticket
     */
    public function show(int $id): JsonResponse
    {
        $ticket = QueueTicket::with(['department', 'service', 'counter', 'events'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $ticket,
        ]);
    }

    /**
     * Cancel ticket
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $ticket = QueueTicket::findOrFail($id);
        $user = $request->user();

        // Ensure student owns ticket unless admin
        if (!$user->isAdmin() && $ticket->studentProfile?->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to cancel this ticket.',
            ], 403);
        }

        try {
            $cancelled = $this->queueEngine->cancelTicket($ticket, $user, $request->input('reason', 'Cancelled by student'));

            return response()->json([
                'success' => true,
                'message' => "Ticket {$cancelled->ticket_number} was successfully cancelled.",
                'data' => $cancelled,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Student queue history
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentProfile = $user->studentProfile;

        if (!$studentProfile) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $tickets = QueueTicket::with(['department', 'service', 'counter'])
            ->where('student_profile_id', $studentProfile->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $tickets,
        ]);
    }
}
