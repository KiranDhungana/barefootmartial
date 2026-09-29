<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Student;
use App\Support\AcademyOrg;
use Illuminate\Support\Collection;

class StudentFeeSummaryService
{
    /**
     * Open (unpaid / partial / overdue) invoices for a student.
     *
     * @return Collection<int, Invoice>
     */
    public function openInvoices(Student $student): Collection
    {
        return Invoice::query()
            ->where('student_id', $student->id)
            ->whereIn('status', [
                Invoice::STATUS_PENDING,
                Invoice::STATUS_PARTIAL,
                Invoice::STATUS_OVERDUE,
            ])
            ->whereColumn('amount_paid', '<', 'amount')
            ->with(['lineItems', 'payments'])
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();
    }

    /**
     * Consolidated fee figures for receipts and reminders.
     *
     * @return array{
     *     open_invoices: Collection<int, Invoice>,
     *     all_invoices: Collection<int, Invoice>,
     *     total_billed: float,
     *     total_paid: float,
     *     total_outstanding: float,
     *     open_count: int,
     *     itemized: list<array{invoice_number: string, description: string, balance: float, due_date: ?string}>
     * }
     */
    public function summary(Student $student): array
    {
        $all = Invoice::query()
            ->where('student_id', $student->id)
            ->with('lineItems')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        $open = $all->filter(fn (Invoice $inv) => $inv->balanceDue() > 0.009
            && in_array($inv->status, [
                Invoice::STATUS_PENDING,
                Invoice::STATUS_PARTIAL,
                Invoice::STATUS_OVERDUE,
            ], true)
        )->values();

        $itemized = [];
        foreach ($open as $invoice) {
            $descParts = $invoice->lineItems->pluck('description')->filter()->take(2)->all();
            $description = $descParts
                ? implode(', ', $descParts)
                : ($invoice->billing_period
                    ? str_replace('monthly:', 'Monthly ', $invoice->billing_period)
                    : 'Fees');

            $itemized[] = [
                'invoice_number' => $invoice->invoice_number,
                'description' => $description,
                'balance' => $invoice->balanceDue(),
                'due_date' => optional($invoice->due_date)->format('M j, Y'),
            ];
        }

        return [
            'open_invoices' => $open,
            'all_invoices' => $all,
            'total_billed' => round($all->sum(fn (Invoice $inv) => $inv->totalAmount()), 2),
            'total_paid' => round($all->sum(fn (Invoice $inv) => (float) $inv->amount_paid), 2),
            'total_outstanding' => round($open->sum(fn (Invoice $inv) => $inv->balanceDue()), 2),
            'open_count' => $open->count(),
            'itemized' => $itemized,
        ];
    }

    /**
     * WhatsApp / SMS / email fee reminder using the student's full outstanding balance.
     */
    public function reminderMessage(Student $student): string
    {
        $summary = $this->summary($student);
        $academy = AcademyOrg::getString('legal_name', 'Barefoot Martial Arts');

        $lines = [
            'Hello '.$student->name.',',
            'Fee reminder from '.$academy.'.',
        ];

        if ($summary['open_count'] === 0) {
            $lines[] = 'Your fees are currently paid up to date. Thank you!';

            return implode("\n", $lines);
        }

        $lines[] = 'Total outstanding: Rs. '.number_format($summary['total_outstanding'], 2);
        $lines[] = 'Open invoices: '.$summary['open_count'];
        $lines[] = 'Breakdown:';

        foreach ($summary['itemized'] as $item) {
            $due = $item['due_date'] ? ' (due '.$item['due_date'].')' : '';
            $lines[] = '- '.$item['invoice_number'].': Rs. '.number_format($item['balance'], 2)
                .' — '.$item['description'].$due;
        }

        $lines[] = 'Please pay the total outstanding amount at your branch or via Fonepay.';

        return implode("\n", $lines);
    }
}
