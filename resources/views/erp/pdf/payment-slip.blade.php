<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Payment slip {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #f3f4f6; }
        .text-end { text-align: right; }
        .highlight { font-size: 16px; font-weight: bold; margin-top: 16px; }
    </style>
</head>

<body>
    @include('partials.pdf-header')
    <p><strong>Payment slip</strong> — {{ $invoice->invoice_number }}</p>
    @if ($invoice->student->branch)
        <p>Branch: {{ $invoice->student->branch->name }}</p>
    @endif
    <p><strong>Student:</strong> {{ $invoice->student->name }} ({{ $invoice->student->student_code }})</p>
    <p>Due date: {{ optional($invoice->due_date)->format('M j, Y') ?? '—' }}</p>
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->lineItems as $line)
                <tr>
                    <td>{{ $line->description }}</td>
                    <td class="text-end">{{ number_format($line->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="highlight">Balance due: {{ number_format($invoice->balanceDue(), 2) }}</p>
    <p class="small">Paid to date: {{ number_format($invoice->amount_paid, 2) }} / {{ number_format($invoice->amount, 2) }}</p>
    <p style="margin-top:24px;font-size:10px;color:#666;">Present this slip at the branch office. Online payment not available — pay in person.</p>
</body>

</html>