<?php

namespace Tests\Feature;

use App\Models\Counter;
use App\Models\Department;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\QueueEngineService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueEngineTest extends TestCase
{
    use RefreshDatabase;

    protected QueueEngineService $queueEngine;
    protected Department $dept;
    protected Service $service;
    protected Counter $counter;
    protected User $operator;
    protected User $studentUser1;
    protected StudentProfile $studentProfile1;
    protected User $studentUser2;
    protected StudentProfile $studentProfile2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->queueEngine = app(QueueEngineService::class);

        // Seed basic department & service
        $this->dept = Department::create([
            'code' => 'ACC',
            'name' => 'Accounts & Finance',
            'avg_handling_time_min' => 8,
            'is_active' => true,
        ]);

        $this->service = Service::create([
            'department_id' => $this->dept->id,
            'name' => 'Dues Clearance',
            'code' => 'DC',
            'estimated_duration_min' => 8,
            'is_active' => true,
        ]);

        $this->counter = Counter::create([
            'department_id' => $this->dept->id,
            'counter_number' => 1,
            'name' => 'Counter 1',
            'status' => 'AVAILABLE',
        ]);

        $this->operator = User::create([
            'name' => 'Accounts Officer',
            'email' => 'operator@baust.edu.bd',
            'password' => bcrypt('password'),
            'role' => 'COUNTER_OPERATOR',
        ]);

        $this->studentUser1 = User::create([
            'name' => 'Student 1',
            'email' => 'student1@baust.edu.bd',
            'password' => bcrypt('password'),
            'role' => 'STUDENT',
        ]);
        $this->studentProfile1 = StudentProfile::create([
            'user_id' => $this->studentUser1->id,
            'student_id_number' => '20210104001',
            'department_name' => 'CSE',
            'batch' => '14th',
        ]);

        $this->studentUser2 = User::create([
            'name' => 'Student 2',
            'email' => 'student2@baust.edu.bd',
            'password' => bcrypt('password'),
            'role' => 'STUDENT',
        ]);
        $this->studentProfile2 = StudentProfile::create([
            'user_id' => $this->studentUser2->id,
            'student_id_number' => '20210104002',
            'department_name' => 'CSE',
            'batch' => '14th',
        ]);
    }

    public function test_student_joins_queue_and_receives_sequential_token(): void
    {
        $ticket1 = $this->queueEngine->joinQueue($this->studentProfile1, $this->service);
        $this->assertEquals('ACC-001', $ticket1->ticket_number);
        $this->assertEquals('WAITING', $ticket1->status);

        $ticket2 = $this->queueEngine->joinQueue($this->studentProfile2, $this->service);
        $this->assertEquals('ACC-002', $ticket2->ticket_number);
        $this->assertEquals('WAITING', $ticket2->status);
    }

    public function test_duplicate_active_queue_joining_is_prevented(): void
    {
        $this->queueEngine->joinQueue($this->studentProfile1, $this->service);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("You already hold active token");

        // Attempt joining again with same student in same department
        $this->queueEngine->joinQueue($this->studentProfile1, $this->service);
    }

    public function test_operator_call_next_assigns_earliest_waiting_ticket_atomically(): void
    {
        $ticket1 = $this->queueEngine->joinQueue($this->studentProfile1, $this->service);
        $ticket2 = $this->queueEngine->joinQueue($this->studentProfile2, $this->service);

        // Operator calls next
        $called = $this->queueEngine->callNext($this->counter, $this->operator);

        $this->assertNotNull($called);
        $this->assertEquals($ticket1->id, $called->id);
        $this->assertEquals('CALLED', $called->fresh()->status);
        $this->assertEquals($this->counter->id, $called->counter_id);
        $this->assertEquals('BUSY', $this->counter->fresh()->status);
    }

    public function test_full_service_lifecycle_call_start_complete(): void
    {
        $ticket = $this->queueEngine->joinQueue($this->studentProfile1, $this->service);

        // 1. Call Next
        $called = $this->queueEngine->callNext($this->counter, $this->operator);
        $this->assertEquals('CALLED', $called->status);

        // 2. Start Service
        $started = $this->queueEngine->startService($called, $this->counter, $this->operator);
        $this->assertEquals('SERVING', $started->status);
        $this->assertNotNull($started->served_at);

        // 3. Complete Service
        $completed = $this->queueEngine->completeService($started, $this->counter, $this->operator);
        $this->assertEquals('COMPLETED', $completed->status);
        $this->assertNotNull($completed->completed_at);
        $this->assertEquals('AVAILABLE', $this->counter->fresh()->status);
    }

    public function test_student_can_cancel_waiting_ticket(): void
    {
        $ticket = $this->queueEngine->joinQueue($this->studentProfile1, $this->service);
        $this->assertEquals('WAITING', $ticket->status);

        $cancelled = $this->queueEngine->cancelTicket($ticket, $this->studentUser1, 'Changed mind');
        $this->assertEquals('CANCELLED', $cancelled->status);
    }
}
