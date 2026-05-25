<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\BranchReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchReportController extends Controller
{
    public function __construct(private BranchReportService $reports)
    {
        $this->middleware('finance');
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        $branches = $user->isSuperAdmin()
            ? Branch::query()->orderBy('name')->get()
            : Branch::query()->where('id', $user->branch_id)->get();

        $branchId = $user->isBranchScoped()
            ? $user->branch_id
            : ($request->integer('branch_id') ?: $branches->first()?->id);

        $tab = $request->input('tab', 'daily');
        $date = $request->input('date', now()->toDateString());
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $daily = $this->reports->dailyReport($branchId, $date);
        $monthly = $this->reports->monthlyReport($branchId, $year, $month);
        $rankings = $user->isSuperAdmin()
            ? $this->reports->branchRankings($year, $month)
            : collect();

        return view('erp.branch-reports.index', compact(
            'branches',
            'branchId',
            'tab',
            'date',
            'year',
            'month',
            'daily',
            'monthly',
            'rankings'
        ));
    }

    public function exportPdf(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $user = auth()->user();
        $branchId = $user->isBranchScoped()
            ? $user->branch_id
            : $request->integer('branch_id');
        $branch = Branch::query()->find($branchId);
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $monthly = $this->reports->monthlyReport($branchId, $year, $month);
        $daily = $this->reports->dailyReport($branchId, $request->input('date', now()->toDateString()));

        return Pdf::loadView('erp.pdf.branch-report', compact('branch', 'monthly', 'daily'))
            ->download('branch-report-'.($branch->code ?? 'all').'-'.$year.'-'.$month.'.pdf');
    }
}
