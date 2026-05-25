<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Support\BranchScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('finance');
    }

    public function index(Request $request): View
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $studentBase = BranchScope::students();
        $totalStudents = (clone $studentBase)->count();
        $officialStudents = (clone $studentBase)->where('registration_status', Student::REG_OFFICIAL)->count();
        $pendingRegistration = (clone $studentBase)->where('registration_status', Student::REG_PENDING)->count();

        $invoiceBase = BranchScope::invoices();
        $invoiceIds = (clone $invoiceBase)->pluck('id');
        $feesCollected = Payment::query()
            ->whereIn('invoice_id', $invoiceIds)
            ->whereYear('paid_at', $year)
            ->whereMonth('paid_at', $month)
            ->sum('amount');

        $pendingFees = (clone $invoiceBase)
            ->whereIn('status', [Invoice::STATUS_PENDING, Invoice::STATUS_PARTIAL, Invoice::STATUS_OVERDUE])
            ->selectRaw('COALESCE(SUM(amount - amount_paid), 0) as bal')
            ->value('bal');

        $pendingCount = (clone $invoiceBase)
            ->whereIn('status', [Invoice::STATUS_PENDING, Invoice::STATUS_PARTIAL, Invoice::STATUS_OVERDUE])
            ->whereColumn('amount_paid', '<', 'amount')
            ->count();

        return view('erp.reports.index', compact(
            'totalStudents',
            'officialStudents',
            'pendingRegistration',
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

        $studentBase = BranchScope::students();
        $invoiceBase = BranchScope::invoices();
        $invoiceIds = (clone $invoiceBase)->pluck('id');

        $rows = [
            ['Metric', 'Value'],
            ['Total students', (clone $studentBase)->count()],
            ['Official students', (clone $studentBase)->where('registration_status', Student::REG_OFFICIAL)->count()],
            ['Pending registration', (clone $studentBase)->where('registration_status', Student::REG_PENDING)->count()],
            ['Fees collected ('.$year.'-'.str_pad((string) $month, 2, '0', STR_PAD_LEFT).')', Payment::query()
                ->whereIn('invoice_id', $invoiceIds)
                ->whereYear('paid_at', $year)
                ->whereMonth('paid_at', $month)
                ->sum('amount')],
            ['Outstanding balance (all)', (clone $invoiceBase)
                ->whereIn('status', [Invoice::STATUS_PENDING, Invoice::STATUS_PARTIAL, Invoice::STATUS_OVERDUE])
                ->selectRaw('COALESCE(SUM(amount - amount_paid), 0) as bal')
                ->value('bal')],
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

        $studentBase = BranchScope::students();
        $invoiceBase = BranchScope::invoices();

        $totalStudents = (clone $studentBase)->count();
        $officialStudents = (clone $studentBase)->where('registration_status', Student::REG_OFFICIAL)->count();
        $pendingRegistration = (clone $studentBase)->where('registration_status', Student::REG_PENDING)->count();

        $invoiceIds = (clone $invoiceBase)->pluck('id');
        $feesCollected = Payment::query()
            ->whereIn('invoice_id', $invoiceIds)
            ->whereYear('paid_at', $year)
            ->whereMonth('paid_at', $month)
            ->sum('amount');
        $pendingFees = (clone $invoiceBase)
            ->whereIn('status', [Invoice::STATUS_PENDING, Invoice::STATUS_PARTIAL, Invoice::STATUS_OVERDUE])
            ->selectRaw('COALESCE(SUM(amount - amount_paid), 0) as bal')
            ->value('bal');
        $pendingCount = (clone $invoiceBase)
            ->whereIn('status', [Invoice::STATUS_PENDING, Invoice::STATUS_PARTIAL, Invoice::STATUS_OVERDUE])
            ->whereColumn('amount_paid', '<', 'amount')
            ->count();

        $label = $year.'-'.str_pad((string) $month, 2, '0', STR_PAD_LEFT);

        return Pdf::loadView('erp.pdf.report-summary', [
            'totalStudents' => $totalStudents,
            'officialStudents' => $officialStudents,
            'pendingRegistration' => $pendingRegistration,
            'feesCollected' => $feesCollected,
            'pendingFees' => $pendingFees,
            'pendingCount' => $pendingCount,
            'periodLabel' => $label,
        ])->download('report-'.$label.'.pdf');
    }
}
