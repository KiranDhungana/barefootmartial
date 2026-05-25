<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Student;
use App\Support\BranchScope;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceAnalyticsService
{
    /**
     * @return Collection<int, object{student: Student, present_days: int, late_days: int, absent_days: int, percent: float}>
     */
    public function monthlySummary(?int $year = null, ?int $month = null, ?int $branchId = null): Collection
    {
        $year = $year ?? (int) now()->format('Y');
        $month = $month ?? (int) now()->format('m');
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        $q = BranchScope::students()
            ->where('registration_status', Student::REG_OFFICIAL);
        if ($branchId) {
            $q->where('branch_id', $branchId);
        }

        return $q->orderBy('name')->get()->map(function (Student $student) use ($year, $month, $daysInMonth) {
            $counts = Attendance::query()
                ->where('student_id', $student->id)
                ->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $month)
                ->selectRaw("
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days
                ")
                ->first();

            $present = (int) ($counts->present_days ?? 0);
            $late = (int) ($counts->late_days ?? 0);
            $absent = (int) ($counts->absent_days ?? 0);
            $effective = $present + $late;
            $percent = $daysInMonth > 0 ? round(($effective / $daysInMonth) * 100, 1) : 0;

            return (object) [
                'student' => $student,
                'present_days' => $present,
                'late_days' => $late,
                'absent_days' => $absent,
                'percent' => $percent,
            ];
        });
    }

    /**
     * @return Collection<int, Student>
     */
    public function inactiveStudents(?int $branchId = null): Collection
    {
        $days = (int) config('academy.attendance_inactive_days', 14);
        $cutoff = now()->subDays($days)->toDateString();

        $q = BranchScope::students()
            ->where('registration_status', Student::REG_OFFICIAL)
            ->where('status', Student::STATUS_ACTIVE);

        if ($branchId) {
            $q->where('branch_id', $branchId);
        }

        return $q->get()->filter(function (Student $student) use ($cutoff) {
            $last = Attendance::query()
                ->where('student_id', $student->id)
                ->whereIn('status', ['present', 'late'])
                ->max('attendance_date');

            return ! $last || $last < $cutoff;
        })->values();
    }

    /**
     * @return Collection<int, object>
     */
    public function lowAttendanceStudents(?int $year = null, ?int $month = null, ?int $branchId = null): Collection
    {
        $threshold = (int) config('academy.attendance_low_percent', 40);

        return $this->monthlySummary($year, $month, $branchId)
            ->filter(fn ($row) => $row->percent < $threshold && $row->percent > 0);
    }
}
