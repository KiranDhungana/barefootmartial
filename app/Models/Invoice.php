<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_PAID = 'paid';

    public const STATUS_OVERDUE = 'overdue';

    protected $fillable = [
        'invoice_number',
        'student_id',
        'branch_id',
        'amount',
        'subtotal',
        'discount_amount',
        'late_fee_amount',
        'amount_paid',
        'is_scholarship_waiver',
        'due_date',
        'status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'late_fee_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'is_scholarship_waiver' => 'boolean',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(InvoiceLineItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function totalAmount(): float
    {
        return (float) $this->amount;
    }

    public function balanceDue(): float
    {
        return max(0, round($this->totalAmount() - (float) $this->amount_paid, 2));
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PARTIAL => 'Partial',
            self::STATUS_OVERDUE => 'Overdue',
            self::STATUS_PAID => 'Paid',
            default => 'Pending',
        };
    }

    public static function generateInvoiceNumber(): string
    {
        $year = (int) now()->format('Y');
        $prefix = 'INV-'.$year.'-';
        $last = static::query()
            ->where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('invoice_number');
        $next = 1;
        if ($last && preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', $last, $m)) {
            $next = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
