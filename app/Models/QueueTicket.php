<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QueueTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'sequence_number',
        'department_id',
        'service_id',
        'student_profile_id',
        'counter_id',
        'operator_id',
        'status', // WAITING, CALLED, SERVING, COMPLETED, CANCELLED, NO_SHOW, TRANSFERRED
        'estimated_wait_time_min',
        'joined_at',
        'called_at',
        'served_at',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sequence_number' => 'integer',
            'estimated_wait_time_min' => 'integer',
            'joined_at' => 'datetime',
            'called_at' => 'datetime',
            'served_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(QueueEvent::class);
    }

    public function session(): HasOne
    {
        return $this->hasOne(ServiceSession::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
