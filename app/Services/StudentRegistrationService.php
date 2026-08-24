<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentRegistrationService
{
    public function missingOfficialFields(Student $student): array
    {
        $missing = [];
        foreach (config('academy.official_required_fields', []) as $field) {
            $value = $student->{$field};
            if ($value === null || $value === '') {
                $missing[] = $field;
            }
        }
        if (! $student->phone && ! $student->parent_contact) {
            $missing[] = 'phone_or_parent_contact';
        }

        return array_unique($missing);
    }

    public function canMarkOfficial(Student $student): bool
    {
        return count($this->missingOfficialFields($student)) === 0;
    }

    public function markOfficial(Student $student, ?User $by = null): Student
    {
        if ($student->isOfficial()) {
            return $student;
        }

        if (! $this->canMarkOfficial($student)) {
            throw ValidationException::withMessages([
                'registration' => 'Complete all required fields before marking this student as officially registered.',
            ]);
        }

        return DB::transaction(function () use ($student, $by) {
            $student = Student::query()->lockForUpdate()->findOrFail($student->id);

            if ($student->isOfficial()) {
                return $student;
            }

            if (! $this->canMarkOfficial($student)) {
                throw ValidationException::withMessages([
                    'registration' => 'Complete all required fields before marking this student as officially registered.',
                ]);
            }

            $old = $student->only(['registration_status', 'student_code', 'registered_at']);

            if (str_starts_with($student->student_code, 'PRE-')) {
                $student->student_code = Student::generateMembershipCode(true, $student->id);
            }

            $student->registration_status = Student::REG_OFFICIAL;
            $student->registered_at = now();
            $student->registered_by = $by?->id ?? auth()->id();
            if ($student->status === Student::STATUS_INACTIVE) {
                $student->status = Student::STATUS_ACTIVE;
            }
            $student->save();

            AuditLogger::log('student.marked_official', $student, $old, $student->only([
                'registration_status', 'student_code', 'registered_at', 'registered_by',
            ]));

            return $student->fresh();
        });
    }

    public function assertOfficialForAction(Student $student, string $action): void
    {
        if ($student->isOfficial()) {
            return;
        }

        throw ValidationException::withMessages([
            'student' => "Student must be officially registered in the system before {$action}. Complete registration first.",
        ]);
    }
}
