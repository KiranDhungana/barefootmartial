<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_BRANCH_ADMIN = 'branch_admin';

    public const ROLE_ACCOUNTANT = 'accountant';

    public const ROLE_COACH = 'coach';

    public const ROLE_STAFF = 'staff';

    public const ROLE_PLAYER = 'player';

    public const ROLE_PARENT = 'parent';

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'role',
        'branch_id',
        'image',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function isSuperAdmin(): bool
    {
        return (int) $this->is_admin === 1
            || $this->role === self::ROLE_SUPER_ADMIN
            || $this->role === 'admin';
    }

    /** @deprecated Use isSuperAdmin() */
    public function isAdmin(): bool
    {
        return $this->isSuperAdmin();
    }

    public function isStaff(): bool
    {
        return in_array($this->role, [
            self::ROLE_STAFF,
            self::ROLE_COACH,
            self::ROLE_BRANCH_ADMIN,
            self::ROLE_ACCOUNTANT,
        ], true);
    }

    public function isParent(): bool
    {
        return $this->role === self::ROLE_PARENT;
    }

    public function canAccessParentPortal(): bool
    {
        return $this->isParent();
    }

    public function canAccessErp(): bool
    {
        return $this->isSuperAdmin() || $this->isStaff();
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'parent_user_id', 'student_id')
            ->withTimestamps();
    }

    public function canManageFinance(): bool
    {
        return $this->isSuperAdmin()
            || in_array($this->role, [self::ROLE_BRANCH_ADMIN, self::ROLE_ACCOUNTANT], true);
    }

    public function canManageStudents(): bool
    {
        return $this->canAccessErp();
    }

    public function canImportStudents(): bool
    {
        return $this->isSuperAdmin() || $this->role === self::ROLE_BRANCH_ADMIN;
    }

    public function canMarkOfficialRegistration(): bool
    {
        return $this->isSuperAdmin() || $this->role === self::ROLE_BRANCH_ADMIN;
    }

    public function canManageErpUsers(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canViewAuditLogs(): bool
    {
        return $this->isSuperAdmin() || $this->role === self::ROLE_BRANCH_ADMIN;
    }

    public function isBranchScoped(): bool
    {
        return $this->branch_id !== null && ! $this->isSuperAdmin();
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_SUPER_ADMIN, 'admin' => 'Super Admin',
            self::ROLE_BRANCH_ADMIN => 'Branch Admin',
            self::ROLE_ACCOUNTANT => 'Accountant',
            self::ROLE_COACH => 'Coach',
            self::ROLE_STAFF => 'Staff',
            self::ROLE_PARENT => 'Parent',
            default => ucfirst((string) $this->role),
        };
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_secret !== null && $this->two_factor_confirmed_at !== null;
    }
}
