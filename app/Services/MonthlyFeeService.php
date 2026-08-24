<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MonthlyFeeService
{
    public function __construct(
        private InvoiceBillingService $billing
    ) {
    }

    /**
     * Create any missing monthly fee invoices (from join/admission date through today).
     *
     * @return array{created: int, skipped: int}
     */
    public function generateDueInvoices(?Carbon $asOf = null): array
    {
        $asOf = ($asOf ?? now())->copy()->startOfDay();
        $created = 0;
        $skipped = 0;

        $students = Student::query()
            ->where('registration_status', Student::REG_OFFICIAL)
            ->whereIn('status', [Student::STATUS_ACTIVE, Student::STATUS_PENDING_FEE])
            ->whereNotNull('join_date')
            ->orderBy('id')
            ->get();

        foreach ($students as $student) {
            if ($student->hasFullScholarship()) {
                $skipped++;

                continue;
            }

            $amount = $this->monthlyAmountFor($student);
            if ($amount <= 0) {
                $skipped++;

                continue;
            }

            foreach ($this->duePeriods($student, $asOf) as $period) {
                if ($this->periodInvoiceExists($student->id, $period['key'])) {
                    continue;
                }

                try {
                    $this->billing->createInvoice(
                        $student,
                        [[
                            'fee_type' => 'monthly',
                            'selected' => true,
                            'unit_price' => $amount,
                            'quantity' => 1,
                            'description' => 'Monthly fee — '.$period['label'],
                        ]],
                        [],
                        [
                            'due_date' => $period['due']->toDateString(),
                            'notes' => 'Auto-generated monthly fee from admission date '.$student->join_date->format('Y-m-d'),
                            'discount_percent' => $student->discount_percent,
                            'billing_period' => $period['key'],
                        ]
                    );
                    $created++;
                } catch (\Throwable $e) {
                    Log::warning('Monthly fee generation failed', [
                        'student_id' => $student->id,
                        'period' => $period['key'],
                        'error' => $e->getMessage(),
                    ]);
                    $skipped++;
                }
            }
        }

        return compact('created', 'skipped');
    }

    public function monthlyAmountFor(Student $student): float
    {
        $own = (float) ($student->monthly_fee ?? 0);
        if ($own > 0) {
            return $own;
        }

        return (float) config('academy.default_monthly_fee', config('academy.fee_types.monthly.default_price', 0));
    }

    /**
     * @return list<array{key: string, label: string, due: Carbon}>
     */
    public function duePeriods(Student $student, Carbon $asOf): array
    {
        $join = $student->join_date?->copy()->startOfDay();
        if (! $join) {
            return [];
        }

        $includeJoinMonth = (bool) config('academy.monthly_fee_include_join_month', true);
        $cursor = $includeJoinMonth ? $join->copy() : $this->addCalendarMonth($join, 1);
        $periods = [];

        while ($cursor->lte($asOf)) {
            $periods[] = [
                'key' => 'monthly:'.$cursor->format('Y-m'),
                'label' => $cursor->format('F Y'),
                'due' => $cursor->copy(),
            ];
            $cursor = $this->addCalendarMonth($cursor, 1);
        }

        return $periods;
    }

    public function periodInvoiceExists(int $studentId, string $billingPeriod): bool
    {
        return Invoice::query()
            ->where('student_id', $studentId)
            ->where('billing_period', $billingPeriod)
            ->exists();
    }

    /**
     * Advance by N calendar months, clamping the day to the last day of the target month
     * (e.g. Jan 31 → Feb 28/29).
     */
    private function addCalendarMonth(Carbon $date, int $months): Carbon
    {
        $day = (int) $date->day;
        $target = $date->copy()->startOfMonth()->addMonthsNoOverflow($months);
        $lastDay = (int) $target->copy()->endOfMonth()->day;
        $target->day(min($day, $lastDay));

        return $target->startOfDay();
    }
}
