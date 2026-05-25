<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class BranchScope
{
    public static function forUser(?User $user, Builder $query, string $column = 'branch_id'): Builder
    {
        if (! $user || $user->isSuperAdmin()) {
            return $query;
        }

        if ($user->branch_id) {
            return $query->where($column, $user->branch_id);
        }

        return $query;
    }

    public static function students(?User $user = null): Builder
    {
        $user = $user ?? auth()->user();

        return static::forUser($user, \App\Models\Student::query());
    }

    public static function invoices(?User $user = null): Builder
    {
        $user = $user ?? auth()->user();

        return \App\Models\Invoice::query()->whereHas('student', function (Builder $q) use ($user) {
            static::forUser($user, $q);
        });
    }

    public static function assertStudentAccess(\App\Models\Student $student, ?User $user = null): void
    {
        $user = $user ?? auth()->user();
        if (! $user) {
            abort(403);
        }
        if ($user->isSuperAdmin()) {
            return;
        }
        if ($user->branch_id && (int) $student->branch_id !== (int) $user->branch_id) {
            abort(403, 'You can only access students in your branch.');
        }
    }
}
