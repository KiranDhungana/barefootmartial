<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Student extends Model
{
    protected $fillable = [
        'branch_id',
        'student_code',
        'name',
        'phone',
        'address',
        'join_date',
        'photo_path',
        'qr_token',
        'notes',
    ];

    protected $casts = [
        'join_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Student $student): void {
            if (empty($student->qr_token)) {
                $student->qr_token = (string) Str::uuid();
            }
            if (empty($student->student_code)) {
                $student->student_code = static::generateStudentCode();
            }
        });
    }

    public static function generateStudentCode(): string
    {
        $year = (int) now()->format('Y');
        $prefix = 'STU-'.$year.'-';
        $last = static::query()
            ->where('student_code', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('student_code');
        $next = 1;
        if ($last && preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', $last, $m)) {
            $next = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function profileUrl(): string
    {
        return url('/erp/students/'.$this->id);
    }

    public function qrScanUrl(): string
    {
        return route('erp.attendance.scan', ['token' => $this->qr_token]);
    }
}
