<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSchedule extends Model
{
    protected $fillable = [
        'branch_id',
        'name',
        'coach_name',
        'day_of_week',
        'start_time',
        'end_time',
        'belt_level',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function dayLabel(): string
    {
        return ucfirst($this->day_of_week);
    }
}
