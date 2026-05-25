<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\file;
use App\Models\Invoice;
use App\Models\Student;
use App\Services\AttendanceAnalyticsService;
use Illuminate\View\View;

class ParentPortalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'parent']);
    }

    public function dashboard(): View
    {
        $children = auth()->user()->children()->with('branch')->get();
        $studentId = request()->integer('student_id') ?: $children->first()?->id;
        $student = $children->firstWhere('id', $studentId);

        $attendanceSummary = null;
        $invoices = collect();
        $notices = file::query()->orderByDesc('id')->limit(5)->get();

        if ($student) {
            $attendanceSummary = app(AttendanceAnalyticsService::class)
                ->monthlySummary((int) now()->format('Y'), (int) now()->format('m'), $student->branch_id)
                ->firstWhere(fn ($r) => $r->student->id === $student->id);

            $invoices = Invoice::query()
                ->where('student_id', $student->id)
                ->latest()
                ->limit(10)
                ->get();
        }

        return view('parent.dashboard', compact('children', 'student', 'attendanceSummary', 'invoices', 'notices'));
    }
}
