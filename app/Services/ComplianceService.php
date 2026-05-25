<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Student;
use Illuminate\Support\Collection;

class ComplianceService
{
    /**
     * @return Collection<int, array{branch: Branch, registration: float, uniform: float, reporting: float, overall: float, details: array}>
     */
    public function branchScores(): Collection
    {
        return Branch::query()->orderBy('name')->get()->map(function (Branch $branch) {
            $total = Student::query()->where('branch_id', $branch->id)->count();
            $official = Student::query()
                ->where('branch_id', $branch->id)
                ->where('registration_status', Student::REG_OFFICIAL)
                ->count();
            $pending = Student::query()
                ->where('branch_id', $branch->id)
                ->where('registration_status', Student::REG_PENDING)
                ->count();

            $registrationScore = $total > 0 ? round(($official / $total) * 100, 1) : 100;

            $withUniform = Student::query()
                ->where('branch_id', $branch->id)
                ->where('registration_status', Student::REG_OFFICIAL)
                ->where(function ($q) {
                    $q->whereNotNull('uniform_status')
                        ->where('uniform_status', '!=', '')
                        ->where('uniform_status', 'not like', '%pending%');
                })
                ->count();
            $uniformScore = $official > 0 ? round(($withUniform / $official) * 100, 1) : 0;

            $monthStart = now()->startOfMonth();
            $hasInvoices = Invoice::query()
                ->where('branch_id', $branch->id)
                ->where('created_at', '>=', $monthStart)
                ->exists();
            $hasStudents = $official > 0;
            $reportingScore = ($hasStudents && $hasInvoices) || ! $hasStudents ? 100 : 40;

            $overall = round(
                $registrationScore * 0.45 + $uniformScore * 0.3 + $reportingScore * 0.25,
                1
            );

            return [
                'branch' => $branch,
                'registration' => $registrationScore,
                'uniform' => $uniformScore,
                'reporting' => $reportingScore,
                'overall' => $overall,
                'details' => [
                    'total_students' => $total,
                    'official' => $official,
                    'pending_registration' => $pending,
                    'uniform_documented' => $withUniform,
                ],
            ];
        });
    }
}
