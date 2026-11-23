<?php

namespace Database\Seeders;

use App\Models\Counter;
use App\Models\Department;
use App\Models\OperatorAssignment;
use App\Models\QueueEvent;
use App\Models\QueueMetric;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\ServiceSession;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\AdminInvite;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password');

        $demoAdminToken = 'DEMO-ADMIN-INVITE';
        AdminInvite::updateOrCreate(
            ['token_hash' => hash('sha256', $demoAdminToken)],
            [
                'institutional_mail' => 'demo.admin@queueless.local',
                'tier' => 'Super Admin',
                'managed_service_unit_ids' => null,
                'expires_at' => now()->addDays(7),
                'used_at' => null,
                'used_by' => null,
            ]
        );
        if ($this->command) {
            $this->command->info("Demo admin invite: {$demoAdminToken}");
        }

        DB::table('invite_codes')->updateOrInsert([
            'code' => 'OP-2026-CLEARANCE',
        ], [
            'code' => 'OP-2026-CLEARANCE',
            'service_unit_id' => null,
            'expires_at' => now()->addYear(),
            'max_uses' => 100,
            'used_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Create Administrator
        $admin = User::firstOrCreate(
            ['email' => 'admin@queueless.local'],
            [
                'name' => 'System Administrator',
                'password' => $defaultPassword,
                'role' => 'ADMIN',
                'phone' => '01711000001',
                'is_active' => true,
            ]
        );

        // 2. Define University Service Departments & Counter Configurations
        $departmentsData = [
            [
                'code' => 'REG',
                'name' => 'Registrar Office',
                'description' => 'Academic records, transcripts, certificates, and student registration.',
                'location' => 'Admin Building, Level 2',
                'avg_handling_time_min' => 6,
                'counter_count' => 4,
                'services' => [
                    ['name' => 'Transcript Request', 'code' => 'TR', 'duration' => 6],
                    ['name' => 'Student Certificate', 'code' => 'SC', 'duration' => 5],
                    ['name' => 'Registration', 'code' => 'RG', 'duration' => 8],
                    ['name' => 'Grade Sheet', 'code' => 'GS', 'duration' => 5],
                ],
            ],
            [
                'code' => 'ACC',
                'name' => 'Accounts & Finance',
                'description' => 'Tuition fee payments, dues clearance, scholarship processing, and hall deposits.',
                'location' => 'Admin Building, Level 1',
                'avg_handling_time_min' => 8,
                'counter_count' => 5,
                'services' => [
                    ['name' => 'Tuition Fee Payment', 'code' => 'TFP', 'duration' => 8],
                    ['name' => 'Dues Clearance', 'code' => 'DC', 'duration' => 6],
                    ['name' => 'Scholarship Claim', 'code' => 'SC', 'duration' => 10],
                    ['name' => 'Hall Deposit', 'code' => 'HD', 'duration' => 6],
                ],
            ],
            [
                'code' => 'MED',
                'name' => 'Medical Center',
                'description' => 'Physician consultation, emergency medical triage, and prescription medicines.',
                'location' => 'Medical Complex, Ground Floor',
                'avg_handling_time_min' => 5,
                'counter_count' => 2,
                'services' => [
                    ['name' => 'Physician Consultation', 'code' => 'PC', 'duration' => 8],
                    ['name' => 'Emergency Triage', 'code' => 'ET', 'duration' => 4],
                    ['name' => 'Prescription Medicine Pickup', 'code' => 'PMP', 'duration' => 3],
                ],
            ],
            [
                'code' => 'CAF',
                'name' => 'Central Cafeteria',
                'description' => 'Daily meal tokens, prepaid food ordering, and student card top-up.',
                'location' => 'Cafeteria Building',
                'avg_handling_time_min' => 3,
                'counter_count' => 4,
                'services' => [
                    ['name' => 'Meal Token', 'code' => 'MT', 'duration' => 2],
                    ['name' => 'Food Order', 'code' => 'FO', 'duration' => 4],
                    ['name' => 'Card Top-up', 'code' => 'CT', 'duration' => 2],
                ],
            ],
            [
                'code' => 'ITS',
                'name' => 'IT Support & Helpdesk',
                'description' => 'Campus Wi-Fi credentials, portal password resets, LMS issues, and hardware diagnostics.',
                'location' => 'CSE Building, Level 3',
                'avg_handling_time_min' => 7,
                'counter_count' => 2,
                'services' => [
                    ['name' => 'Wi-Fi Credentials', 'code' => 'WIFI', 'duration' => 5],
                    ['name' => 'Portal Password Reset', 'code' => 'PWR', 'duration' => 4],
                    ['name' => 'LMS Issue', 'code' => 'LMS', 'duration' => 8],
                    ['name' => 'Hardware Support', 'code' => 'HWS', 'duration' => 12],
                ],
            ],
            [
                'code' => 'LIB',
                'name' => 'Central Library',
                'description' => 'Book checkout and returns, semester clearance, and study room reservations.',
                'location' => 'Library Complex, Level 1 & 2',
                'avg_handling_time_min' => 4,
                'counter_count' => 3,
                'services' => [
                    ['name' => 'Book Checkout', 'code' => 'BCO', 'duration' => 3],
                    ['name' => 'Book Return', 'code' => 'BRT', 'duration' => 3],
                    ['name' => 'Clearance Processing', 'code' => 'LCP', 'duration' => 5],
                    ['name' => 'Study Room Booking', 'code' => 'SRB', 'duration' => 4],
                ],
            ],
        ];

        $deptModels = [];
        $counterModels = [];

        foreach ($departmentsData as $data) {
            $department = Department::updateOrCreate([
                'code' => $data['code'],
            ], [
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'],
                'location' => $data['location'],
                'avg_handling_time_min' => $data['avg_handling_time_min'],
                'is_active' => true,
            ]);

            $deptModels[$data['code']] = $department;

            // Seed Services
            foreach ($data['services'] as $svc) {
                Service::updateOrCreate([
                    'department_id' => $department->id,
                    'code' => $svc['code'],
                ], [
                    'department_id' => $department->id,
                    'name' => $svc['name'],
                    'code' => $svc['code'],
                    'estimated_duration_min' => $svc['duration'],
                    'is_active' => true,
                ]);
            }

            // Seed Counters
            for ($c = 1; $c <= $data['counter_count']; $c++) {
                $counter = Counter::updateOrCreate([
                    'department_id' => $department->id,
                    'counter_number' => $c,
                ], [
                    'department_id' => $department->id,
                    'counter_number' => $c,
                    'name' => "Counter {$c}",
                    'status' => 'AVAILABLE',
                ]);
                $counterModels[$data['code']][] = $counter;
            }
        }

        // 3. Create Counter Operators
        $operatorsData = [
            ['name' => 'Kamal Hossain', 'email' => 'acc.operator@queueless.local', 'dept' => 'ACC', 'counter_index' => 0],
            ['name' => 'Farhana Yasmin', 'email' => 'reg.operator@queueless.local', 'dept' => 'REG', 'counter_index' => 0],
            ['name' => 'Dr. Shahed Ali', 'email' => 'med.operator@queueless.local', 'dept' => 'MED', 'counter_index' => 0],
            ['name' => 'Tareq Mahmud', 'email' => 'its.operator@queueless.local', 'dept' => 'ITS', 'counter_index' => 0],
            ['name' => 'Sharmin Akter', 'email' => 'caf.operator@queueless.local', 'dept' => 'CAF', 'counter_index' => 0],
            ['name' => 'Belal Ahmed', 'email' => 'lib.operator@queueless.local', 'dept' => 'LIB', 'counter_index' => 0],
        ];

        $operatorUsers = [];
        foreach ($operatorsData as $op) {
            $user = User::updateOrCreate([
                'email' => $op['email'],
            ], [
                'name' => $op['name'],
                'email' => $op['email'],
                'password' => $defaultPassword,
                'role' => 'COUNTER_OPERATOR',
                'phone' => '01811223344',
                'is_active' => true,
            ]);

            $operatorUsers[$op['dept']] = $user;

            $assignedCounter = $counterModels[$op['dept']][$op['counter_index']];
            OperatorAssignment::updateOrCreate([
                'user_id' => $user->id,
                'counter_id' => $assignedCounter->id,
                'status' => 'ACTIVE',
            ], [
                'user_id' => $user->id,
                'counter_id' => $assignedCounter->id,
                'started_at' => now()->subHours(4),
                'status' => 'ACTIVE',
            ]);
        }

        // 4. Create Students
        $studentsData = [
            ['name' => 'Tanvir Hasan', 'email' => 'student1@queueless.local', 'student_id' => '20210104001', 'dept' => 'Engineering', 'batch' => '14th'],
            ['name' => 'Sadia Afrin', 'email' => 'student2@queueless.local', 'student_id' => '20210104002', 'dept' => 'Engineering', 'batch' => '14th'],
            ['name' => 'Rafiqul Islam', 'email' => 'student3@queueless.local', 'student_id' => '20210104003', 'dept' => 'Science', 'batch' => '13th'],
            ['name' => 'Nusrat Jahan', 'email' => 'student4@queueless.local', 'student_id' => '20210104004', 'dept' => 'Business', 'batch' => '15th'],
            ['name' => 'Mahmudul Karim', 'email' => 'student5@queueless.local', 'student_id' => '20210104005', 'dept' => 'Arts', 'batch' => '12th'],
        ];

        $studentProfiles = [];
        foreach ($studentsData as $st) {
            $u = User::updateOrCreate([
                'email' => $st['email'],
            ], [
                'name' => $st['name'],
                'email' => $st['email'],
                'password' => $defaultPassword,
                'role' => 'STUDENT',
                'phone' => '01712345678',
                'is_active' => true,
            ]);

            $p = StudentProfile::updateOrCreate([
                'user_id' => $u->id,
            ], [
                'user_id' => $u->id,
                'student_id_number' => $st['student_id'],
                'department_name' => $st['dept'],
                'batch' => $st['batch'],
            ]);

            $studentProfiles[] = $p;
        }

        // 5. Seed Historical Completed Queue Tickets to power M/M/c rolling averages & Analytics
        $accDept = $deptModels['ACC'];
        $accService = $accDept->services()->first();
        $accCounter = $counterModels['ACC'][0];
        $accOperator = $operatorUsers['ACC'];

        for ($i = 1; $i <= 10; $i++) {
            $joinedTime = now()->subMinutes(120 - ($i * 10));
            $calledTime = (clone $joinedTime)->addMinutes(rand(3, 8));
            $completedTime = (clone $calledTime)->addMinutes(rand(5, 9));
            $durationSec = $completedTime->diffInSeconds($calledTime);

            $t = QueueTicket::updateOrCreate([
                'ticket_number' => sprintf('ACC-%03d', $i),
            ], [
                'ticket_number' => sprintf('ACC-%03d', $i),
                'sequence_number' => $i,
                'department_id' => $accDept->id,
                'service_id' => $accService->id,
                'student_profile_id' => $studentProfiles[$i % count($studentProfiles)]->id,
                'counter_id' => $accCounter->id,
                'operator_id' => $accOperator->id,
                'status' => 'COMPLETED',
                'estimated_wait_time_min' => 6,
                'joined_at' => $joinedTime,
                'called_at' => $calledTime,
                'served_at' => $calledTime,
                'completed_at' => $completedTime,
            ]);

            ServiceSession::updateOrCreate([
                'queue_ticket_id' => $t->id,
            ], [
                'queue_ticket_id' => $t->id,
                'counter_id' => $accCounter->id,
                'operator_id' => $accOperator->id,
                'started_at' => $calledTime,
                'ended_at' => $completedTime,
                'duration_seconds' => $durationSec,
                'sla_status' => ($durationSec > 600) ? 'BREACHED_SLA' : 'WITHIN_SLA',
            ]);

            QueueEvent::updateOrCreate([
                'queue_ticket_id' => $t->id,
                'event_type' => 'SERVICE_COMPLETED',
            ], [
                'queue_ticket_id' => $t->id,
                'actor_id' => $accOperator->id,
                'event_type' => 'SERVICE_COMPLETED',
                'payload' => ['duration_seconds' => $durationSec],
                'created_at' => $completedTime,
            ]);
        }

        // 6. Seed Sample Hourly Analytics & Metrics
        $hours = [9, 10, 11, 12, 13, 14, 15, 16];
        foreach ($departmentsData as $deptData) {
            $dept = $deptModels[$deptData['code']];
            foreach ($hours as $hour) {
                $arrivals = rand(12, 28);
                $served = (int) ($arrivals * rand(85, 95) / 100);
                $noShows = rand(0, 2);
                $cancelled = rand(0, 1);

                QueueMetric::updateOrCreate([
                    'department_id' => $dept->id,
                    'metric_date' => now()->startOfDay(),
                    'hour_of_day' => $hour,
                ], [
                    'department_id' => $dept->id,
                    'metric_date' => now()->startOfDay(),
                    'hour_of_day' => $hour,
                    'total_arrivals' => $arrivals,
                    'total_served' => $served,
                    'total_no_shows' => $noShows,
                    'total_cancelled' => $cancelled,
                    'avg_wait_seconds' => rand(240, 720),
                    'avg_service_seconds' => rand(180, 540),
                    'peak_queue_length' => rand(4, 14),
                    'counter_utilization_pct' => rand(65, 92),
                ]);
            }
        }
    }
}
