<?php

namespace Tests\Feature;

use App\Models\Counter;
use App\Models\Department;
use App\Models\OperatorAssignment;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $student;
    protected StudentProfile $studentProfile;
    protected Department $department;
    protected Service $service;
    protected Counter $counter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@baust.edu.bd',
            'password' => bcrypt('password'),
            'role' => 'ADMIN',
        ]);

        $this->department = Department::create([
            'code' => 'ACC',
            'name' => 'Accounts & Finance',
            'avg_handling_time_min' => 8,
            'is_active' => true,
        ]);

        $this->service = Service::create([
            'department_id' => $this->department->id,
            'name' => 'Tuition Fee Payment',
            'code' => 'TFP',
            'estimated_duration_min' => 8,
            'is_active' => true,
        ]);

        $this->counter = Counter::create([
            'department_id' => $this->department->id,
            'counter_number' => 1,
            'name' => 'Counter 1',
            'status' => 'AVAILABLE',
        ]);

        $this->operator = User::create([
            'name' => 'Operator User',
            'email' => 'operator@baust.edu.bd',
            'password' => bcrypt('password'),
            'role' => 'COUNTER_OPERATOR',
        ]);

        OperatorAssignment::create([
            'user_id' => $this->operator->id,
            'counter_id' => $this->counter->id,
            'started_at' => now(),
            'status' => 'ACTIVE',
        ]);

        $this->student = User::create([
            'name' => 'Student User',
            'email' => 'student@baust.edu.bd',
            'password' => bcrypt('password'),
            'role' => 'STUDENT',
        ]);

        $this->studentProfile = StudentProfile::create([
            'user_id' => $this->student->id,
            'student_id_number' => '20210104001',
            'department_name' => 'CSE',
            'batch' => '14th',
        ]);
    }

    public function test_auth_login_returns_token_and_user_profile(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'student@baust.edu.bd',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'student@baust.edu.bd')
            ->assertJsonPath('data.user.role', 'STUDENT');
    }

    public function test_get_departments_lists_active_departments_with_stats(): void
    {
        $response = $this->getJson('/api/departments');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'code', 'name', 'waiting_count', 'active_counters', 'services']
                ]
            ]);
    }

    public function test_student_can_join_queue_via_api(): void
    {
        $response = $this->actingAs($this->student)->postJson('/api/queues/join', [
            'service_id' => $this->service->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.ticket.ticket_number', 'ACC-001')
            ->assertJsonPath('data.ticket.status', 'WAITING');
    }

    public function test_operator_call_next_via_api(): void
    {
        // Enqueue ticket
        $ticket = QueueTicket::create([
            'ticket_number' => 'ACC-001',
            'sequence_number' => 1,
            'department_id' => $this->department->id,
            'service_id' => $this->service->id,
            'student_profile_id' => $this->studentProfile->id,
            'status' => 'WAITING',
            'estimated_wait_time_min' => 8,
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($this->operator)->postJson('/api/operator/call-next');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.ticket.ticket_number', 'ACC-001');

        $this->assertEquals('CALLED', $ticket->fresh()->status);
    }

    public function test_admin_metrics_endpoint_restricted_to_admins(): void
    {
        // Student should be forbidden (403)
        $forbidden = $this->actingAs($this->student)->getJson('/api/admin/metrics');
        $forbidden->assertStatus(403);

        // Admin should succeed (200)
        $allowed = $this->actingAs($this->admin)->getJson('/api/admin/metrics');
        $allowed->assertStatus(200)->assertJsonPath('success', true);
    }

    public function test_public_display_live_returns_now_serving(): void
    {
        $response = $this->getJson('/api/display/live');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => ['now_serving', 'recently_called', 'departments', 'server_time']
            ]);
    }
}
