<?php

namespace App\Services;

use App\Models\Department;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\ServiceSession;
use Illuminate\Support\Facades\DB;

class QueuePredictionService
{
    /**
     * Factorial helper with caching/precomputation.
     */
    protected function factorial(int $n): float
    {
        if ($n <= 1) {
            return 1.0;
        }
        $result = 1.0;
        for ($i = 2; $i <= $n; $i++) {
            $result *= $i;
        }
        return $result;
    }

    /**
     * Erlang-C formula for multi-server M/M/c queue.
     * Calculates the probability C(c, a) that an arriving student must wait in queue.
     *
     * @param int $c Number of active servers/counters
     * @param float $a Offered traffic intensity (a = lambda / mu)
     * @param float $rho System utilization (rho = a / c)
     * @return float Probability of queue delay [0.0 - 1.0]
     */
    public function erlangC(int $c, float $a, float $rho): float
    {
        if ($c < 1 || $rho <= 0.0) {
            return 0.0;
        }

        // System is oversaturated or at boundary
        if ($rho >= 1.0) {
            return 1.0;
        }

        // Sum_{k=0}^{c-1} (a^k / k!)
        $sum = 0.0;
        for ($k = 0; $k < $c; $k++) {
            $sum += pow($a, $k) / $this->factorial($k);
        }

        // (a^c / c!) * (1 / (1 - rho))
        $lastTerm = (pow($a, $c) / $this->factorial($c)) * (1.0 / (1.0 - $rho));

        // P0 (probability of 0 students in system)
        $p0 = 1.0 / ($sum + $lastTerm);

        // Erlang-C: C(c, a) = P0 * lastTerm
        $cProb = $p0 * $lastTerm;

        return max(0.0, min(1.0, $cProb));
    }

    /**
     * Theoretical M/M/c expected waiting time in minutes (Wq).
     *
     * @param float $arrivalRate Students per minute (lambda)
     * @param float $serviceRate Students per minute per counter (mu = 1 / meanServiceTime)
     * @param int $activeCounters Active counters (c)
     * @return float Theoretical waiting time in minutes
     */
    public function theoreticalWaitTime(float $arrivalRate, float $serviceRate, int $activeCounters): float
    {
        if ($activeCounters < 1 || $serviceRate <= 0.0) {
            return 0.0;
        }

        $a = $arrivalRate / $serviceRate;
        $rho = $a / $activeCounters;

        if ($rho >= 0.99) {
            // High saturation asymptotic approximation
            $rho = 0.98;
        }

        $cProb = $this->erlangC($activeCounters, $a, $rho);

        // Wq = C(c, a) / (c * mu - lambda) = C(c, a) / (c * mu * (1 - rho))
        $denominator = ($activeCounters * $serviceRate) - $arrivalRate;
        if ($denominator <= 0.001) {
            $denominator = 0.001;
        }

        $wq = $cProb / $denominator;
        return max(0.0, $wq);
    }

    /**
     * Retrieve dynamic rolling average service time in minutes from the last N completed sessions.
     */
    public function getRollingServiceTimeMin(Department $department, ?Service $service = null, int $sampleSize = 30): float
    {
        $query = ServiceSession::query()
            ->join('queue_tickets', 'service_sessions.queue_ticket_id', '=', 'queue_tickets.id')
            ->where('queue_tickets.department_id', $department->id)
            ->whereNotNull('service_sessions.ended_at')
            ->where('service_sessions.duration_seconds', '>', 30);

        if ($service) {
            $query->where('queue_tickets.service_id', $service->id);
        }

        $recentDurations = $query->latest('service_sessions.ended_at')
            ->limit($sampleSize)
            ->pluck('service_sessions.duration_seconds');

        if ($recentDurations->count() >= 3) {
            $avgSeconds = $recentDurations->avg();
            return round($avgSeconds / 60.0, 2);
        }

        // Fallback to configured service or department benchmark
        if ($service && $service->estimated_duration_min > 0) {
            return (float) $service->estimated_duration_min;
        }

        return (float) ($department->avg_handling_time_min > 0 ? $department->avg_handling_time_min : 5);
    }

    /**
     * Compute current estimated arrival rate (students per minute) over the last 60 minutes.
     */
    public function getRecentArrivalRate(Department $department): float
    {
        $oneHourAgo = now()->subHour();
        $recentArrivals = QueueTicket::where('department_id', $department->id)
            ->where('created_at', '>=', $oneHourAgo)
            ->count();

        // arrivals per minute (minimum baseline 0.1/min to avoid division by zero)
        return max(0.1, round($recentArrivals / 60.0, 3));
    }

