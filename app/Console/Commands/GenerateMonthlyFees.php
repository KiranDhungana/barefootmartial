<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Services\MonthlyFeeService;
use Illuminate\Console\Command;

class GenerateMonthlyFees extends Command
{
    protected $signature = 'academy:generate-monthly-fees
                            {--dry-run : Show how many invoices would be created}
                            {--force : Run even when ACADEMY_MONTHLY_FEE_AUTO_GENERATE is false}';

    protected $description = 'Create monthly fee invoices (disabled unless ACADEMY_MONTHLY_FEE_AUTO_GENERATE=true or --force)';

    public function handle(MonthlyFeeService $monthlyFees): int
    {
        $enabled = (bool) config('academy.monthly_fee_auto_generate', false);
        if (! $enabled && ! $this->option('force') && ! $this->option('dry-run')) {
            $this->warn('Auto monthly fees are DISABLED (ACADEMY_MONTHLY_FEE_AUTO_GENERATE=false).');
            $this->line('Create invoices manually in ERP, or run with --force for a one-time generate.');

            return self::SUCCESS;
        }

        $backfill = (bool) config('academy.monthly_fee_backfill', false);

        if ($this->option('dry-run')) {
            $students = Student::query()
                ->where('registration_status', Student::REG_OFFICIAL)
                ->whereIn('status', [
                    Student::STATUS_ACTIVE,
                    Student::STATUS_PENDING_FEE,
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
            $mode = $backfill ? 'backfill' : 'current month only';
            $auto = $enabled ? 'enabled' : 'disabled';
            $this->info("Dry run (auto={$auto}, {$mode}): {$wouldCreate} invoice(s) would be created.");

            return self::SUCCESS;
        }

        $result = $monthlyFees->generateDueInvoices();
        $this->info("Created {$result['created']} monthly fee invoice(s) ({$result['skipped']} skipped).");

        return self::SUCCESS;
    }
}
