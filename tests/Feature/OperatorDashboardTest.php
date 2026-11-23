<?php

namespace Tests\Feature;

use App\Models\Counter;
use App\Models\Department;
use App\Models\OperatorAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_dashboard_shows_campus_service_units(): void
    {
        $operator = User::create([
            'name' => 'Accounts Operator',
            'email' => 'operator@example.test',
            'password' => bcrypt('password'),
            'role' => 'COUNTER_OPERATOR',
        ]);

        $assignedDepartment = Department::create([
            'code' => 'ACC',
            'name' => 'Accounts & Finance',
            'is_active' => true,
        ]);
        $otherDepartment = Department::create([
            'code' => 'MED',
            'name' => 'Medical Center',
            'is_active' => true,
        ]);

        $counter = Counter::create([
            'department_id' => $assignedDepartment->id,
            'counter_number' => 1,
            'name' => 'Accounts Counter 1',
            'status' => 'AVAILABLE',
        ]);
        Counter::create([
            'department_id' => $otherDepartment->id,
            'counter_number' => 1,
            'name' => 'Medical Counter 1',
            'status' => 'AVAILABLE',
        ]);

        OperatorAssignment::create([
            'user_id' => $operator->id,
            'counter_id' => $counter->id,
            'started_at' => now(),
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($operator)
            ->get(route('operator.dashboard'))
            ->assertOk()
            ->assertSee('Accounts &amp; Finance', false)
            ->assertSee('Medical Counter 1');
    }

    public function test_operator_login_redirects_to_the_operator_desk(): void
    {
        User::create([
            'name' => 'Accounts Operator',
            'email' => 'operator-login@example.test',
            'password' => bcrypt('password'),
            'role' => 'COUNTER_OPERATOR',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'operator-login@example.test',
            'password' => 'password',
            'login_role' => 'COUNTER_OPERATOR',
        ])->assertRedirect(route('operator.console'));
    }

    public function test_operator_desk_opens_the_counter_workstation(): void
    {
        $operator = User::create([
            'name' => 'Registrar Operator',
            'email' => 'registrar-operator@example.test',
            'password' => bcrypt('password'),
            'role' => 'COUNTER_OPERATOR',
        ]);
        $department = Department::create([
            'code' => 'REG',
            'name' => 'Registrar Office',
            'is_active' => true,
        ]);
        $counter = Counter::create([
            'department_id' => $department->id,
            'counter_number' => 1,
            'name' => 'Counter 1',
            'status' => 'AVAILABLE',
        ]);
        OperatorAssignment::create([
            'user_id' => $operator->id,
            'counter_id' => $counter->id,
            'started_at' => now(),
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($operator)
            ->get(route('operator.console'))
            ->assertOk()
            ->assertSee('Active Counter Workstation Terminal')
            ->assertSee('Registrar Office');
    }
}
