<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Services\AttendanceAnalyticsService;
use App\Services\StudentRegistrationService;
use App\Support\BranchScope;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        private StudentRegistrationService $registration,
        private AttendanceAnalyticsService $analytics
    ) {
    }

    public function index(Request $request): View
    {
        $date = $request->date ? Carbon::parse($request->date) : now()->startOfDay();
        $user = auth()->user();
        $branchId = $user?->isBranchScoped() ? $user->branch_id : null;

        $students = BranchScope::students()
            ->where('registration_status', Student::REG_OFFICIAL)
            ->where('status', Student::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        $records = Attendance::query()
            ->whereDate('attendance_date', $date->toDateString())
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $month = $request->input('summary_month', now()->format('Y-m'));
        $y = (int) substr($month, 0, 4);
        $m = (int) substr($month, 5, 2);
        $daysInMonth = Carbon::createFromDate($y, $m, 1)->daysInMonth;

        $summaryRows = $this->analytics->monthlySummary($y, $m, $branchId);
        $inactiveStudents = $this->analytics->inactiveStudents($branchId);
        $lowAttendance = $this->analytics->lowAttendanceStudents($y, $m, $branchId);

        return view('erp.attendance.index', [
            'date' => $date,
            'students' => $students,
            'records' => $records,
            'summaryRows' => $summaryRows,
            'summaryMonth' => $month,
            'daysInMonth' => $daysInMonth,
            'inactiveStudents' => $inactiveStudents,
            'lowAttendance' => $lowAttendance,
        ]);
    }

    public function saveDay(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'attendance_date' => 'required|date',
            'status' => 'required|in:present,absent,late',
            'student_id' => 'required|exists:students,id',
        ]);

        $student = Student::query()->findOrFail($data['student_id']);
        BranchScope::assertStudentAccess($student);

        try {
            $this->registration->assertOfficialForAction($student, 'recording attendance');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        Attendance::query()->updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'attendance_date' => $data['attendance_date'],
            ],
            [
                'status' => $data['status'],
                'source' => 'manual',
            ]
        );

        return back()->with('success', 'Attendance saved.');
    }

    public function scan(string $token): RedirectResponse
    {
        $student = Student::query()->where('qr_token', $token)->firstOrFail();

        if (auth()->check() && auth()->user()->canAccessErp()) {
            BranchScope::assertStudentAccess($student);
        }

        if (! $student->isOfficial()) {
            return redirect()->route('verify.student', $token)
                ->with('error', 'Student is not officially registered.');
        }

        if ($student->status === Student::STATUS_SUSPENDED || $student->status === Student::STATUS_INACTIVE) {
            return redirect()->route('verify.student', $token)
                ->with('error', $student->name.' is '.$student->statusLabel().'.');
        }

        if (! auth()->check() || ! auth()->user()->canAccessErp()) {
            return redirect()->route('verify.student', $token);
        }

        $today = now()->toDateString();

        Attendance::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'attendance_date' => $today,
            ],
            [
                'status' => 'present',
                'source' => 'qr',
            ]
        );

        return redirect()
            ->route('erp.attendance.index', ['date' => $today])
            ->with('success', $student->name.' marked present via QR.');
    }

    public function bulk(Request $request): View
    {
        $date = $request->date ? Carbon::parse($request->date) : now()->startOfDay();

        $students = BranchScope::students()
            ->where('registration_status', Student::REG_OFFICIAL)
            ->where('status', Student::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        $records = Attendance::query()
            ->whereDate('attendance_date', $date->toDateString())
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        return view('erp.attendance.bulk', compact('date', 'students', 'records'));
    }

    public function bulkSave(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'attendance_date' => 'required|date',
            'statuses' => 'required|array',
            'statuses.*' => 'nullable|in:present,absent,late',
        ]);

        $date = $data['attendance_date'];
        $saved = 0;

        foreach ($data['statuses'] as $studentId => $status) {
            if (! $status) {
                continue;
            }
            $student = Student::query()->find($studentId);
            if (! $student) {
                continue;
            }
            BranchScope::assertStudentAccess($student);
            if (! $student->isOfficial()) {
                continue;
            }

            Attendance::query()->updateOrCreate(
                ['student_id' => $student->id, 'attendance_date' => $date],
                ['status' => $status, 'source' => 'bulk']
            );
            $saved++;
        }

        return redirect()
            ->route('erp.attendance.bulk', ['date' => $date])
            ->with('success', "Saved attendance for {$saved} students.");
    }
}
