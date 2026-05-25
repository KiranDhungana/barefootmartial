<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'receipt_number',
        'amount',
        'payment_method',
        'paid_at',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public static function generateReceiptNumber(): string
    {
        $year = (int) now()->format('Y');
        $prefix = 'RCP-'.$year.'-';
        $last = static::query()
            ->where('receipt_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('receipt_number');
        $next = 1;
        if ($last && preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', $last, $m)) {
            $next = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
