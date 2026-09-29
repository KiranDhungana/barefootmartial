<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Student;
use App\Services\InvoiceBillingService;
use App\Services\NotificationService;
use App\Services\StudentFeeSummaryService;
use App\Support\BranchScope;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeeController extends Controller
{
    public function __construct(
        private InvoiceBillingService $billing,
        private NotificationService $notifications,
        private StudentFeeSummaryService $feeSummary
    ) {
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

        $reminders = $this->notifications->studentsWithOpenBalances();

        return view('erp.fees.reminders', compact('reminders'));
    }
}
