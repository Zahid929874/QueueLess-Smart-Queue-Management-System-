<?php

namespace Tests\Unit;

use App\Services\QueuePredictionService;
use PHPUnit\Framework\TestCase;

class QueuePredictionTest extends TestCase
{
    protected QueuePredictionService $predictionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->predictionService = new QueuePredictionService();
    }

    public function test_erlang_c_formula_calculates_correct_probabilities(): void
    {
        // Test single server c=1, traffic intensity a = 0.5, rho = 0.5
        // In M/M/1, Erlang-C delay probability equals rho = 0.5
        $prob1 = $this->predictionService->erlangC(1, 0.5, 0.5);
        $this->assertEqualsWithDelta(0.5, $prob1, 0.01);

        // Test multi-server c=3, traffic load a = 2.0, rho = 2/3 = 0.6667
        $prob3 = $this->predictionService->erlangC(3, 2.0, 2.0 / 3.0);
        $this->assertGreaterThan(0.0, $prob3);
        $this->assertLessThan(1.0, $prob3);

        // Test boundary when servers = 0 or rho <= 0
        $this->assertEquals(0.0, $this->predictionService->erlangC(0, 1.0, 0.5));
        $this->assertEquals(0.0, $this->predictionService->erlangC(2, 0.0, 0.0));
    }

    public function test_theoretical_wait_time_increases_with_traffic_load(): void
    {
        $counters = 3;
        $serviceRate = 0.2; // 5 min per student -> 0.2 students/min

        // Lower arrival rate: lambda = 0.3 (utilization = 0.3 / (3*0.2) = 50%)
        $waitLow = $this->predictionService->theoreticalWaitTime(0.3, $serviceRate, $counters);

        // Higher arrival rate: lambda = 0.5 (utilization = 0.5 / (3*0.2) = 83.3%)
        $waitHigh = $this->predictionService->theoreticalWaitTime(0.5, $serviceRate, $counters);

        $this->assertGreaterThan($waitLow, $waitHigh);
    }
}
