<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_profiles', function (Blueprint $table) {
            $table->string('office_title', 100)->nullable()->after('job_title');
            $table->string('profile_photo_path')->nullable()->after('office_title');
        });
    }

    public function down(): void
    {
        Schema::table('admin_profiles', function (Blueprint $table) {
            $table->dropColumn(['office_title', 'profile_photo_path']);
        });
    }
};
