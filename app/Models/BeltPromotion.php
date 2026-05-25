<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeltPromotion extends Model
{
    protected $fillable = [
        'student_id',
        'from_belt',
        'to_belt',
        'promoted_at',
        'exam_passed',
        'certificate_number',
        'notes',
        'promoted_by',
    ];

    protected $casts = [
        'promoted_at' => 'date',
        'exam_passed' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function promotedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'promoted_by');
    }

    public static function generateCertificateNumber(): string
    {
        $year = (int) now()->format('Y');
        $prefix = 'CERT-'.$year.'-';
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
