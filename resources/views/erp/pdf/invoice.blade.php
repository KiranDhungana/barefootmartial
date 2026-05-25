<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f3f4f6; }
        .text-end { text-align: right; }
    </style>
</head>

<body>
    @include('partials.pdf-header')
    <p><strong>Invoice {{ $invoice->invoice_number }}</strong>
        @if ($invoice->student->branch)
            <br>{{ $invoice->student->branch->name }}
        @endif
    </p>
    <p><strong>Bill to:</strong> {{ $invoice->student->name }}<br>
        ID: {{ $invoice->student->student_code }}<br>
        @if ($invoice->student->phone)
            Phone: {{ $invoice->student->phone }}
        @endif
    </p>
    <table>
        <tr>
            <th>Description</th>
            <th>Qty</th>
            <th class="text-end">Unit</th>
            <th class="text-end">Total</th>
        </tr>
        @foreach ($invoice->lineItems as $line)
            <tr>
                <td>{{ $line->description }}</td>
                <td>{{ $line->quantity }}</td>
                <td class="text-end">{{ number_format($line->unit_price, 2) }}</td>
                <td class="text-end">{{ number_format($line->line_total, 2) }}</td>
            </tr>
        @endforeach
    </table>
    <table style="margin-top:12px;border:none">
        <tr>
            <td class="text-end" style="border:none">Subtotal</td>
            <td class="text-end" style="border:none;width:100px">{{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if ($invoice->discount_amount > 0)
            <tr>
                <td class="text-end" style="border:none">Discount</td>
                <td class="text-end" style="border:none">−{{ number_format($invoice->discount_amount, 2) }}</td>
            </tr>
        @endif
        @if ($invoice->late_fee_amount > 0)
            <tr>
                <td class="text-end" style="border:none">Late fee</td>
                <td class="text-end" style="border:none">{{ number_format($invoice->late_fee_amount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td class="text-end" style="border:none"><strong>Total</strong></td>
            <td class="text-end" style="border:none"><strong>{{ number_format($invoice->amount, 2) }}</strong></td>
        </tr>
        <tr>
            <td class="text-end" style="border:none">Paid</td>
            <td class="text-end" style="border:none">{{ number_format($invoice->amount_paid, 2) }}</td>
        </tr>
        <tr>
            <td class="text-end" style="border:none"><strong>Balance due</strong></td>
            <td class="text-end" style="border:none"><strong>{{ number_format($invoice->balanceDue(), 2) }}</strong></td>
        </tr>
    </table>
    <p><strong>Status:</strong> {{ $invoice->statusLabel() }}</p>
    @if ($invoice->due_date)
        <p><strong>Due date:</strong> {{ $invoice->due_date->format('M j, Y') }}</p>
    @endif
</body>

</html>
