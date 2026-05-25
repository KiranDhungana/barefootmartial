<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Student;
use App\Services\InvoiceBillingService;
use App\Support\BranchScope;
use App\Support\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeeController extends Controller
{
    public function __construct(private InvoiceBillingService $billing)
    {
        $this->middleware('finance');
    }

    public function index(Request $request): View
    {
        $this->billing->refreshOverdueStatuses(
            auth()->user()?->isBranchScoped() ? auth()->user()->branch_id : null
        );

        $tab = $request->input('tab', 'due');
        $base = BranchScope::invoices()->with('student.branch');

        $counts = [
            'due' => (clone $base)->whereIn('status', [Invoice::STATUS_PENDING, Invoice::STATUS_PARTIAL, Invoice::STATUS_OVERDUE])
                ->whereColumn('amount_paid', '<', 'amount')->count(),
            'overdue' => (clone $base)->where('status', Invoice::STATUS_OVERDUE)->count(),
            'partial' => (clone $base)->where('status', Invoice::STATUS_PARTIAL)->count(),
            'paid' => (clone $base)->where('status', Invoice::STATUS_PAID)->count(),
        ];

        $q = clone $base;
        match ($tab) {
            'overdue' => $q->where('status', Invoice::STATUS_OVERDUE),
            'partial' => $q->where('status', Invoice::STATUS_PARTIAL),
            'paid' => $q->where('status', Invoice::STATUS_PAID),
            default => $q->whereIn('status', [Invoice::STATUS_PENDING, Invoice::STATUS_PARTIAL, Invoice::STATUS_OVERDUE])
                ->whereColumn('amount_paid', '<', 'amount'),
        };

        $invoices = $q->orderBy('due_date')->paginate(25)->withQueryString();

        $studentsPendingFee = BranchScope::students()
            ->where('status', Student::STATUS_PENDING_FEE)
            ->orderBy('name')
            ->limit(20)
            ->get();

        return view('erp.fees.index', compact('invoices', 'tab', 'counts', 'studentsPendingFee'));
    }

    public function reminders(Request $request): View
    {
        $this->billing->refreshOverdueStatuses(
            auth()->user()?->isBranchScoped() ? auth()->user()->branch_id : null
        );

        $invoices = BranchScope::invoices()
            ->with('student')
            ->whereIn('status', [Invoice::STATUS_PENDING, Invoice::STATUS_PARTIAL, Invoice::STATUS_OVERDUE])
            ->whereColumn('amount_paid', '<', 'amount')
            ->orderBy('due_date')
            ->limit(50)
            ->get()
            ->map(function (Invoice $invoice) {
                $student = $invoice->student;
                $msg = $this->reminderMessage($invoice);
                $phone = $student->phone ?: $student->parent_contact;

                return [
                    'invoice' => $invoice,
                    'student' => $student,
                    'message' => $msg,
                    'whatsapp_url' => $phone ? WhatsApp::waMeUrl($phone, $msg) : null,
                ];
            });

        return view('erp.fees.reminders', compact('invoices'));
    }

    private function reminderMessage(Invoice $invoice): string
    {
        $s = $invoice->student;
        $lines = [
            'Hello '.$s->name.',',
            'Fee reminder from Barefoot Martial Arts.',
            'Invoice: '.$invoice->invoice_number,
            'Total: '.number_format($invoice->amount, 2),
            'Paid: '.number_format($invoice->amount_paid, 2),
            'Balance due: '.number_format($invoice->balanceDue(), 2),
        ];
        if ($invoice->due_date) {
            $lines[] = 'Due date: '.$invoice->due_date->format('M j, Y');
        }
        if ($invoice->status === Invoice::STATUS_OVERDUE) {
            $lines[] = 'This invoice is overdue. Please contact your branch.';
        }

        return implode("\n", $lines);
    }
}
