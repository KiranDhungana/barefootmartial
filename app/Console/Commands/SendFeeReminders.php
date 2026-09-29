<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendFeeReminders extends Command
{
    protected $signature = 'academy:send-fee-reminders {--dry-run : List only, do not send}';

    protected $description = 'Email fee reminders with consolidated outstanding balance (one per student)';

    public function handle(NotificationService $notifications): int
    {
        $count = 0;

        foreach ($notifications->studentsWithOpenBalances() as $row) {
            $student = $row['student'];
            $summary = $row['summary'];
            $email = $student->parents()->value('email') ?: null;
            if (! $email && filter_var($student->parent_contact, FILTER_VALIDATE_EMAIL)) {
                $email = $student->parent_contact;
            }
            if (! $email) {
                continue;
            }

            $subject = 'Fee reminder — total outstanding Rs. '.number_format($summary['total_outstanding'], 2);
            $body = $row['message'];

            if ($this->option('dry-run')) {
                $this->line($email.' — '.$student->name.' — Rs. '.number_format($summary['total_outstanding'], 2)
                    .' ('.$summary['open_count'].' invoice(s))');

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
