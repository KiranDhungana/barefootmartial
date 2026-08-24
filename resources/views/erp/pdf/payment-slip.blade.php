{{-- Invoice payment slip / receipt — branded layout --}}
@include('erp.pdf.payment-receipt', [
    'invoice' => $invoice,
    'payment' => null,
    'qrSvg' => $qrSvg ?? null,
    'receiptNumber' => $invoice->invoice_number,
    'documentDate' => now(),
    'paymentMethod' => optional($invoice->payments->sortByDesc('id')->first())->payment_method,
    'paidVia' => optional($invoice->payments->sortByDesc('id')->first())->payment_method,
    'paidAmount' => (float) $invoice->amount_paid,
    'balanceDue' => $invoice->balanceDue(),
    'statusLabel' => $invoice->statusLabel(),
    'statusKey' => $invoice->status,
    'receivedBy' => config('academy.org.legal_name', 'Barefoot Martial Arts Academy'),
])
