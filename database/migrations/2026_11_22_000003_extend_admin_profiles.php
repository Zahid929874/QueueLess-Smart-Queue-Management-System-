<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_profiles', function (Blueprint $table) {
            $table->string('employee_id', 50)->nullable()->unique();
            $table->string('job_title', 100)->nullable();
            $table->string('duty_contact', 25)->nullable();
            $table->foreignId('primary_service_unit_id')->nullable()->constrained('departments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('admin_profiles', function (Blueprint $table) {
            $table->dropForeign(['primary_service_unit_id']);
            $table->dropColumn([
                'employee_id',
                'job_title',
                'duty_contact',
                'primary_service_unit_id',
            ]);
        });
    }
};
