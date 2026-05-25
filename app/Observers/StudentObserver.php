<?php

namespace App\Observers;

use App\Models\Student;
use App\Services\AuditLogger;

class StudentObserver
{
    public function created(Student $student): void
    {
        AuditLogger::log('student.created', $student, null, $student->getAttributes());
    }

    public function updated(Student $student): void
    {
        $dirty = $student->getChanges();
        if ($dirty === []) {
            return;
        }
        $old = [];
        foreach (array_keys($dirty) as $key) {
            $old[$key] = $student->getOriginal($key);
        }
        AuditLogger::log('student.updated', $student, $old, $dirty);
    }

    public function deleted(Student $student): void
    {
        AuditLogger::log('student.deleted', $student, $student->getAttributes(), null);
    }
}
