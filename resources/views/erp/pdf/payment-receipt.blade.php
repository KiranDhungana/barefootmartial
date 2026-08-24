@php
    use App\Support\PdfHelper;

    $org = config('academy.org');
    $logoPath = PdfHelper::logoPath();
    $student = $invoice->student;
    $branch = $student->branch ?? $invoice->branch;
    $receiptNo = $receiptNumber ?? $invoice->invoice_number;
    $statusLabel = strtoupper($statusLabel ?? $invoice->statusLabel());
    $statusKey = strtolower($statusKey ?? $invoice->status);
    $paymentMethod = $paymentMethod ?? optional($invoice->payments->sortByDesc('id')->first())->payment_method;
    $paidVia = $paidVia ?? $paymentMethod;
    $receivedBy = $receivedBy ?? ($org['legal_name'] ?? 'Barefoot Martial Arts Academy');
    $paidAmount = (float) ($paidAmount ?? $invoice->amount_paid);
    $balanceDue = (float) ($balanceDue ?? $invoice->balanceDue());
    $totalFee = (float) ($invoice->subtotal > 0 ? $invoice->subtotal : $invoice->amount);
    $discount = (float) $invoice->discount_amount;
    $subtotalAfterDiscount = max(0, round($totalFee - $discount, 2));
    $lineItems = $invoice->lineItems;
    $currency = 'Rs.';
    $docDate = $documentDate ?? optional($payment)->paid_at ?? $invoice->created_at ?? now();

    $statusBg = match ($statusKey) {
        'paid' => '#e8f5e9',
        'partial' => '#fff3e0',
        'overdue' => '#ffebee',
        default => '#fff8e1',
    };
    $statusFg = match ($statusKey) {
        'paid' => '#2e7d32',
        'partial' => '#ef6c00',
        'overdue' => '#c62828',
        default => '#f57f17',
    };
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt {{ $receiptNo }}</title>
    <style>
        @page { margin: 28px 32px 36px 32px; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1a2332;
            margin: 0;
            padding: 0;
            position: relative;
        }
        .navy { color: #0b3a66; }
        .red { color: #c62828; }
        .muted { color: #5a6a7a; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }

        .watermark {
            position: absolute;
            top: 280px;
            left: 50%;
            margin-left: -110px;
            width: 220px;
            opacity: 0.06;
            z-index: 0;
        }

        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .header-table td { vertical-align: top; }
        .logo-img { width: 72px; height: auto; }
        .brand-name {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
            line-height: 1.05;
            margin: 4px 0 0 0;
        }
        .brand-name .red { color: #c62828; }
        .brand-sub {
            font-size: 13px;
            font-weight: bold;
            color: #0b3a66;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .tagline {
            font-size: 9px;
            color: #c62828;
            margin: 4px 0 0 0;
            letter-spacing: 0.3px;
        }
        .contact-line {
            font-size: 8.5px;
            color: #334155;
            line-height: 1.55;
            text-align: right;
        }
        .contact-label { color: #0b3a66; font-weight: bold; }

        .title-row { width: 100%; border-collapse: collapse; margin: 8px 0 14px 0; }
        .title-row td { vertical-align: middle; }
        .doc-title {
            font-size: 22px;
            font-weight: bold;
            color: #0b3a66;
            letter-spacing: 1px;
            margin: 0;
        }
        .receipt-no {
            font-size: 11px;
            margin: 4px 0 0 0;
            color: #334155;
        }
        .receipt-no .num { color: #c62828; font-weight: bold; }

        .datetime-box {
            border: 1.5px solid #90caf9;
            border-radius: 8px;
            padding: 8px 12px;
            background: #f5fbff;
            min-width: 210px;
        }
        .datetime-box table { width: 100%; border-collapse: collapse; }
        .datetime-box td { font-size: 9.5px; padding: 2px 4px; vertical-align: middle; }
        .datetime-label { color: #64748b; font-size: 8px; text-transform: uppercase; }

        .section-banner {
            border-radius: 6px;
            color: #fff;
            font-weight: bold;
            font-size: 11px;
            letter-spacing: 0.8px;
            padding: 8px 12px;
            margin: 0 0 0 0;
        }
        .banner-blue { background: #0b3a66; }
        .banner-red { background: #c62828; }

        .student-wrap {
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 6px 6px;
            padding: 10px 12px;
            margin-bottom: 14px;
        }
        .student-grid { width: 100%; border-collapse: collapse; }
        .student-grid td { vertical-align: top; padding: 4px 6px; }
        .field-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.4px;
            margin: 0 0 2px 0;
        }
        .field-value {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }
        .qr-cell { width: 100px; text-align: center; }
        .qr-box {
            border: 1.5px solid #c62828;
            border-radius: 6px;
            padding: 4px;
            display: inline-block;
        }
        .qr-box svg { width: 72px; height: 72px; }
        .qr-caption {
            font-size: 7.5px;
            font-weight: bold;
            color: #c62828;
            letter-spacing: 0.4px;
            margin: 4px 0 0 0;
        }
        .qr-hint { font-size: 7px; color: #94a3b8; margin: 2px 0 0 0; }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 14px 0;
        }
        .items-table th {
            background: #0b3a66;
            color: #fff;
            font-size: 9px;
            letter-spacing: 0.5px;
            text-align: left;
            padding: 8px 8px;
        }
        .items-table th.num, .items-table td.num { text-align: center; width: 28px; }
        .items-table th.qty, .items-table td.qty { text-align: center; width: 48px; }
        .items-table th.money, .items-table td.money { text-align: right; width: 90px; }
        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10.5px;
        }
        .items-table tr:nth-child(even) td { background: #f8fafc; }

        .bottom-grid { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .bottom-grid > tbody > tr > td { vertical-align: top; padding: 0; }
        .pay-meta td { padding: 5px 0; font-size: 10.5px; }
        .pay-meta .label { color: #64748b; width: 110px; }
        .pay-meta .value { font-weight: bold; color: #0f172a; }

        .status-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            margin-top: 10px;
            display: inline-block;
            min-width: 160px;
        }
        .status-card .lbl {
            font-size: 8px;
            color: #64748b;
            letter-spacing: 0.6px;
            margin: 0 0 6px 0;
        }
        .status-pill {
            display: inline-block;
            border-radius: 999px;
            padding: 5px 14px;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.6px;
        }

        .summary-box {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            overflow: hidden;
        }
        .summary-head {
            background: #0b3a66;
            color: #fff;
            font-weight: bold;
            font-size: 11px;
            letter-spacing: 0.6px;
            padding: 8px 12px;
        }
        .summary-body { padding: 8px 12px; }
        .summary-row { width: 100%; border-collapse: collapse; }
        .summary-row td { padding: 5px 0; font-size: 10.5px; }
        .summary-row .amt { text-align: right; font-weight: bold; }
        .summary-row .paid { color: #c62828; }
        .balance-row td {
            background: #e3f2fd;
            padding: 8px 10px;
            font-weight: bold;
            font-size: 12px;
        }
        .balance-row .due { color: #c62828; font-size: 13px; }

        .sig-table { width: 100%; border-collapse: collapse; margin: 28px 0 16px 0; }
        .sig-table td { width: 33%; text-align: center; vertical-align: bottom; padding: 0 12px; }
        .sig-line {
            border-top: 1px solid #94a3b8;
            margin: 0 auto;
            width: 85%;
            padding-top: 6px;
            font-size: 9px;
            color: #64748b;
        }
        .thanks-seal {
            border: 1.5px solid #90caf9;
            border-radius: 50%;
            width: 120px;
            height: 120px;
            margin: 0 auto;
            text-align: center;
            padding-top: 28px;
            background: #f8fbff;
        }
        .thanks-seal .t1 {
            font-size: 11px;
            font-weight: bold;
            color: #0b3a66;
            margin: 0;
        }
        .thanks-seal .t2 {
            font-size: 7.5px;
            color: #64748b;
            margin: 4px 8px 0 8px;
            line-height: 1.3;
        }

        .footer-bar {
            background: #0b3a66;
            color: #fff;
            font-size: 8.5px;
            padding: 9px 14px;
            border-radius: 4px;
            margin-top: 8px;
        }
        .footer-bar table { width: 100%; border-collapse: collapse; }
        .footer-bar td { color: #fff; vertical-align: middle; }
        .footer-bar .right { text-align: right; color: #bbdefb; }
    </style>
</head>
<body>
    @if ($logoPath)
        <img class="watermark" src="{{ $logoPath }}" alt="">
    @endif

    <table class="header-table">
        <tr>
            <td style="width:78px;">
                @if ($logoPath)
                    <img class="logo-img" src="{{ $logoPath }}" alt="Barefoot">
                @endif
            </td>
            <td style="padding-left:8px;">
                <p class="brand-name"><span class="red">{{ $org['brand_line1'] ?? 'BAREFOOT' }}</span></p>
                <p class="brand-sub">{{ $org['brand_line2'] ?? 'MARTIAL ARTS ACADEMY' }}</p>
                <p class="tagline">{{ $org['tagline'] ?? 'Discipline • Respect • Strength' }}</p>
            </td>
            <td style="width:42%;" class="contact-line">
                <div><span class="contact-label">Address:</span> {{ $org['address'] ?? '' }}</div>
                <div><span class="contact-label">Phone:</span> {{ $org['phone'] ?? '' }}</div>
                <div><span class="contact-label">Email:</span> {{ $org['email'] ?? '' }}</div>
                <div><span class="contact-label">Website:</span> {{ $org['website'] ?? '' }}</div>
                @if (! empty($org['pan']) || ! empty($org['vat']))
                    <div>
                        @if (! empty($org['pan']))<span class="contact-label">PAN:</span> {{ $org['pan'] }}@endif
                        @if (! empty($org['pan']) && ! empty($org['vat'])) | @endif
                        @if (! empty($org['vat']))<span class="contact-label">VAT:</span> {{ $org['vat'] }}@endif
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <table class="title-row">
        <tr>
            <td>
                <p class="doc-title">PAYMENT RECEIPT</p>
                <p class="receipt-no">Receipt No: <span class="num">{{ $receiptNo }}</span></p>
            </td>
            <td class="text-right" style="width:240px;">
                <div class="datetime-box">
                    <table>
                        <tr>
                            <td>
                                <div class="datetime-label">Date</div>
                                <div class="bold navy">{{ $docDate->format('F d, Y') }}</div>
                            </td>
                            <td class="text-right">
                                <div class="datetime-label">Time</div>
                                <div class="bold navy">{{ $docDate->format('h:i A') }}</div>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-banner banner-blue">STUDENT DETAILS</div>
    <div class="student-wrap">
        <table class="student-grid">
            <tr>
                <td style="width:38%;">
                    <p class="field-label">Name</p>
                    <p class="field-value">{{ $student->name }}</p>
                    <p class="field-label" style="margin-top:10px;">Student ID</p>
                    <p class="field-value">{{ $student->student_code }}</p>
                </td>
                <td style="width:38%;">
                    <p class="field-label">Phone</p>
                    <p class="field-value">{{ $student->phone ?: '—' }}</p>
                    <p class="field-label" style="margin-top:10px;">Dojang / Branch</p>
                    <p class="field-value">{{ $branch->name ?? '—' }}</p>
                </td>
                <td class="qr-cell">
                    @if (! empty($qrSvg))
                        <div class="qr-box">{!! $qrSvg !!}</div>
                        <p class="qr-caption">SCAN TO VERIFY</p>
                        <p class="qr-hint">Verify this receipt online</p>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="section-banner banner-red">INVOICE DETAILS</div>
    <table class="items-table">
        <thead>
            <tr>
                <th class="num">#</th>
                <th>DESCRIPTION</th>
                <th class="qty">QTY</th>
                <th class="money">UNIT PRICE</th>
                <th class="money">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lineItems as $i => $line)
                <tr>
                    <td class="num">{{ $i + 1 }}</td>
                    <td>{{ $line->description }}</td>
                    <td class="qty">{{ $line->quantity }}</td>
                    <td class="money">{{ $currency }} {{ number_format((float) $line->unit_price, 2) }}</td>
                    <td class="money">{{ $currency }} {{ number_format((float) $line->line_total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td class="num">1</td>
                    <td>{{ $invoice->notes ?: 'Academy fees' }}</td>
                    <td class="qty">1</td>
                    <td class="money">{{ $currency }} {{ number_format($totalFee, 2) }}</td>
                    <td class="money">{{ $currency }} {{ number_format($totalFee, 2) }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="bottom-grid">
        <tr>
            <td style="width:52%; padding-right:16px;">
                <table class="pay-meta">
                    <tr>
                        <td class="label">Payment Method</td>
                        <td class="value">{{ $paymentMethod ? ucfirst($paymentMethod) : '—' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Paid Via</td>
                        <td class="value">{{ $paidVia ? ucfirst($paidVia) : '—' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Received By</td>
                        <td class="value">{{ $receivedBy }}</td>
                    </tr>
                </table>
                <div class="status-card">
                    <p class="lbl">STATUS</p>
                    <span class="status-pill" style="background:{{ $statusBg }};color:{{ $statusFg }};">
                        {{ $statusLabel }}
                    </span>
                </div>
            </td>
            <td style="width:48%;">
                <div class="summary-box">
                    <div class="summary-head">PAYMENT SUMMARY</div>
                    <div class="summary-body">
                        <table class="summary-row">
                            <tr>
                                <td>Total Fee</td>
                                <td class="amt">{{ $currency }} {{ number_format($totalFee, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Discount</td>
                                <td class="amt">{{ $currency }} {{ number_format($discount, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Subtotal</td>
                                <td class="amt">{{ $currency }} {{ number_format($subtotalAfterDiscount, 2) }}</td>
                            </tr>
                            @if ((float) $invoice->late_fee_amount > 0)
                                <tr>
                                    <td>Late fee</td>
                                    <td class="amt">{{ $currency }} {{ number_format((float) $invoice->late_fee_amount, 2) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td>Paid</td>
                                <td class="amt paid">{{ $currency }} {{ number_format($paidAmount, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                    <table class="summary-row balance-row" style="width:100%;">
                        <tr>
                            <td>Balance Due</td>
                            <td class="text-right due">{{ $currency }} {{ number_format($balanceDue, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-line">Student Signature</div>
            </td>
            <td>
                <div class="thanks-seal">
                    <p class="t1">THANK YOU!</p>
                    <p class="t2">Keep training, keep growing!</p>
                </div>
            </td>
            <td>
                <div class="sig-line">Authorized Signature</div>
            </td>
        </tr>
    </table>

    <div class="footer-bar">
        <table>
            <tr>
                <td>Thank you for being part of Barefoot Martial Arts Academy.</td>
                <td class="right">{{ $org['tagline'] ?? 'Discipline • Respect • Strength' }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
