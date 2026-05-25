<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Models\Student;
use App\Support\BranchScope;
use App\Support\WhatsApp;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function feeReminderMessage(Invoice $invoice): string
    {
        $s = $invoice->student;
        $lines = [
            'Hello '.$s->name.',',
            'Fee reminder from Barefoot Martial Arts.',
            'Invoice: '.$invoice->invoice_number,
            'Balance due: '.number_format($invoice->balanceDue(), 2),
        ];
        if ($invoice->due_date) {
            $lines[] = 'Due: '.$invoice->due_date->format('M j, Y');
        }

        return implode("\n", $lines);
    }

    public function sendEmail(string $to, string $subject, string $body, ?Student $student = null, ?int $sentBy = null): bool
    {
        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });
            $status = 'sent';
        } catch (\Throwable $e) {
            $status = 'failed';
        }

        NotificationLog::create([
            'channel' => 'email',
            'recipient' => $to,
            'subject' => $subject,
            'body' => $body,
            'student_id' => $student?->id,
            'sent_by' => $sentBy,
            'status' => $status,
        ]);

        return $status === 'sent';
    }

    public function logSms(string $phone, string $body, ?Student $student = null, ?int $sentBy = null): void
    {
        NotificationLog::create([
            'channel' => 'sms',
            'recipient' => $phone,
            'subject' => 'SMS',
            'body' => $body,
            'student_id' => $student?->id,
            'sent_by' => $sentBy,
            'status' => 'logged',
        ]);
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function overdueInvoicesForReminders(): Collection
    {
        return BranchScope::invoices()
            ->with('student')
            ->whereIn('status', [Invoice::STATUS_PENDING, Invoice::STATUS_PARTIAL, Invoice::STATUS_OVERDUE])
            ->whereColumn('amount_paid', '<', 'amount')
            ->get();
    }

    public function whatsappUrlForInvoice(Invoice $invoice): ?string
    {
        $phone = $invoice->student->phone ?: $invoice->student->parent_contact;

        return $phone ? WhatsApp::waMeUrl($phone, $this->feeReminderMessage($invoice)) : null;
    }
}
