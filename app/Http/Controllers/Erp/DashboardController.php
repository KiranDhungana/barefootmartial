<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\Trainer;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $showFinance = $user && $user->isAdmin();

        $totalStudents = Student::query()->count();

        $pendingInvoices = $showFinance
            ? Invoice::query()->where('status', 'pending')->count()
            : 0;
        $collectedMonth = $showFinance
            ? Invoice::query()
                ->where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('amount')
            : 0;
        $pendingFeesAmount = $showFinance
            ? Invoice::query()->where('status', 'pending')->sum('amount')
            : 0;

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $slots = Attendance::query()
            ->whereBetween('attendance_date', [$monthStart, $monthEnd])
            ->select('attendance_date', DB::raw('count(*) as c'))
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->pluck('c', 'attendance_date');

        $attendanceChartLabels = $slots->keys()->map(fn ($d) => (string) $d)->values()->all();
        $attendanceChartCounts = $slots->values()->all();

        $trainersCount = $showFinance ? Trainer::query()->count() : 0;

        return view('erp.dashboard', [
            'showFinance' => $showFinance,
            'totalStudents' => $totalStudents,
            'pendingInvoices' => $pendingInvoices,
            'collectedMonth' => $collectedMonth,
            'pendingFeesAmount' => $pendingFeesAmount,
            'attendanceChartLabels' => $attendanceChartLabels,
            'attendanceChartCounts' => $attendanceChartCounts,
            'trainersCount' => $trainersCount,
        ]);
    }
}
