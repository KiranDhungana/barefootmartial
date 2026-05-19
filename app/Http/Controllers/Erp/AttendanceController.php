<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->date ? Carbon::parse($request->date) : now()->startOfDay();

        $students = Student::query()->orderBy('name')->get();

        $records = Attendance::query()
            ->whereDate('attendance_date', $date->toDateString())
            ->get()
            ->keyBy('student_id');

        $month = $request->input('summary_month', now()->format('Y-m'));
        $y = (int) substr($month, 0, 4);
        $m = (int) substr($month, 5, 2);

        $summaryRows = Student::query()
            ->withCount(['attendances as present_days' => function ($q) use ($y, $m) {
                $q->whereYear('attendance_date', $y)
                    ->whereMonth('attendance_date', $m)
                    ->where('status', 'present');
            }])
            ->orderBy('name')
            ->get();

        $daysInMonth = Carbon::createFromDate($y, $m, 1)->daysInMonth;

        return view('erp.attendance.index', [
            'date' => $date,
            'students' => $students,
            'records' => $records,
            'summaryRows' => $summaryRows,
            'summaryMonth' => $month,
            'daysInMonth' => $daysInMonth,
        ]);
    }

    public function saveDay(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'attendance_date' => 'required|date',
            'status' => 'required|in:present,absent,late',
            'student_id' => 'required|exists:students,id',
        ]);

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
}
