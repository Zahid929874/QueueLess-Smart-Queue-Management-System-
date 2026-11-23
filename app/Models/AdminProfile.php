<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminProfile extends Model
{
    protected $fillable = [
        'user_id',
        'employee_id',
        'job_title',
        'duty_contact',
        'office_title',
        'profile_photo_path',
        'tier',
        'primary_service_unit_id',
        'managed_service_unit_ids',
        'totp_secret',
        'recovery_codes',
        'totp_enabled',
    ];

    protected function casts(): array
    {
        return [
            'managed_service_unit_ids' => 'array',
            'recovery_codes' => 'array',
            'totp_enabled' => 'boolean',
            'totp_secret' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
