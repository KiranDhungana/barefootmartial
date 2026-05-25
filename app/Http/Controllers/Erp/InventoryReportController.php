<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\InvoiceLineItem;
use App\Support\BranchScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('finance');
    }

    public function index(Request $request): View
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $rows = $this->salesQuery($year, $month)->get();
        $total = $rows->sum('revenue');

        return view('erp.inventory.report', compact('rows', 'total', 'year', 'month'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $rows = $this->salesQuery($year, $month)->get();

        $filename = "inventory-sales-{$year}-{$month}.csv";

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Item', 'Fee type', 'Qty sold', 'Revenue']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->description,
                    $row->fee_type,
                    $row->qty,
                    $row->revenue,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function salesQuery(int $year, int $month)
    {
        $user = auth()->user();
        $branchId = $user?->isBranchScoped() ? $user->branch_id : null;

        return InvoiceLineItem::query()
            ->select(
                'invoice_line_items.description',
                'invoice_line_items.fee_type',
                DB::raw('SUM(invoice_line_items.quantity) as qty'),
                DB::raw('SUM(invoice_line_items.line_total) as revenue')
            )
            ->join('invoices', 'invoices.id', '=', 'invoice_line_items.invoice_id')
            ->whereYear('invoices.created_at', $year)
            ->whereMonth('invoices.created_at', $month)
            ->where(function ($q) {
                $q->whereNotNull('invoice_line_items.inventory_item_id')
                    ->orWhere('invoice_line_items.fee_type', 'uniform');
            })
            ->when($branchId, fn ($q) => $q->where('invoices.branch_id', $branchId))
            ->groupBy('invoice_line_items.description', 'invoice_line_items.fee_type')
            ->orderByDesc('revenue');
    }
}
