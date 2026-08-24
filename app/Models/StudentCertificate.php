<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCertificate extends Model
{
    protected $fillable = [
        'student_id',
        'title',
        'file_url',
        'public_id',
        'resource_type',
        'original_filename',
        'issued_on',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'issued_on' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isImage(): bool
    {
        return in_array($this->resource_type, ['image', ''], true)
            || preg_match('/\.(jpe?g|png|gif|webp)$/i', $this->file_url);
    }
}
