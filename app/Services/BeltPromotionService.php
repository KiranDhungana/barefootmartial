<?php

namespace App\Services;

use App\Models\BeltPromotion;
use App\Models\Student;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Str;

class BeltPromotionService
{
    public function nextBelt(?string $current): ?string
    {
        $ranks = config('academy.belt_ranks', []);
        if ($current === null || $current === '') {
            return $ranks[0] ?? null;
        }
        $idx = $this->beltIndex($current);
        if ($idx === null || $idx >= count($ranks) - 1) {
            return null;
        }

        return $ranks[$idx + 1];
    }

    public function beltIndex(string $belt): ?int
    {
        $ranks = config('academy.belt_ranks', []);
        $normalized = Str::lower(trim($belt));
        foreach ($ranks as $i => $rank) {
            if (Str::lower($rank) === $normalized) {
                return $i;
            }
        }

        return null;
    }

    public function isEligible(Student $student): bool
    {
        $next = $this->nextBelt($student->belt_rank);
        if (! $next || ! $student->isOfficial()) {
            return false;
        }

        $months = (int) config('academy.belt_months_between_exams', 3);
        $lastPromo = $student->beltPromotions()->orderByDesc('promoted_at')->first();
        $since = $lastPromo?->promoted_at ?? $student->join_date ?? $student->created_at;

        if (! $since) {
            return true;
        }

        return $since->diffInMonths(now()) >= $months;
    }

    public function eligibilityReason(Student $student): string
    {
        if (! $student->isOfficial()) {
            return 'Not officially registered';
        }
        if (! $this->nextBelt($student->belt_rank)) {
            return 'Already at highest configured rank';
        }
        if ($this->isEligible($student)) {
            return 'Eligible for '.$this->nextBelt($student->belt_rank);
        }

        $months = (int) config('academy.belt_months_between_exams', 3);

        return 'Needs '.$months.'+ months since last promotion/joining';
    }

    public function promote(Student $student, array $data, ?User $by = null): BeltPromotion
    {
        $toBelt = $data['to_belt'] ?? $this->nextBelt($student->belt_rank);
        if (! $toBelt) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'to_belt' => 'No next belt defined.',
            ]);
        }

        $fromBelt = $student->belt_rank;
        $promotion = BeltPromotion::create([
            'student_id' => $student->id,
            'from_belt' => $fromBelt,
            'to_belt' => $toBelt,
            'promoted_at' => $data['promoted_at'] ?? now()->toDateString(),
            'exam_passed' => $data['exam_passed'] ?? true,
            'certificate_number' => $data['certificate_number'] ?? BeltPromotion::generateCertificateNumber(),
            'notes' => $data['notes'] ?? null,
            'promoted_by' => $by?->id ?? auth()->id(),
        ]);

        $student->update(['belt_rank' => $toBelt]);
        AuditLogger::log('belt.promoted', $promotion, ['belt_rank' => $fromBelt], ['belt_rank' => $toBelt]);

        return $promotion;
    }
}
