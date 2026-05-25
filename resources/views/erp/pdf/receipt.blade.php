<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $payment->receipt_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; }
        .box { border: 1px solid #333; padding: 16px; margin-top: 16px; }
    </style>
</head>

<body>
    @include('partials.pdf-header')
    <p><strong>Payment receipt</strong></p>
    <div class="box">
        <p><strong>Receipt #:</strong> {{ $payment->receipt_number }}</p>
        <p><strong>Date:</strong> {{ $payment->paid_at->format('M j, Y H:i') }}</p>
        <p><strong>Student:</strong> {{ $invoice->student->name }} ({{ $invoice->student->student_code }})</p>
        <p><strong>Invoice:</strong> {{ $invoice->invoice_number }}</p>
        <p><strong>Amount paid:</strong> {{ number_format($payment->amount, 2) }}</p>
        <p><strong>Method:</strong> {{ ucfirst($payment->payment_method) }}</p>
        <p><strong>Invoice balance remaining:</strong> {{ number_format($invoice->balanceDue(), 2) }}</p>
        @if ($payment->notes)
            <p><strong>Notes:</strong> {{ $payment->notes }}</p>
        @endif
    </div>
    <p style="margin-top:24px;font-size:10px;color:#666">Thank you for your payment.</p>
</body>

</html>
