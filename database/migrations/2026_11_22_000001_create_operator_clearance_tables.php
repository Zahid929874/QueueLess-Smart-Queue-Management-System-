<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_status', 20)->default('Active')->after('is_active');
            $table->text('rejection_reason')->nullable()->after('account_status');
        });

        Schema::create('invite_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->foreignId('service_unit_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('max_uses')->default(1);
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamps();
        });

        Schema::create('operator_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('badge_no', 50)->unique();
            $table->foreignId('service_unit_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('counter_id')->constrained('counters')->cascadeOnDelete();
            $table->string('rank', 30);
            $table->string('shift', 20);
            $table->string('duty_contact', 25);
            $table->json('permissions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operator_profiles');
        Schema::dropIfExists('invite_codes');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_status', 'rejection_reason']);
        });
    }
};
