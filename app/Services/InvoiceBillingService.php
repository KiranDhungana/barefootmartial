<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceBillingService
{
    public function __construct(
        private InventoryService $inventory
    ) {
    }

    /**
     * @param  array<int, array{fee_type: string, unit_price: float|int|string, quantity?: int, description?: string}>  $feeLines
     * @param  array<int, array{inventory_item_id: int, quantity: int, unit_price?: float, size?: string}>  $inventoryLines
     */
    public function createInvoice(
        Student $student,
        array $feeLines,
        array $inventoryLines,
        array $options = [],
        ?User $by = null
    ): Invoice {
        $by = $by ?? auth()->user();
        $branchId = $student->branch_id;
        $scholarshipWaiver = ! empty($options['scholarship_waiver']);
        $discountPercent = (float) ($options['discount_percent'] ?? $student->discount_percent ?? 0);
        $lateFee = (float) ($options['late_fee'] ?? 0);
        $dueDate = $options['due_date'] ?? null;
        $notes = $options['notes'] ?? null;
        $initialPayment = (float) ($options['initial_payment'] ?? 0);

        return DB::transaction(function () use (
            $student,
            $feeLines,
            $inventoryLines,
            $branchId,
            $scholarshipWaiver,
            $discountPercent,
            $lateFee,
            $dueDate,
            $notes,
            $initialPayment,
            $by
        ) {
            $invoice = new Invoice([
                'student_id' => $student->id,
                'branch_id' => $branchId,
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'due_date' => $dueDate,
                'notes' => $notes,
                'late_fee_amount' => $lateFee,
                'is_scholarship_waiver' => $scholarshipWaiver,
            ]);

            $subtotal = 0;
            $pendingLines = [];
            $stockDeductions = [];

            foreach ($feeLines as $line) {
                if (empty($line['selected']) && empty($line['unit_price'])) {
                    continue;
                }
                $qty = max(1, (int) ($line['quantity'] ?? 1));
                $unit = (float) ($line['unit_price'] ?? 0);
                $feeType = $line['fee_type'] ?? 'other';
                $label = config("academy.fee_types.{$feeType}.label", ucfirst($feeType));
                $lineTotal = round($qty * $unit, 2);
                $subtotal += $lineTotal;

                $pendingLines[] = new InvoiceLineItem([
                    'fee_type' => $feeType,
                    'description' => $line['description'] ?? $label,
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'line_total' => $lineTotal,
                ]);
            }

            foreach ($inventoryLines as $line) {
                if (empty($line['inventory_item_id']) || empty($line['quantity'])) {
                    continue;
                }
                $item = $this->inventory->findItem((int) $line['inventory_item_id']);
                $qty = (int) $line['quantity'];
                $size = $line['size'] ?? null;
                $unit = (float) ($line['unit_price'] ?? $item->unit_price);
                $lineTotal = round($qty * $unit, 2);
                $subtotal += $lineTotal;

                $desc = $item->name.($size ? " ({$size})" : '');
                $pendingLines[] = new InvoiceLineItem([
                    'fee_type' => 'inventory',
                    'description' => $desc,
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'line_total' => $lineTotal,
                    'inventory_item_id' => $item->id,
                    'size' => $size,
                ]);
                $stockDeductions[] = [$branchId, $item->id, $qty];
            }

            if ($subtotal <= 0 && ! $scholarshipWaiver) {
                throw ValidationException::withMessages([
                    'lines' => 'Select at least one fee or inventory item.',
                ]);
            }

            $discountAmount = 0;
            if ($scholarshipWaiver) {
                $discountAmount = $subtotal;
            } elseif ($discountPercent > 0) {
                $discountAmount = round($subtotal * ($discountPercent / 100), 2);
            }

            $total = max(0, round($subtotal - $discountAmount + $lateFee, 2));

            $invoice->subtotal = $subtotal;
            $invoice->discount_amount = $discountAmount;
            $invoice->amount = $total;
            $invoice->amount_paid = 0;
            $invoice->save();

            foreach ($pendingLines as $lineItem) {
                $invoice->lineItems()->save($lineItem);
            }

            foreach ($stockDeductions as [$bId, $itemId, $qty]) {
                $this->inventory->deduct($bId, $itemId, $qty);
            }

            $this->syncStatus($invoice);

            if ($initialPayment > 0) {
                $this->recordPayment($invoice, min($initialPayment, $invoice->balanceDue()), [
                    'payment_method' => $options['payment_method'] ?? 'cash',
                    'notes' => 'Initial payment on invoice',
                ], $by);
            }

            if ($scholarshipWaiver && $invoice->balanceDue() <= 0) {
                $student->update(['status' => Student::STATUS_SCHOLARSHIP]);
            }

            return $invoice->fresh(['lineItems', 'payments', 'student']);
        });
    }

    public function recordPayment(Invoice $invoice, float $amount, array $options = [], ?User $by = null): Payment
    {
        $by = $by ?? auth()->user();
        $amount = round($amount, 2);

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Payment amount must be greater than zero.']);
        }

        if ($amount > $invoice->balanceDue() + 0.01) {
            throw ValidationException::withMessages([
                'amount' => 'Payment exceeds balance due ('.number_format($invoice->balanceDue(), 2).').',
            ]);
        }

        return DB::transaction(function () use ($invoice, $amount, $options, $by) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'receipt_number' => Payment::generateReceiptNumber(),
                'amount' => $amount,
                'payment_method' => $options['payment_method'] ?? 'cash',
                'paid_at' => $options['paid_at'] ?? now(),
                'notes' => $options['notes'] ?? null,
                'recorded_by' => $by?->id,
            ]);

            $invoice->amount_paid = round((float) $invoice->amount_paid + $amount, 2);
            $invoice->save();
            $this->syncStatus($invoice);

            return $payment;
        });
    }

    public function applyLateFee(Invoice $invoice, ?float $amount = null): Invoice
    {
        $amount = $amount ?? (float) config('academy.default_late_fee', 0);
        if ($amount <= 0) {
            return $invoice;
        }

        $invoice->late_fee_amount = round((float) $invoice->late_fee_amount + $amount, 2);
        $invoice->amount = round((float) $invoice->subtotal - (float) $invoice->discount_amount + (float) $invoice->late_fee_amount, 2);
        $invoice->save();
        $this->syncStatus($invoice);

        return $invoice->fresh();
    }

    public function syncStatus(Invoice $invoice): void
    {
        $balance = $invoice->balanceDue();
        $status = Invoice::STATUS_PENDING;

        if ($balance <= 0.009) {
            $status = Invoice::STATUS_PAID;
            $invoice->paid_at = $invoice->paid_at ?? now();
        } elseif ((float) $invoice->amount_paid > 0) {
            $status = Invoice::STATUS_PARTIAL;
            $invoice->paid_at = null;
        } elseif ($invoice->due_date && $invoice->due_date->isPast()) {
            $status = Invoice::STATUS_OVERDUE;
            $invoice->paid_at = null;
        } else {
            $invoice->paid_at = null;
        }

        $invoice->status = $status;
        $invoice->save();
    }

    public function refreshOverdueStatuses(?int $branchId = null): int
    {
        $q = Invoice::query()
            ->whereIn('status', [Invoice::STATUS_PENDING, Invoice::STATUS_PARTIAL])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->whereColumn('amount_paid', '<', 'amount');

        if ($branchId) {
            $q->where('branch_id', $branchId);
        }

        return $q->update(['status' => Invoice::STATUS_OVERDUE]);
    }
}
