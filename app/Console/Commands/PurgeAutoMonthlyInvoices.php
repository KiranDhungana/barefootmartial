<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeAutoMonthlyInvoices extends Command
{
    protected $signature = 'academy:purge-auto-monthly-invoices
                            {--dry-run : List counts only}
                            {--include-paid : Also delete paid auto monthly invoices}';

    protected $description = 'Remove erroneous auto-generated monthly fee invoices (e.g. historical backfill)';

    public function handle(): int
    {
        $q = Invoice::query()
            ->where(function ($query) {
                $query->where('billing_period', 'like', 'monthly:%')
                    ->orWhere('notes', 'like', 'Auto-generated monthly fee%');
            });

        if (! $this->option('include-paid')) {
            $q->whereColumn('amount_paid', '<', 'amount');
        }

        $count = (clone $q)->count();
        $total = (clone $q)->selectRaw('COALESCE(SUM(amount - amount_paid), 0) as bal')->value('bal');

        if ($this->option('dry-run')) {
            $this->info("Would delete {$count} auto monthly invoice(s), outstanding Rs. ".number_format((float) $total, 2));

            return self::SUCCESS;
        }

        if ($count === 0) {
            $this->info('No matching invoices to delete.');

            return self::SUCCESS;
        }

        if (! $this->confirm("Delete {$count} auto monthly invoice(s)? This cannot be undone.")) {
            return self::SUCCESS;
        }

        DB::transaction(function () use ($q) {
            $ids = (clone $q)->pluck('id');
            InvoiceLineItem::query()->whereIn('invoice_id', $ids)->delete();
            Invoice::query()->whereIn('id', $ids)->delete();
        });

        $this->info("Deleted {$count} auto monthly invoice(s).");

        return self::SUCCESS;
    }
}
