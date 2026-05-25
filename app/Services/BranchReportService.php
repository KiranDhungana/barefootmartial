<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\InvoiceLineItem;
use App\Models\Payment;
use App\Models\Student;
use App\Support\BranchScope;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BranchReportService
{
    public function dailyReport(?int $branchId, ?string $date = null): array
    {
        $date = $date ?? now()->toDateString();
        $branchId = $this->resolveBranchId($branchId);

        $paymentQ = Payment::query()
            ->whereDate('paid_at', $date)
            ->whereHas('invoice', fn ($q) => $q->when($branchId, fn ($qq) => $qq->where('branch_id', $branchId)));

        $feesCollected = (clone $paymentQ)->sum('amount');

        $uniformSales = InvoiceLineItem::query()
            ->whereHas('invoice', function ($q) use ($branchId, $date) {
                $q->whereDate('created_at', $date);
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->where(function ($q) {
                $q->where('fee_type', 'uniform')
                    ->orWhere('fee_type', 'inventory')
                    ->orWhereNotNull('inventory_item_id');
            })
            ->sum('line_total');

        $expenses = Expense::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('expense_date', $date)
            ->sum('amount');

        $newStudents = Student::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('created_at', $date)
            ->count();

        return [
            'date' => $date,
            'fees_collected' => $feesCollected,
            'uniform_sales' => $uniformSales,
            'expenses' => $expenses,
            'net' => $feesCollected + $uniformSales - $expenses,
            'new_students' => $newStudents,
        ];
    }

    public function monthlyReport(?int $branchId, ?int $year = null, ?int $month = null): array
    {
        $year = $year ?? (int) now()->format('Y');
        $month = $month ?? (int) now()->format('m');
        $branchId = $this->resolveBranchId($branchId);

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $feesCollected = Payment::query()
            ->whereBetween('paid_at', [$start, $end])
            ->whereHas('invoice', fn ($q) => $q->when($branchId, fn ($qq) => $qq->where('branch_id', $branchId)))
            ->sum('amount');

        $uniformSales = InvoiceLineItem::query()
            ->whereHas('invoice', function ($q) use ($branchId, $start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->where(function ($q) {
                $q->where('fee_type', 'uniform')
                    ->orWhere('fee_type', 'inventory')
                    ->orWhereNotNull('inventory_item_id');
            })
            ->sum('line_total');

        $expenses = Expense::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->sum('amount');

        $revenue = $feesCollected + $uniformSales;
        $profit = $revenue - $expenses;

        $pendingFees = DB::table('invoices')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->selectRaw('COALESCE(SUM(amount - amount_paid), 0) as bal')
            ->value('bal');

        $studentGrowth = Student::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        return [
            'year' => $year,
            'month' => $month,
            'fees_collected' => $feesCollected,
            'uniform_sales' => $uniformSales,
            'revenue' => $revenue,
            'expenses' => $expenses,
            'profit' => $profit,
            'pending_fees' => $pendingFees,
            'student_growth' => $studentGrowth,
        ];
    }

    /**
     * @return Collection<int, array{branch: Branch, score: float, metrics: array}>
     */
    public function branchRankings(?int $year = null, ?int $month = null): Collection
    {
        $year = $year ?? (int) now()->format('Y');
        $month = $month ?? (int) now()->format('m');
        $attendance = app(AttendanceAnalyticsService::class);

        return Branch::query()->orderBy('name')->get()->map(function (Branch $branch) use ($year, $month, $attendance) {
            $monthly = $this->monthlyReport($branch->id, $year, $month);
            $official = Student::query()
                ->where('branch_id', $branch->id)
                ->where('registration_status', Student::REG_OFFICIAL)
                ->count();
            $pendingReg = Student::query()
                ->where('branch_id', $branch->id)
                ->where('registration_status', Student::REG_PENDING)
                ->count();
            $summary = $attendance->monthlySummary($year, $month, $branch->id);
            $avgAttendance = $summary->avg('percent') ?? 0;
            $compliance = $official > 0
                ? max(0, 100 - ($pendingReg / max(1, $official + $pendingReg)) * 100)
                : 0;

            $revenueScore = min(100, ($monthly['revenue'] / 100000) * 100);
            $growthScore = min(100, $monthly['student_growth'] * 10);
            $attendanceScore = min(100, $avgAttendance);
            $complianceScore = $compliance;

            $score = round(
                $revenueScore * 0.35
                + $growthScore * 0.2
                + $attendanceScore * 0.25
                + $complianceScore * 0.2,
                1
            );

            return [
                'branch' => $branch,
                'score' => $score,
                'metrics' => [
                    'revenue' => $monthly['revenue'],
                    'student_growth' => $monthly['student_growth'],
                    'avg_attendance' => round($avgAttendance, 1),
                    'compliance' => round($compliance, 1),
                    'pending_registration' => $pendingReg,
                ],
            ];
        })->sortByDesc('score')->values();
    }

    private function resolveBranchId(?int $branchId): ?int
    {
        $user = auth()->user();
        if ($user?->isBranchScoped()) {
            return (int) $user->branch_id;
        }

        return $branchId;
    }
}
