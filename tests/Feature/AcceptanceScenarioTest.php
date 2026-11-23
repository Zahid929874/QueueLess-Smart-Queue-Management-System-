<?php

namespace Tests\Feature;

use App\Models\Counter;
use App\Models\Department;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\ServiceSession;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcceptanceScenarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_section_33_acceptance_scenario(): void
    {
        // 1. Setup university department: Accounts & Finance
        $accDept = Department::create([
            'code' => 'ACC',
            'name' => 'Accounts & Finance',
            'avg_handling_time_min' => 8,
            'is_active' => true,
        ]);

        $duesService = Service::create([
            'department_id' => $accDept->id,
            'name' => 'Dues Clearance',
            'code' => 'DC',
            'estimated_duration_min' => 8,
            'is_active' => true,
        ]);

        $counter1 = Counter::create([
            'department_id' => $accDept->id,
            'counter_number' => 1,
            'name' => 'Counter 1',
            'status' => 'AVAILABLE',
        ]);

        // Users
        $studentUser1 = User::create([
            'name' => 'Tanvir Hasan',
            'email' => 'student1@baust.edu.bd',
            'password' => bcrypt('password'),
            'role' => 'STUDENT',
        ]);
        $profile1 = StudentProfile::create([
            'user_id' => $studentUser1->id,
            'student_id_number' => '20210104001',
            'department_name' => 'CSE',
            'batch' => '14th',
        ]);

        $studentUser2 = User::create([
            'name' => 'Sadia Afrin',
            'email' => 'student2@baust.edu.bd',
            'password' => bcrypt('password'),
            'role' => 'STUDENT',
        ]);
        $profile2 = StudentProfile::create([
            'user_id' => $studentUser2->id,
            'student_id_number' => '20210104002',
            'department_name' => 'CSE',
            'batch' => '14th',
        ]);

        $operatorUser = User::create([
            'name' => 'Accounts Officer',
            'email' => 'acc.operator@baust.edu.bd',
            'password' => bcrypt('password'),
            'role' => 'COUNTER_OPERATOR',
        ]);

        $adminUser = User::create([
            'name' => 'Administrator',
            'email' => 'admin@baust.edu.bd',
            'password' => bcrypt('password'),
            'role' => 'ADMIN',
        ]);

        // -------------------------------------------------------------
        // STEP 1: Student 1 Joins Queue
        // -------------------------------------------------------------
        $joinResponse1 = $this->actingAs($studentUser1)->postJson('/api/queues/join', [
            'service_id' => $duesService->id,
        ]);

        $joinResponse1->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.ticket.ticket_number', 'ACC-001')
            ->assertJsonPath('data.ticket.status', 'WAITING');

        // Check Student 1 active status: People Ahead = 0
        $activeResponse1 = $this->actingAs($studentUser1)->getJson('/api/queues/active');
        $activeResponse1->assertStatus(200)
            ->assertJsonPath('data.active_ticket.ticket_number', 'ACC-001')
            ->assertJsonPath('data.people_ahead', 0)
            ->assertJsonPath('data.position', 1);

        // -------------------------------------------------------------
        // STEP 2: Student 2 Joins Queue and receives ACC-002
        // -------------------------------------------------------------
        $joinResponse2 = $this->actingAs($studentUser2)->postJson('/api/queues/join', [
            'service_id' => $duesService->id,
        ]);

        $joinResponse2->assertStatus(201)
            ->assertJsonPath('data.ticket.ticket_number', 'ACC-002');

        // Verify Student 2 sees 1 person ahead
        $activeResponse2 = $this->actingAs($studentUser2)->getJson('/api/queues/active');
        $activeResponse2->assertStatus(200)
            ->assertJsonPath('data.people_ahead', 1)
            ->assertJsonPath('data.position', 2);

        // -------------------------------------------------------------
        // STEP 3: Operator assigns counter and calls next (ACC-001)
        // -------------------------------------------------------------
        $this->actingAs($operatorUser)->postJson('/api/operator/assign-counter', [
            'counter_id' => $counter1->id,
        ])->assertStatus(200);

        $callNextResponse = $this->actingAs($operatorUser)->postJson('/api/operator/call-next');
        $callNextResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.ticket.ticket_number', 'ACC-001')
            ->assertJsonPath('data.ticket.status', 'CALLED');

        // -------------------------------------------------------------
        // STEP 4: Verify Student 1 dashboard immediately updates to CALLED
        // -------------------------------------------------------------
        $student1AfterCall = $this->actingAs($studentUser1)->getJson('/api/queues/active');
        $student1AfterCall->assertStatus(200)
            ->assertJsonPath('data.active_ticket.status', 'CALLED')
            ->assertJsonPath('data.active_ticket.counter_id', $counter1->id);

        // -------------------------------------------------------------
        // STEP 5: Verify Public TV Display updates with Now Serving ACC-001
        // -------------------------------------------------------------
        $displayResponse = $this->getJson('/api/display/live/ACC');
        $displayResponse->assertStatus(200)
            ->assertJsonPath('data.now_serving.0.ticket_number', 'ACC-001')
            ->assertJsonPath('data.now_serving.0.counter_name', 'Counter 1');

        // -------------------------------------------------------------
        // STEP 6: Operator Starts Service
        // -------------------------------------------------------------
        $startResponse = $this->actingAs($operatorUser)->postJson('/api/operator/start');
        $startResponse->assertStatus(200)
            ->assertJsonPath('data.ticket.status', 'SERVING');

        // -------------------------------------------------------------
        // STEP 7: Operator Completes Service
        // -------------------------------------------------------------
        $completeResponse = $this->actingAs($operatorUser)->postJson('/api/operator/complete');
        $completeResponse->assertStatus(200)
            ->assertJsonPath('data.ticket.status', 'COMPLETED');

        // Counter 1 is now AVAILABLE again
        $this->assertEquals('AVAILABLE', $counter1->fresh()->status);

        // -------------------------------------------------------------
        // STEP 8: Operator can now call ACC-002
        // -------------------------------------------------------------
        $callNextResponse2 = $this->actingAs($operatorUser)->postJson('/api/operator/call-next');
        $callNextResponse2->assertStatus(200)
            ->assertJsonPath('data.ticket.ticket_number', 'ACC-002')
            ->assertJsonPath('data.ticket.status', 'CALLED');

        // -------------------------------------------------------------
        // STEP 9: Admin Analytics reflects completed ticket and live metrics
        // -------------------------------------------------------------
        $adminMetricsResponse = $this->actingAs($adminUser)->getJson('/api/admin/metrics');
        $adminMetricsResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.completed_today', 1)
            ->assertJsonPath('data.currently_serving', 1); // ACC-002 is now CALLED
    }
}
