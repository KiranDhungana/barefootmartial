<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Invoice;
use App\Models\Student;
use App\Services\AttendanceAnalyticsService;
use App\Services\InvoiceBillingService;
use App\Support\BranchScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): RedirectResponse|View
    {
        $user = auth()->user();
        if ($user?->isSuperAdmin()) {
            return redirect()->route('erp.hq.dashboard');
        }

        $user?->load('branch');
        $showFinance = $user && $user->canManageFinance();
        $attendance = app(AttendanceAnalyticsService::class);

        $studentBase = BranchScope::students();
        $totalStudents = (clone $studentBase)->count();
        $officialStudents = (clone $studentBase)->where('registration_status', Student::REG_OFFICIAL)->count();
        $pendingRegistration = (clone $studentBase)->where('registration_status', Student::REG_PENDING)->count();
        $newToday = (clone $studentBase)->whereDate('created_at', today())->count();

        $pendingInvoices = 0;
        $collectedMonth = 0;
        $pendingFeesAmount = 0;
        $overdueCount = 0;

        if ($showFinance) {
            app(InvoiceBillingService::class)->refreshOverdueStatuses(
                $user?->isBranchScoped() ? $user->branch_id : null
            );
            $invoiceBase = BranchScope::invoices();
            $pendingInvoices = (clone $invoiceBase)
                ->whereIn('status', [Invoice::STATUS_PENDING, Invoice::STATUS_PARTIAL, Invoice::STATUS_OVERDUE])
                ->whereColumn('amount_paid', '<', 'amount')
                ->count();
            $overdueCount = (clone $invoiceBase)->where('status', Invoice::STATUS_OVERDUE)->count();
            $collectedMonth = \App\Models\Payment::query()
                ->whereIn('invoice_id', (clone $invoiceBase)->pluck('id'))
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('amount');
            $pendingFeesAmount = (clone $invoiceBase)
                ->whereIn('status', [Invoice::STATUS_PENDING, Invoice::STATUS_PARTIAL, Invoice::STATUS_OVERDUE])
                ->selectRaw('SUM(amount - amount_paid) as bal')
                ->value('bal') ?? 0;
        }

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $branchId = $user?->branch_id;

        $attendanceQuery = Attendance::query()->whereBetween('attendance_date', [$monthStart, $monthEnd]);
        if ($branchId) {
            $attendanceQuery->whereHas('student', fn ($q) => $q->where('branch_id', $branchId));
        }

        $slots = $attendanceQuery
            ->select('attendance_date', DB::raw('count(*) as c'))
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->pluck('c', 'attendance_date');

        $inactiveStudents = $attendance->inactiveStudents($branchId);
        $lowAttendance = $attendance->lowAttendanceStudents(branchId: $branchId);

        return view('erp.dashboard', [
            'showFinance' => $showFinance,
            'totalStudents' => $totalStudents,
            'officialStudents' => $officialStudents,
            'pendingRegistration' => $pendingRegistration,
            'newToday' => $newToday,
            'branchCount' => 1,
            'pendingInvoices' => $pendingInvoices,
            'collectedMonth' => $collectedMonth,
            'pendingFeesAmount' => $pendingFeesAmount,
            'attendanceChartLabels' => $slots->keys()->map(fn ($d) => (string) $d)->values()->all(),
            'attendanceChartCounts' => $slots->values()->all(),
            'overdueCount' => $overdueCount,
            'inactiveStudents' => $inactiveStudents,
            'lowAttendance' => $lowAttendance,
            'user' => $user,
        ]);
    }
}
