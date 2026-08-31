<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    protected $fillable = [
        'event_id',
        'student_id',
        'registrant_name',
        'phone',
        'email',
        'category',
        'fee_amount',
        'status',
        'notes',
        'certificate_number',
        'certificate_url',
        'certificate_public_id',
        'certificate_resource_type',
        'certificate_title',
        'certificate_issued_on',
        'certificate_uploaded_by',
    ];

    protected $casts = [
        'fee_amount' => 'decimal:2',
        'certificate_issued_on' => 'date',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function certificateUploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'certificate_uploaded_by');
    }

    public function displayName(): string
    {
        return $this->student?->name
            ?: ($this->registrant_name ?: 'Participant');
    }

    public function displayPhone(): ?string
    {
        return $this->phone ?: $this->student?->phone;
    }

    public function hasCertificate(): bool
    {
        return filled($this->certificate_url) || filled($this->certificate_number);
    }

    public function certificateIsImage(): bool
    {
        if (! $this->certificate_url) {
            return false;
        }

        return ($this->certificate_resource_type === 'image')
            || (bool) preg_match('/\.(jpe?g|png|gif|webp)$/i', $this->certificate_url);
    }

    public static function generateCertificateNumber(): string
    {
        $year = (int) now()->format('Y');
        $prefix = 'EVT-'.$year.'-';
        $last = static::query()
            ->where('certificate_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('certificate_number');
        $next = 1;
        if ($last && preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', $last, $m)) {
            $next = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
