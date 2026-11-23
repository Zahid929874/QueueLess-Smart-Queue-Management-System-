<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\QueuePredictionService;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    public function __construct(
        protected QueuePredictionService $predictionService
    ) {}

    /**
     * List all university departments with live queue stats and M/M/c wait metrics.
     */
    public function index(): JsonResponse
    {
        $departments = Department::with(['services' => function ($q) {
            $q->where('is_active', true);
        }, 'counters'])
            ->where('is_active', true)
            ->get()
            ->map(function ($dept) {
                $waitingCount = $dept->queueTickets()->where('status', 'WAITING')->count();
                $servingCount = $dept->queueTickets()->whereIn('status', ['CALLED', 'SERVING'])->count();
                $activeCounters = $dept->counters->whereIn('status', ['AVAILABLE', 'BUSY'])->count();

                $firstService = $dept->services->first();
                $waitStats = $firstService
                    ? $this->predictionService->estimateWaitTime($dept, $firstService, $waitingCount)
                    : ['estimated_wait_min' => 0, 'utilization_pct' => 0];

                return [
                    'id' => $dept->id,
                    'code' => $dept->code,
                    'name' => $dept->name,
                    'description' => $dept->description,
                    'location' => $dept->location,
                    'avg_handling_time_min' => $dept->avg_handling_time_min,
                    'waiting_count' => $waitingCount,
                    'serving_count' => $servingCount,
                    'active_counters' => $activeCounters,
                    'total_counters' => $dept->counters->count(),
                    'estimated_wait_min' => $waitStats['estimated_wait_min'],
                    'utilization_pct' => $waitStats['utilization_pct'],
                    'services' => $dept->services,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $departments,
        ]);
    }

    /**
     * Get specific department details
     */
    public function show(int $id): JsonResponse
    {
        $department = Department::with(['services' => function ($q) {
            $q->where('is_active', true);
        }, 'counters'])->findOrFail($id);

        $waitingCount = $department->queueTickets()->where('status', 'WAITING')->count();
        $servingCount = $department->queueTickets()->whereIn('status', ['CALLED', 'SERVING'])->count();

        return response()->json([
            'success' => true,
            'data' => [
                'department' => $department,
                'waiting_count' => $waitingCount,
                'serving_count' => $servingCount,
            ],
        ]);
    }

    /**
     * Get services for a specific department
     */
    public function services(int $id): JsonResponse
    {
        $department = Department::findOrFail($id);
        $services = $department->services()->where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }
}
