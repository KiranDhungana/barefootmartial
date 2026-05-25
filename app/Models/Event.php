<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'branch_id',
        'title',
        'description',
        'event_date',
        'registration_deadline',
        'fee_amount',
        'is_published',
    ];

    protected $casts = [
        'event_date' => 'date',
        'registration_deadline' => 'date',
        'fee_amount' => 'decimal:2',
        'is_published' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }
}
