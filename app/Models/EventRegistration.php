<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    protected $fillable = [
        'event_id',
        'student_id',
        'category',
        'fee_amount',
        'status',
        'certificate_number',
    ];

    protected $casts = [
        'fee_amount' => 'decimal:2',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
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
