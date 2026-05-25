<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    public static function log(string $action, ?Model $model = null, ?array $old = null, ?array $new = null): void
    {
        $user = auth()->user();

        AuditLog::query()->create([
            'user_id' => $user?->id,
            'branch_id' => $user?->branch_id ?? ($model instanceof \App\Models\Student ? $model->branch_id : null),
            'action' => $action,
            'auditable_type' => $model ? $model::class : null,
            'auditable_id' => $model?->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
