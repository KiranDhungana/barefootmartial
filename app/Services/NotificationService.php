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
    public function __construct(
        private StudentFeeSummaryService $feeSummary
    ) {
    }

    /**
     * Consolidated reminder for the student (all open invoices).
     * Kept Invoice argument for backward compatibility with existing callers.
     */
    public function feeReminderMessage(Invoice|Student $invoiceOrStudent): string
    {
        $student = $invoiceOrStudent instanceof Student
            ? $invoiceOrStudent
            : $invoiceOrStudent->student;

        return $this->feeSummary->reminderMessage($student);
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
     * Open invoices (legacy list). Prefer studentsWithOpenBalances() for reminders.
     *
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

    /**
     * One entry per student with consolidated outstanding balance.
     *
     * @return Collection<int, array{student: Student, summary: array, message: string, whatsapp_url: ?string}>
     */
    public function studentsWithOpenBalances(): Collection
    {
        $invoices = $this->overdueInvoicesForReminders();

        return $invoices
            ->groupBy('student_id')
            ->map(function (Collection $group) {
                /** @var Student $student */
                $student = $group->first()->student;
                $summary = $this->feeSummary->summary($student);
                $message = $this->feeSummary->reminderMessage($student);
                $phone = $student->phone ?: $student->parent_contact;

                return [
                    'student' => $student,
                    'summary' => $summary,
                    'message' => $message,
                    'whatsapp_url' => $phone ? WhatsApp::waMeUrl($phone, $message) : null,
                ];
            })
            ->filter(fn (array $row) => ($row['summary']['total_outstanding'] ?? 0) > 0)
            ->sortBy(fn (array $row) => $row['student']->name)
            ->values();
    }

    public function whatsappUrlForInvoice(Invoice $invoice): ?string
    {
        return $this->whatsappUrlForStudent($invoice->student);
    }

    public function whatsappUrlForStudent(Student $student): ?string
    {
        $phone = $student->phone ?: $student->parent_contact;

        return $phone ? WhatsApp::waMeUrl($phone, $this->feeReminderMessage($student)) : null;
    }
}
