<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\file;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Services\AttendanceAnalyticsService;
use Illuminate\View\View;

class ParentPortalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'parent']);
    }

    public function dashboard(): View
    {
        $children = auth()->user()->children()->with('branch')->get();
        $studentId = request()->integer('student_id') ?: $children->first()?->id;
        $student = $children->firstWhere('id', $studentId);

        $attendanceSummary = null;
        $invoices = collect();
        $payments = collect();
        $feeSummary = [
            'total_billed' => 0,
            'total_paid' => 0,
            'total_remaining' => 0,
        ];
        $notices = file::query()->orderByDesc('id')->limit(5)->get();

        if ($student) {
            $attendanceSummary = app(AttendanceAnalyticsService::class)
                ->monthlySummary((int) now()->format('Y'), (int) now()->format('m'), $student->branch_id)
                ->firstWhere(fn ($r) => $r->student->id === $student->id);

            $invoices = Invoice::query()
                ->where('student_id', $student->id)
                ->with(['payments' => fn ($q) => $q->orderByDesc('paid_at')])
                ->latest()
                ->get();

            $payments = Payment::query()
                ->whereHas('invoice', fn ($q) => $q->where('student_id', $student->id))
                ->with('invoice')
                ->orderByDesc('paid_at')
                ->get();

            $feeSummary = [
                'total_billed' => round($invoices->sum(fn (Invoice $inv) => $inv->totalAmount()), 2),
                'total_paid' => round($invoices->sum(fn (Invoice $inv) => (float) $inv->amount_paid), 2),
                'total_remaining' => round($invoices->sum(fn (Invoice $inv) => $inv->balanceDue()), 2),
            ];
        }

        return view('parent.dashboard', compact(
            'children',
            'student',
            'attendanceSummary',
            'invoices',
            'payments',
            'feeSummary',
            'notices'
        ));
    }
}
