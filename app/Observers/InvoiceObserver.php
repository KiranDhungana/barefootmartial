<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\AuditLogger;

class InvoiceObserver
{
    public function created(Invoice $invoice): void
    {
        AuditLogger::log('invoice.created', $invoice, null, $invoice->getAttributes());
    }

    public function updated(Invoice $invoice): void
    {
        $dirty = $invoice->getChanges();
        if ($dirty === []) {
            return;
        }
        $old = [];
        foreach (array_keys($dirty) as $key) {
            $old[$key] = $invoice->getOriginal($key);
        }
        AuditLogger::log('invoice.updated', $invoice, $old, $dirty);
    }

    public function deleted(Invoice $invoice): void
    {
        AuditLogger::log('invoice.deleted', $invoice, $invoice->getAttributes(), null);
    }
}
