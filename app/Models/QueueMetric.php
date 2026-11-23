<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'metric_date',
        'hour_of_day',
        'total_arrivals',
        'total_served',
        'total_no_shows',
        'total_cancelled',
        'avg_wait_seconds',
        'avg_service_seconds',
        'peak_queue_length',
        'counter_utilization_pct',
    ];

    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
            'hour_of_day' => 'integer',
            'total_arrivals' => 'integer',
            'total_served' => 'integer',
            'total_no_shows' => 'integer',
            'total_cancelled' => 'integer',
            'avg_wait_seconds' => 'float',
            'avg_service_seconds' => 'float',
            'peak_queue_length' => 'integer',
            'counter_utilization_pct' => 'float',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
