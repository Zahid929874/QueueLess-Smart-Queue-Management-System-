<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_profiles', function (Blueprint $table) {
            $table->text('totp_secret')->nullable()->after('primary_service_unit_id');
            $table->json('recovery_codes')->nullable()->after('totp_secret');
            $table->boolean('totp_enabled')->default(false)->after('recovery_codes');
        });
    }

    public function down(): void
    {
        Schema::table('admin_profiles', function (Blueprint $table) {
            $table->dropColumn(['totp_secret', 'recovery_codes', 'totp_enabled']);
        });
    }
};
