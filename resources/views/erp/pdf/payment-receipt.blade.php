@php
    use App\Support\PdfHelper;

    $org = config('academy.org');
    $student = $invoice->student;
    $logoPath = PdfHelper::logoPath();
    $qrPath = PdfHelper::publicImagePath($org['qr_path'] ?? 'images/qr_final.jpeg');

    $receiptNo = $receiptNumber ?? optional($payment)->receipt_number ?? $invoice->invoice_number;
    $docDate = $documentDate ?? optional($payment)->paid_at ?? $invoice->created_at ?? now();
    $paidAmount = (float) ($paidAmount ?? optional($payment)->amount ?? $invoice->amount_paid);
    $paymentMethod = $paymentMethod ?? optional($payment)->payment_method
        ?? optional($invoice->payments->sortByDesc('id')->first())->payment_method;

    $amountWords = PdfHelper::amountInWords($paidAmount);

    $methods = [
        'cash' => 'Cash',
        'esewa' => 'eSewa',
        'bank' => 'Fonepay / Bank Transfer',
        'other' => 'Other',
    ];

    // Particulars: prefer this payment's share of invoice lines, else invoice lines, else single line.
    $rows = collect();
    if ($payment && $invoice->lineItems->isNotEmpty()) {
        foreach ($invoice->lineItems as $line) {
            $rows->push((object) [
                'description' => $line->description.($line->size ? ' ('.$line->size.')' : ''),
                'amount' => (float) $line->line_total,
            ]);
        }
        // If this is a partial payment, show a clear payment line as well when totals differ.
        $invoiceTotal = (float) $invoice->totalAmount();
        if ($invoiceTotal > 0 && abs($paidAmount - $invoiceTotal) > 0.01) {
            $rows = collect([(object) [
                'description' => 'Payment received against invoice '.$invoice->invoice_number,
                'amount' => $paidAmount,
            ]]);
        }
    } elseif ($invoice->lineItems->isNotEmpty() && ! $payment) {
        foreach ($invoice->lineItems as $line) {
            $rows->push((object) [
                'description' => $line->description.($line->size ? ' ('.$line->size.')' : ''),
                'amount' => (float) $line->line_total,
            ]);
        }
    } else {
        $rows->push((object) [
            'description' => 'Payment received'.($invoice->invoice_number ? ' — '.$invoice->invoice_number : ''),
            'amount' => $paidAmount,
        ]);
    }

    $padRows = max(0, 5 - $rows->count());
    $isChecked = function (string $key) use ($paymentMethod): bool {
        if (! $paymentMethod) {
            return false;
        }
        $m = strtolower((string) $paymentMethod);
        if ($key === 'bank') {
            return in_array($m, ['bank', 'fonepay', 'card'], true);
        }

        return $m === $key;
    };
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $receiptNo }}</title>
    <style>
        @page { margin: 18px 20px 22px 20px; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111;
            margin: 0;
            padding: 0;
        }
        .blue { color: #0b3a66; }
        .red { color: #d82027; }
        .muted { color: #555; }
        .bold { font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        table { border-collapse: collapse; }
        .w-100 { width: 100%; }

        .header td { vertical-align: top; }
        .logo { width: 78px; height: auto; }
        .brand-title {
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 1px;
            line-height: 1;
            margin: 2px 0 0 0;
            color: #111;
        }
        .brand-sub {
            font-size: 13px;
            font-weight: bold;
            color: #d82027;
            letter-spacing: 0.5px;
            margin: 2px 0 4px 0;
        }
        .brand-tag {
            font-size: 8px;
            letter-spacing: 0.6px;
            color: #222;
            text-transform: uppercase;
        }
        .receipt-badge {
            background: #0b3a66;
            color: #fff;
            padding: 8px 14px 8px 18px;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 2px;
            text-align: right;
        }
        .contact-line {
            font-size: 9px;
            color: #222;
            line-height: 1.55;
            margin-top: 6px;
        }

        .meta-wrap { margin-top: 16px; }
        .meta-line {
            margin-bottom: 6px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
            font-size: 10px;
        }
        .meta-line .k { color: #333; display: inline-block; width: 78px; }
        .section-title {
            color: #0b3a66;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 6px;
        }

        .items { margin-top: 14px; width: 100%; }
        .items th {
            background: #0b3a66;
            color: #fff;
            font-size: 9px;
            letter-spacing: 0.4px;
            padding: 7px 6px;
            text-align: left;
            border: 1px solid #0b3a66;
        }
        .items td {
            border: 1px solid #d0d0d0;
            padding: 7px 6px;
            vertical-align: top;
            height: 22px;
        }
        .items .sn { width: 36px; text-align: center; }
        .items .amt { width: 120px; text-align: right; }
        .total-table td {
            border: 1px solid #0b3a66;
            padding: 7px 8px;
            font-weight: bold;
        }
        .total-table .label {
            background: #0b3a66;
            color: #fff;
            text-align: right;
            width: 70%;
        }
        .words { margin-top: 8px; font-size: 9.5px; }
        .words .k { color: #0b3a66; font-weight: bold; }

        .bottom { margin-top: 14px; }
        .pay-title { color: #0b3a66; font-weight: bold; font-size: 11px; margin-bottom: 6px; }
        .check {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid #333;
            margin-right: 4px;
            text-align: center;
            font-size: 8px;
            line-height: 10px;
        }
        .check.on { background: #0b3a66; color: #fff; border-color: #0b3a66; }

        .accept-box {
            border: 1px solid #cfcfcf;
            padding: 8px 6px;
            text-align: center;
            background: #fafafa;
        }
        .accept-label { font-size: 8px; color: #666; margin-bottom: 3px; }
        .accept-brand { font-size: 14px; font-weight: bold; color: #d82027; letter-spacing: 0.5px; }
        .accept-sub { font-size: 7px; color: #666; margin-top: 2px; }

        .qr-box { text-align: center; }
        .qr-img { width: 110px; height: 110px; }
        .qr-name { font-size: 8px; font-weight: bold; color: #0b3a66; margin-top: 4px; text-transform: uppercase; }
        .qr-meta { font-size: 7.5px; color: #666; line-height: 1.35; }

        .thanks {
            margin-top: 12px;
            color: #0b3a66;
            font-size: 12px;
            font-style: italic;
            font-weight: bold;
        }
        .thanks-sub { color: #555; font-size: 8.5px; margin-top: 2px; }

        .sign-wrap { text-align: center; margin-top: 18px; }
        .sign-name { font-size: 10px; font-style: italic; }
        .sign-line {
            border-top: 1px solid #333;
            width: 150px;
            margin: 0 auto 4px auto;
            padding-top: 2px;
        }
        .sign-label { font-size: 8px; color: #555; }

        .footer {
            margin-top: 16px;
            background: #0b3a66;
            color: #fff;
            padding: 8px 10px;
            font-size: 8px;
            border-left: 5px solid #d82027;
        }
        .footer td { color: #fff; vertical-align: middle; }
        .footer .tag { font-style: italic; letter-spacing: 0.8px; text-transform: uppercase; }
    </style>
</head>
<body>
    <table class="w-100 header">
        <tr>
            <td style="width: 90px;">
                @if ($logoPath)
                    <img class="logo" src="{{ $logoPath }}" alt="Logo">
                @endif
            </td>
            <td>
                <div class="brand-title">{{ $org['brand_line1'] ?? 'BAREFOOT' }}</div>
                <div class="brand-sub">{{ $org['brand_line2'] ?? 'MARTIAL ARTS ACADEMY' }}</div>
                <div class="brand-tag">{{ $org['tagline'] ?? 'DISCIPLINE • FOCUS • STRENGTH • RESPECT' }}</div>
            </td>
            <td style="width: 210px;" class="text-right">
                <div class="receipt-badge">RECEIPT</div>
                <div class="contact-line text-right">
                    {{ $org['address'] ?? '' }}<br>
                    {{ $org['phone'] ?? '' }}<br>
                    {{ $org['website'] ?? '' }}<br>
                    PAN No. {{ $org['pan'] ?? '—' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="w-100 meta-wrap">
        <tr>
            <td style="width: 48%; vertical-align: top; padding-right: 14px;">
                <div class="meta-line"><span class="k">Receipt No.</span> {{ $receiptNo }}</div>
                <div class="meta-line"><span class="k">Date (AD)</span> {{ optional($docDate)->format('Y-m-d') ?? '—' }}</div>
                <div class="meta-line"><span class="k">Invoice No.</span> {{ $invoice->invoice_number }}</div>
            </td>
            <td style="width: 52%; vertical-align: top;">
                <div class="section-title">Received From</div>
                <div class="meta-line"><span class="k">Name</span> {{ $student->name }}</div>
                <div class="meta-line"><span class="k">Code</span> {{ $student->student_code }}</div>
                <div class="meta-line"><span class="k">Address</span> {{ $student->address ?: ($student->branch->name ?? '—') }}</div>
                <div class="meta-line"><span class="k">Contact</span> {{ $student->phone ?: ($student->parent_contact ?: '—') }}</div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="sn">SN</th>
                <th>PARTICULARS</th>
                <th class="amt">AMOUNT (NPR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $row)
                <tr>
                    <td class="sn">{{ $i + 1 }}</td>
                    <td>{{ $row->description }}</td>
                    <td class="amt">{{ number_format((float) $row->amount, 2) }}</td>
                </tr>
            @endforeach
            @for ($i = 0; $i < $padRows; $i++)
                <tr>
                    <td class="sn">&nbsp;</td>
                    <td></td>
                    <td class="amt"></td>
                </tr>
            @endfor
        </tbody>
    </table>

    <table class="w-100 total-table" style="margin-top:0;">
        <tr>
            <td class="label">TOTAL AMOUNT</td>
            <td class="text-right">Rs. {{ number_format($paidAmount, 2) }}</td>
        </tr>
    </table>

    <div class="words">
        <span class="k">Amount in Words:</span>
        {{ $amountWords }}
    </div>

    <table class="w-100 bottom">
        <tr>
            <td style="width: 34%; vertical-align: top; padding-right: 8px;">
                <div class="pay-title">Payment Method</div>
                @foreach ($methods as $key => $label)
                    <div style="margin-bottom: 4px;">
                        <span class="check {{ $isChecked($key) ? 'on' : '' }}">
                            {{ $isChecked($key) ? '✓' : '' }}
                        </span>
                        {{ $label }}
                    </div>
                @endforeach
                <div class="thanks">Thank you for your payment!</div>
                <div class="thanks-sub">Your trust and support inspire us to grow together.</div>
            </td>
            <td style="width: 22%; vertical-align: top; padding-right: 8px;">
                <div class="accept-box">
                    <div class="accept-label">We Accept</div>
                    <div class="accept-brand">fonepay</div>
                    <div class="accept-sub">Authorized payment partner</div>
                </div>
            </td>
            <td style="width: 24%; vertical-align: top;" class="qr-box">
                @if ($qrPath)
                    <img class="qr-img" src="{{ $qrPath }}" alt="Fonepay QR">
                    <div class="qr-name">{{ $org['legal_name'] ?? 'BAREFOOT MARTIAL ARTS ACADEMY' }}</div>
                    <div class="qr-meta">
                        Terminal: {{ $org['fonepay_terminal'] ?? '—' }}<br>
                        Address: {{ $org['fonepay_address'] ?? 'PARASI' }}
                    </div>
                @endif
            </td>
            <td style="width: 20%; vertical-align: bottom;">
                <div class="sign-wrap">
                    <div class="sign-name">{{ $org['authorized_signatory'] ?? '' }}</div>
                    <div class="sign-line"></div>
                    <div class="sign-label">Authorized Signature</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="w-100 footer">
        <tr>
            <td style="width: 30%;">{{ $org['website'] ?? '' }}</td>
            <td class="text-center tag" style="width: 40%;">{{ $org['footer_tagline'] ?? 'TRAIN TODAY, LEAD TOMORROW.' }}</td>
            <td class="text-right" style="width: 30%;">{{ $org['phone'] ?? '' }}</td>
        </tr>
    </table>
</body>
</html>
