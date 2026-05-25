<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\AuditLogger;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        AuditLogger::log('payment.created', $payment, null, $payment->getAttributes());
    }
}
