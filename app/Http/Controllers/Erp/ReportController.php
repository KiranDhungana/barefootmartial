<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request): View
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $totalStudents = Student::query()->count();

        $feesCollected = Invoice::query()
            ->where('status', 'paid')
            ->whereYear('paid_at', $year)
            ->whereMonth('paid_at', $month)
            ->sum('amount');

        $pendingFees = Invoice::query()
            ->where('status', 'pending')
            ->sum('amount');

        $pendingCount = Invoice::query()->where('status', 'pending')->count();

        return view('erp.reports.index', compact(
            'totalStudents',
            'feesCollected',
            'pendingFees',
            'pendingCount',
            'year',
            'month'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $rows = [
            ['Metric', 'Value'],
            ['Total students', Student::query()->count()],
            ['Fees collected ('.$year.'-'.str_pad((string) $month, 2, '0', STR_PAD_LEFT).')', Invoice::query()
                ->where('status', 'paid')
                ->whereYear('paid_at', $year)
                ->whereMonth('paid_at', $month)
                ->sum('amount')],
            ['Pending fees (all)', Invoice::query()->where('status', 'pending')->sum('amount')],
        ];

        $filename = 'report-'.$year.'-'.str_pad((string) $month, 2, '0', STR_PAD_LEFT).'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPdf(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $totalStudents = Student::query()->count();
        $feesCollected = Invoice::query()
            ->where('status', 'paid')
            ->whereYear('paid_at', $year)
            ->whereMonth('paid_at', $month)
            ->sum('amount');
        $pendingFees = Invoice::query()
            ->where('status', 'pending')
            ->sum('amount');
        $pendingCount = Invoice::query()->where('status', 'pending')->count();

        $label = $year.'-'.str_pad((string) $month, 2, '0', STR_PAD_LEFT);

        return Pdf::loadView('erp.pdf.report-summary', [
            'totalStudents' => $totalStudents,
            'feesCollected' => $feesCollected,
            'pendingFees' => $pendingFees,
            'pendingCount' => $pendingCount,
            'periodLabel' => $label,
        ])->download('report-'.$label.'.pdf');
    }
}
