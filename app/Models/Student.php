<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Student extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_PENDING_FEE = 'pending_fee';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_SCHOLARSHIP = 'scholarship';

    public const STATUS_SUSPENDED = 'suspended';

    public const REG_PENDING = 'pending';

    public const REG_OFFICIAL = 'official';

    protected $fillable = [
        'branch_id',
        'student_code',
        'name',
        'phone',
        'address',
        'dob',
        'gender',
        'blood_group',
        'parent_name',
        'parent_contact',
        'emergency_contact',
        'coach_name',
        'belt_rank',
        'batch_timing',
        'status',
        'registration_status',
        'fee_status',
        'uniform_status',
        'discount_percent',
        'scholarship_type',
        'scholarship_notes',
        'registered_at',
        'registered_by',
        'imported',
        'join_date',
        'photo_path',
        'qr_token',
        'notes',
    ];

    protected $casts = [
        'join_date' => 'date',
        'dob' => 'date',
        'registered_at' => 'datetime',
        'imported' => 'boolean',
        'discount_percent' => 'decimal:2',
    ];

    public function hasFullScholarship(): bool
    {
        return $this->status === self::STATUS_SCHOLARSHIP
            || in_array($this->scholarship_type, ['full_waiver', 'sponsored'], true);
    }

    protected static function booted(): void
    {
        static::creating(function (Student $student): void {
            if (empty($student->qr_token)) {
                $student->qr_token = (string) Str::uuid();
            }
            if (empty($student->student_code)) {
                $student->student_code = static::generateMembershipCode($student->isOfficial());
            }
            if (empty($student->registration_status)) {
                $student->registration_status = self::REG_PENDING;
            }
            if (empty($student->status)) {
                $student->status = self::STATUS_ACTIVE;
            }
        });
    }

    public static function generateMembershipCode(bool $official = true): string
    {
        $year = (int) now()->format('Y');
        $prefix = ($official ? config('academy.membership_prefix', 'BFN') : 'PRE').'-'.$year.'-';
        $last = static::query()
            ->where('student_code', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('student_code');
        $next = 1;
        if ($last && preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', $last, $m)) {
            $next = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function isOfficial(): bool
    {
        return $this->registration_status === self::REG_OFFICIAL;
    }

    public function isPendingRegistration(): bool
    {
        return $this->registration_status === self::REG_PENDING;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_FEE => 'Pending fee',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_SCHOLARSHIP => 'Scholarship',
            self::STATUS_SUSPENDED => 'Suspended',
            default => 'Active',
        };
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function registeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function beltPromotions(): HasMany
    {
        return $this->hasMany(BeltPromotion::class)->orderByDesc('promoted_at');
    }

    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_user_id')
            ->withTimestamps();
    }

    public function profileUrl(): string
    {
        return url('/erp/students/'.$this->id);
    }

    public function verifyUrl(): string
    {
        return route('verify.student', ['token' => $this->qr_token]);
    }

    public function qrScanUrl(): string
    {
        return route('erp.attendance.scan', ['token' => $this->qr_token]);
    }
}
