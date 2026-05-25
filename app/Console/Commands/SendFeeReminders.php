<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendFeeReminders extends Command
{
    protected $signature = 'academy:send-fee-reminders {--dry-run : List only, do not send}';

    protected $description = 'Email fee reminders for open invoices (when student/parent email exists)';

    public function handle(NotificationService $notifications): int
    {
        $count = 0;
        foreach ($notifications->overdueInvoicesForReminders() as $invoice) {
            $student = $invoice->student;
            $email = $student->parents()->value('email') ?: null;
            if (! $email && filter_var($student->parent_contact, FILTER_VALIDATE_EMAIL)) {
                $email = $student->parent_contact;
            }
            if (! $email) {
                continue;
            }

            $subject = 'Fee reminder — '.$invoice->invoice_number;
            $body = $notifications->feeReminderMessage($invoice);

            if ($this->option('dry-run')) {
                $this->line($email.' — '.$student->name.' — '.$invoice->balanceDue());

                continue;
            }

            if ($notifications->sendEmail($email, $subject, $body, $student)) {
                $count++;
            }
        }

        $this->info($this->option('dry-run') ? 'Dry run complete.' : "Sent {$count} reminder(s).");

        return self::SUCCESS;
    }
}
