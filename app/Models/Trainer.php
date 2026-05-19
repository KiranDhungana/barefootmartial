<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trainer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'role_title',
        'salary_mode',
        'monthly_amount',
        'per_day_amount',
        'notes',
    ];

    protected $casts = [
        'monthly_amount' => 'decimal:2',
        'per_day_amount' => 'decimal:2',
    ];

    public function salaryRecords(): HasMany
    {
        return $this->hasMany(SalaryRecord::class);
    }
}
