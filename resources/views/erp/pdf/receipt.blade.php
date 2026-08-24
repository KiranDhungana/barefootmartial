{{-- Individual payment receipt — branded layout --}}
@include('erp.pdf.payment-receipt', [
    'invoice' => $invoice,
    'payment' => $payment,
    'qrSvg' => $qrSvg ?? null,
    'receiptNumber' => $payment->receipt_number,
    'documentDate' => $payment->paid_at ?? now(),
    'paymentMethod' => $payment->payment_method,
    'paidVia' => $payment->payment_method,
    'paidAmount' => (float) $payment->amount,
    'balanceDue' => $invoice->balanceDue(),
    'statusLabel' => $invoice->statusLabel(),
    'statusKey' => $invoice->status,
    'receivedBy' => config('academy.org.legal_name', 'Barefoot Martial Arts Academy'),
])
