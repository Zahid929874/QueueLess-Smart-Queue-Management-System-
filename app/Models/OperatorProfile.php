<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperatorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'badge_no',
        'service_unit_id',
        'counter_id',
        'rank',
        'shift',
        'duty_contact',
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceUnit(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'service_unit_id');
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class);
    }
}
