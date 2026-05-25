<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineRegistration extends Model
{
    protected $fillable = [
        'branch_id',
        'student_name',
        'parent_name',
        'phone',
        'email',
        'message',
        'status',
        'student_id',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
