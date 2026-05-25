<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\BeltPromotion;
use App\Models\Branch;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Payment;
use App\Models\Student;
use App\Services\AttendanceAnalyticsService;
use App\Services\BranchReportService;
use App\Services\InvoiceBillingService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HqDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! $request->user()?->isSuperAdmin()) {
                return redirect()->route('erp.dashboard')->with('error', 'Head office dashboard is for super admins only.');
            }

            return $next($request);
        });
    }

    public function index(): View
    {
        app(InvoiceBillingService::class)->refreshOverdueStatuses();

        $year = (int) now()->format('Y');
        $month = (int) now()->format('m');

        $totalBranches = Branch::query()->count();
        $totalStudents = Student::query()->count();
        $officialStudents = Student::query()->where('registration_status', Student::REG_OFFICIAL)->count();
        $newToday = Student::query()->whereDate('created_at', today())->count();
        $pendingRegistration = Student::query()->where('registration_status', Student::REG_PENDING)->count();

        $collectedMonth = Payment::query()
            ->whereYear('paid_at', $year)
            ->whereMonth('paid_at', $month)
            ->sum('amount');

        $pendingFees = DB::table('invoices')
            ->whereIn('status', [Invoice::STATUS_PENDING, Invoice::STATUS_PARTIAL, Invoice::STATUS_OVERDUE])
            ->selectRaw('COALESCE(SUM(amount - amount_paid), 0) as bal')
            ->value('bal');

        $overdueCount = Invoice::query()->where('status', Invoice::STATUS_OVERDUE)->count();

        $rankings = app(BranchReportService::class)->branchRankings($year, $month);
        $inactive = app(AttendanceAnalyticsService::class)->inactiveStudents();

        $branchGrowth = Branch::query()
            ->withCount(['students as students_count' => fn ($q) => $q->where('registration_status', Student::REG_OFFICIAL)])
            ->orderByDesc('students_count')
            ->get();

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $attendanceChart = DB::table('attendances')
            ->whereBetween('attendance_date', [$monthStart, $monthEnd])
            ->whereIn('status', ['present', 'late'])
            ->select('attendance_date', DB::raw('count(*) as c'))
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->pluck('c', 'attendance_date');

        $activeStudents = Student::query()->where('status', Student::STATUS_ACTIVE)->count();
        $inactiveStudents = Student::query()->whereIn('status', [Student::STATUS_INACTIVE, Student::STATUS_SUSPENDED])->count();

        $uniformSalesMonth = InvoiceLineItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_line_items.invoice_id')
            ->whereYear('invoices.created_at', $year)
            ->whereMonth('invoices.created_at', $month)
            ->where(function ($q) {
                $q->where('invoice_line_items.fee_type', 'uniform')
                    ->orWhereNotNull('invoice_line_items.inventory_item_id');
            })
            ->sum('invoice_line_items.line_total');

        $beltExamsMonth = BeltPromotion::query()
            ->whereYear('promoted_at', $year)
            ->whereMonth('promoted_at', $month)
            ->count();

        $growthLabels = [];
        $growthCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $growthLabels[] = $d->format('M Y');
            $growthCounts[] = Student::query()
                ->where('registration_status', Student::REG_OFFICIAL)
                ->whereYear('registered_at', $d->year)
                ->whereMonth('registered_at', $d->month)
                ->count();
        }

        return view('erp.hq.dashboard', [
            'totalBranches' => $totalBranches,
            'totalStudents' => $totalStudents,
            'officialStudents' => $officialStudents,
            'newToday' => $newToday,
            'pendingRegistration' => $pendingRegistration,
            'collectedMonth' => $collectedMonth,
            'pendingFees' => $pendingFees,
            'overdueCount' => $overdueCount,
            'rankings' => $rankings,
            'inactiveCount' => $inactive->count(),
            'branchGrowth' => $branchGrowth,
            'attendanceChartLabels' => $attendanceChart->keys()->map(fn ($d) => (string) $d)->values()->all(),
            'attendanceChartCounts' => $attendanceChart->values()->all(),
            'activeStudents' => $activeStudents,
            'inactiveStudents' => $inactiveStudents,
            'uniformSalesMonth' => $uniformSalesMonth,
            'beltExamsMonth' => $beltExamsMonth,
            'growthLabels' => $growthLabels,
            'growthCounts' => $growthCounts,
        ]);
    }
}
