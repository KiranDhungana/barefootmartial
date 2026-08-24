<?php

namespace App\Console\Commands;

use App\Services\MonthlyFeeService;
use Illuminate\Console\Command;

class GenerateMonthlyFees extends Command
{
    protected $signature = 'academy:generate-monthly-fees {--dry-run : Show how many invoices would be created}';

    protected $description = 'Auto-create monthly fee invoices from each student admission (join) date';

    public function handle(MonthlyFeeService $monthlyFees): int
    {
        if ($this->option('dry-run')) {
            $students = \App\Models\Student::query()
                ->where('registration_status', \App\Models\Student::REG_OFFICIAL)
                ->whereIn('status', [
                    \App\Models\Student::STATUS_ACTIVE,
                    \App\Models\Student::STATUS_PENDING_FEE,
                ])
                ->whereNotNull('join_date')
                ->get();

            $wouldCreate = 0;
            foreach ($students as $student) {
                if ($student->hasFullScholarship() || $monthlyFees->monthlyAmountFor($student) <= 0) {
                    continue;
                }
                foreach ($monthlyFees->duePeriods($student, now()) as $period) {
                    if (! $monthlyFees->periodInvoiceExists($student->id, $period['key'])) {
                        $wouldCreate++;
                        $this->line($student->student_code.' — '.$period['label'].' — due '.$period['due']->toDateString());
                    }
                }
            }
            $this->info("Dry run: {$wouldCreate} invoice(s) would be created.");

            return self::SUCCESS;
        }

        $result = $monthlyFees->generateDueInvoices();
        $this->info("Created {$result['created']} monthly fee invoice(s) ({$result['skipped']} skipped).");

        return self::SUCCESS;
    }
}