    /**
     * Estimate wait time for a student at a given position in queue.
     * Combines M/M/c Erlang-C queuing theory with deterministic rolling service time.
     *
     * @param Department $department
     * @param Service $service
     * @param int $peopleAhead Number of waiting students ahead of this ticket
     * @return array [estimated_wait_min, people_ahead, active_counters, utilization_pct, erlang_c_prob]
     */
    public function estimateWaitTime(Department $department, Service $service, int $peopleAhead): array
    {
        // 1. Determine active counters
        $activeCounters = $department->counters()
            ->whereIn('status', ['AVAILABLE', 'BUSY'])
            ->count();

        if ($activeCounters < 1) {
            // Default to at least 1 counter to avoid infinite wait display
            $activeCounters = max(1, $department->counters()->count());
        }

        // 2. Rolling average service time (minutes)
        $avgServiceTimeMin = $this->getRollingServiceTimeMin($department, $service);
        $serviceRate = 1.0 / max(1.0, $avgServiceTimeMin); // students per min per counter

        // 3. Arrival rate (students per minute)
        $arrivalRate = $this->getRecentArrivalRate($department);

        // 4. System utilization
        $offeredLoad = $arrivalRate / $serviceRate;
        $utilization = min(0.99, $offeredLoad / $activeCounters);
        $utilizationPct = round($utilization * 100, 1);

        // 5. Theoretical M/M/c delay
        $theoreticalWq = $this->theoreticalWaitTime($arrivalRate, $serviceRate, $activeCounters);

        // 6. Deterministic position delay based on active parallel counters
        // If there are k people ahead and c counters, wait rounds = ceil(k / c)
        if ($peopleAhead <= 0) {
            $deterministicDelay = 0.0;
        } else {
            $rounds = ceil($peopleAhead / $activeCounters);
            $deterministicDelay = $rounds * $avgServiceTimeMin;
        }

        // 7. Practical Production Hybrid Blend
        // Weight: 70% deterministic queue position + 30% stochastic variance
        $hybridWait = (0.70 * $deterministicDelay) + (0.30 * $theoreticalWq);

        // Friendly minimums: if people ahead > 0, wait is at least 1 min
        $estimatedWaitMin = ($peopleAhead > 0) ? max(1, (int) round($hybridWait)) : 0;

        $erlangCProb = $this->erlangC($activeCounters, $offeredLoad, $utilization);

        return [
            'estimated_wait_min' => $estimatedWaitMin,
            'people_ahead' => $peopleAhead,
            'active_counters' => $activeCounters,
            'utilization_pct' => $utilizationPct,
            'avg_service_time_min' => $avgServiceTimeMin,
            'erlang_c_prob' => round($erlangCProb * 100, 1),
        ];
    }

    /**
     * Generate operational recommendations for administrators.
     */
    public function generateCapacityRecommendations(): array
    {
        $recommendations = [];
        $departments = Department::with('counters')->where('is_active', true)->get();

        foreach ($departments as $dept) {
            $waitingCount = QueueTicket::where('department_id', $dept->id)
                ->where('status', 'WAITING')
                ->count();

            $activeCounters = $dept->counters->whereIn('status', ['AVAILABLE', 'BUSY'])->count();
            $totalCounters = $dept->counters->count();

            $avgServiceTime = $this->getRollingServiceTimeMin($dept);
            $arrivalRate = $this->getRecentArrivalRate($dept);
            $serviceRate = 1.0 / max(1.0, $avgServiceTime);

            $offeredLoad = $arrivalRate / $serviceRate;
            $utilization = ($activeCounters > 0) ? ($offeredLoad / $activeCounters) : 1.0;
            $utilizationPct = round($utilization * 100, 1);

            // Trigger criteria: High utilization (> 85%) or severe queue backlog
            if ($utilization >= 0.85 || $waitingCount >= ($activeCounters * 4)) {
                $targetCapacity = (int) ceil($offeredLoad / 0.70);
                $neededCounters = max(1, $targetCapacity - $activeCounters);
                $availableToOpen = max(0, $totalCounters - $activeCounters);

                $recommendations[] = [
                    'department_id' => $dept->id,
                    'department_name' => $dept->name,
                    'department_code' => $dept->code,
                    'utilization_pct' => min(99.0, $utilizationPct),
                    'waiting_count' => $waitingCount,
                    'active_counters' => $activeCounters,
                    'total_counters' => $totalCounters,
                    'severity' => ($utilization >= 0.90 || $waitingCount > 15) ? 'CRITICAL' : 'WARNING',
                    'message' => "{$dept->name} is operating at {$utilizationPct}% utilization with {$waitingCount} waiting students. Consider activating {$neededCounters} additional counter(s).",
                    'actionable' => ($availableToOpen > 0) ? "Open Counter #" . ($activeCounters + 1) : "Staff new overflow desk",
                ];
            }
        }

        return $recommendations;
    }
}
