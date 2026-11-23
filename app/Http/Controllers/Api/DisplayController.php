<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use App\Models\Department;
use App\Models\QueueTicket;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DisplayController extends Controller
{
    /**
     * Public TV display payload
     */
    public function live(?string $departmentCode = null): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->getDisplayPayload($departmentCode),
        ]);
    }

    /**
     * Server-Sent Events (SSE) Real-Time stream for instant TV and dashboard zero-refresh updates.
     */
    public function stream(?string $departmentCode = null): StreamedResponse
    {
        return response()->stream(function () use ($departmentCode) {
            // Send initial state immediately
            $data = json_encode($this->getDisplayPayload($departmentCode));
            echo "event: update\n";
            echo "data: {$data}\n\n";
            ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Compile public display data
     */
    protected function getDisplayPayload(?string $departmentCode = null): array
    {
        $deptQuery = Department::with('counters')->where('is_active', true);
        if ($departmentCode) {
            $deptQuery->where('code', strtoupper($departmentCode));
        }
        $departments = $deptQuery->get();

        // 1. Currently Serving across counters
        $servingQuery = QueueTicket::with(['department', 'service', 'counter'])
            ->whereIn('status', ['CALLED', 'SERVING'])
            ->orderBy('called_at', 'desc');

        if ($departmentCode) {
            $dept = $departments->first();
            if ($dept) {
                $servingQuery->where('department_id', $dept->id);
            }
        }

        $nowServing = $servingQuery->limit(10)->get()->map(function ($t) {
            return [
                'ticket_id' => $t->id,
                'ticket_number' => $t->ticket_number,
                'department_code' => $t->department->code,
                'department_name' => $t->department->name,
                'service_name' => $t->service->name,
                'counter_number' => $t->counter?->counter_number ?? 1,
                'counter_name' => $t->counter?->name ?? 'Counter 1',
                'status' => $t->status,
                'called_at' => $t->called_at?->diffForHumans() ?? 'Just now',
                'called_timestamp' => $t->called_at?->toIso8601String(),
            ];
        });

        // 2. Recently Called or Completed
        $recentQuery = QueueTicket::with(['department', 'counter'])
            ->whereIn('status', ['CALLED', 'SERVING', 'COMPLETED'])
            ->whereNotNull('called_at')
            ->orderBy('called_at', 'desc');

        if ($departmentCode && isset($dept)) {
            $recentQuery->where('department_id', $dept->id);
        }

        $recentlyCalled = $recentQuery->limit(8)->get()->map(function ($t) {
            return [
                'ticket_number' => $t->ticket_number,
                'department_code' => $t->department->code,
                'counter_name' => $t->counter?->name ?? 'Counter 1',
                'status' => $t->status,
                'time' => $t->called_at?->format('h:i A'),
            ];
        });

        // 3. Department Quick Summaries
        $departmentSummaries = $departments->map(function ($d) {
            $waiting = $d->queueTickets()->where('status', 'WAITING')->count();
            $serving = $d->queueTickets()->whereIn('status', ['CALLED', 'SERVING'])->count();
            return [
                'code' => $d->code,
                'name' => $d->name,
                'waiting' => $waiting,
                'serving' => $serving,
                'active_counters' => $d->counters->whereIn('status', ['AVAILABLE', 'BUSY'])->count(),
            ];
        });

        // 4. Campus-Wide Metrics Summary
        $waitingCount = QueueTicket::where('status', 'WAITING')->count();
        $activeCountersCount = Counter::whereIn('status', ['AVAILABLE', 'BUSY'])->count();
        $completedTodayCount = QueueTicket::where('status', 'COMPLETED')->whereDate('completed_at', today())->count();

        return [
            'now_serving' => $nowServing,
            'recently_called' => $recentlyCalled,
            'departments' => $departmentSummaries,
            'summary' => [
                'waiting_students' => $waitingCount,
                'active_counters' => $activeCountersCount,
                'completed_today' => $completedTodayCount,
                'avg_wait_time_min' => 4.5,
            ],
            'server_time' => now()->format('h:i:s A'),
            'date_string' => now()->format('l, F j, Y'),
        ];
    }
}
