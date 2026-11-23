<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Student Profiles
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('student_id_number', 50)->unique();
            $table->string('department_name', 100)->default('CSE');
            $table->string('batch', 30)->default('14th');
            $table->timestamps();
        });

        // 2. Departments
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // REG, ACC, MED, CAF, ITS, LIB
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('location', 150)->nullable();
            $table->integer('avg_handling_time_min')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Services
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('code', 30)->nullable();
            $table->text('description')->nullable();
            $table->integer('estimated_duration_min')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['department_id', 'is_active']);
        });

        // 4. Counters
        Schema::create('counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->integer('counter_number');
            $table->string('name', 100);
            $table->string('status', 30)->default('AVAILABLE'); // AVAILABLE, BUSY, ON_BREAK, OFFLINE
            $table->timestamps();

            $table->unique(['department_id', 'counter_number']);
        });

        // 5. Operator Assignments
        Schema::create('operator_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counter_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->string('status', 30)->default('ACTIVE'); // ACTIVE, COMPLETED
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        // 6. Queue Tickets
        Schema::create('queue_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 30)->index(); // e.g. ACC-001
            $table->integer('sequence_number')->default(1);
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counter_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('WAITING')->index(); // WAITING, CALLED, SERVING, COMPLETED, CANCELLED, NO_SHOW, TRANSFERRED
            $table->integer('estimated_wait_time_min')->default(0);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['department_id', 'status', 'created_at'], 'idx_tickets_dept_status_created');
            $table->index(['student_profile_id', 'status'], 'idx_tickets_student_status');
        });

        // 7. Queue Events (Audit history for ticket)
        Schema::create('queue_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 50); // JOINED, CALLED, RECALLED, SERVICE_STARTED, SERVICE_COMPLETED, NO_SHOW, TRANSFERRED, CANCELLED
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['queue_ticket_id', 'created_at']);
        });

        // 8. Service Sessions (Metrics & timers)
        Schema::create('service_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('operator_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->string('sla_status', 30)->default('WITHIN_SLA'); // WITHIN_SLA, BREACHED_SLA
            $table->timestamps();
        });

        // 9. Queue Metrics (Daily & Hourly aggregated reporting)
        Schema::create('queue_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->date('metric_date');
            $table->integer('hour_of_day'); // 0-23
            $table->integer('total_arrivals')->default(0);
            $table->integer('total_served')->default(0);
            $table->integer('total_no_shows')->default(0);
            $table->integer('total_cancelled')->default(0);
            $table->double('avg_wait_seconds')->default(0);
            $table->double('avg_service_seconds')->default(0);
            $table->integer('peak_queue_length')->default(0);
            $table->double('counter_utilization_pct')->default(0);
            $table->timestamps();

            $table->unique(['department_id', 'metric_date', 'hour_of_day'], 'idx_metrics_unique_dept_hour');
        });

        // 10. Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('queue_ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel', 30)->default('IN_APP'); // IN_APP, WEB_PUSH, SMS, WHATSAPP
            $table->string('title', 150);
            $table->text('message');
            $table->string('status', 30)->default('SENT'); // QUEUED, SENT, DELIVERED, FAILED
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
        });

        // 11. Audit Logs (System actions)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 100);
            $table->string('target_type', 100)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('details')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('queue_metrics');
        Schema::dropIfExists('service_sessions');
        Schema::dropIfExists('queue_events');
        Schema::dropIfExists('queue_tickets');
        Schema::dropIfExists('operator_assignments');
        Schema::dropIfExists('counters');
        Schema::dropIfExists('services');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('student_profiles');
    }
};
