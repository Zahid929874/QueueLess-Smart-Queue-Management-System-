<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Counter extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'counter_number',
        'name',
        'status', // AVAILABLE, BUSY, ON_BREAK, OFFLINE
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(OperatorAssignment::class);
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(OperatorAssignment::class)->where('status', 'ACTIVE')->latestOfMany();
    }

    public function queueTickets(): HasMany
    {
        return $this->hasMany(QueueTicket::class);
    }

    public function currentServingTicket(): HasOne
    {
        return $this->hasOne(QueueTicket::class)->whereIn('status', ['CALLED', 'SERVING'])->latestOfMany();
    }
}
