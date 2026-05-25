<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\Branch;
use App\Models\Attendance;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Student;
use App\Services\QrCodeService;
use App\Services\StudentRegistrationService;
use App\Support\BranchScope;
use App\Support\WhatsApp;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(
        private StudentRegistrationService $registration
    ) {
    }

    public function index(Request $request): View
    {
        $q = BranchScope::students()->with('branch')->orderByDesc('id');

        if ($request->filled('search')) {
            $s = '%'.$request->string('search').'%';
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', $s)
                    ->orWhere('student_code', 'like', $s)
                    ->orWhere('phone', 'like', $s);
            });
        }

        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }

        if ($request->filled('registration_status')) {
            $q->where('registration_status', $request->string('registration_status'));
        }

        $students = $q->paginate(15)->withQueryString();

        return view('erp.students.index', compact('students'));
    }

    public function create(): View
    {
        $branches = $this->branchesForForm();
        $statuses = config('academy.student_statuses', []);

        return view('erp.students.create', compact('branches', 'statuses'));
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $student = new Student($data);
        $student->registration_status = Student::REG_PENDING;
        if ($request->hasFile('photo')) {
            $student->photo_path = $request->file('photo')->store('students', 'public');
        }
        $student->save();

        return redirect()->route('erp.students.show', $student)
            ->with('success', 'Student saved. Complete registration and mark as official when ready.');
    }

    public function show(Student $student): View
    {
        BranchScope::assertStudentAccess($student);
        $student->load(['branch', 'registeredByUser', 'beltPromotions.promotedByUser']);

        $canViewFinance = auth()->user()?->canManageFinance() ?? false;
        $invoices = collect();
        $uniformLines = collect();
        if ($canViewFinance) {
            $invoices = $student->invoices()->with('payments')->latest()->limit(20)->get();
            $uniformLines = InvoiceLineItem::query()
                ->whereHas('invoice', fn ($q) => $q->where('student_id', $student->id))
                ->where(function ($q) {
                    $q->where('fee_type', 'uniform')->orWhereNotNull('inventory_item_id');
                })
                ->with('invoice')
                ->latest('id')
                ->limit(15)
                ->get();
        }

        $attendanceRecords = Attendance::query()
            ->where('student_id', $student->id)
            ->orderByDesc('attendance_date')
            ->limit(40)
            ->get();

        $monthPct = app(\App\Services\AttendanceAnalyticsService::class)
            ->monthlySummary((int) now()->format('Y'), (int) now()->format('m'), $student->branch_id)
            ->first(fn ($r) => $r->student->id === $student->id);

        $activeTab = request('tab', 'profile');

        $beltService = app(\App\Services\BeltPromotionService::class);
        $nextBelt = $beltService->nextBelt($student->belt_rank);
        $beltEligible = $beltService->isEligible($student);

        $missingOfficial = $this->registration->missingOfficialFields($student);
        $canMarkOfficial = auth()->user()?->canMarkOfficialRegistration()
            && $student->isPendingRegistration()
            && count($missingOfficial) === 0;

        $feeReminderMessage = $this->feeReminderMessage($student, $canViewFinance);
        $whatsappUrl = WhatsApp::waMeUrl($student->phone ?: $student->parent_contact, $feeReminderMessage);

        return view('erp.students.show', compact(
            'student',
            'feeReminderMessage',
            'whatsappUrl',
            'canViewFinance',
            'missingOfficial',
            'canMarkOfficial',
            'nextBelt',
            'beltEligible',
            'invoices',
            'uniformLines',
            'attendanceRecords',
            'monthPct',
            'activeTab'
        ));
    }

    private function feeReminderMessage(Student $student, bool $canViewFinance): string
    {
        $pending = $canViewFinance
            ? $student->invoices()->where('status', 'pending')->orderBy('due_date')->first()
            : null;
        $lines = [
            'Hello '.$student->name.',',
            'This is a reminder from Barefoot Martial Arts regarding your academy fees.',
        ];
        if ($pending) {
            $lines[] = 'Invoice '.$pending->invoice_number.' — amount '.$pending->amount.' — due '.optional($pending->due_date)->format('M j, Y').'.';
        } else {
            $lines[] = 'Please contact us if you have questions about your balance.';
        }

        return implode("\n", $lines);
    }

    public function edit(Student $student): View
    {
        BranchScope::assertStudentAccess($student);
        $branches = $this->branchesForForm();
        $statuses = config('academy.student_statuses', []);

        return view('erp.students.edit', compact('student', 'branches', 'statuses'));
    }

    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        BranchScope::assertStudentAccess($student);
        $data = $request->validated();
        $student->fill($data);
        if ($request->hasFile('photo')) {
            if ($student->photo_path) {
                Storage::disk('public')->delete($student->photo_path);
            }
            $student->photo_path = $request->file('photo')->store('students', 'public');
        }
        $student->save();

        return redirect()->route('erp.students.show', $student)->with('success', 'Student updated.');
    }

    public function markOfficial(Student $student): RedirectResponse
    {
        BranchScope::assertStudentAccess($student);
        if (! auth()->user()?->canMarkOfficialRegistration()) {
            abort(403);
        }

        try {
            $this->registration->markOfficial($student, auth()->user());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('erp.students.show', $student)
            ->with('success', 'Student is now officially registered ('.$student->student_code.').');
    }

    public function destroy(Student $student): RedirectResponse
    {
        BranchScope::assertStudentAccess($student);
        if ($student->photo_path) {
            Storage::disk('public')->delete($student->photo_path);
        }
        $student->delete();

        return redirect()->route('erp.students.index')->with('success', 'Student removed.');
    }

    public function idCardPdf(Student $student, QrCodeService $qr): \Symfony\Component\HttpFoundation\Response
    {
        BranchScope::assertStudentAccess($student);
        if (! $student->isOfficial()) {
            return redirect()->route('erp.students.show', $student)
                ->with('error', 'ID cards are only issued for officially registered students.');
        }

        $student->load('branch');
        $verifyUrl = $student->verifyUrl();
        $svg = $qr->svg($verifyUrl, 140);

        $pdf = Pdf::loadView('erp.pdf.id-card', [
            'student' => $student,
            'qrSvg' => $svg,
        ])->setPaper([0, 0, 270, 426], 'portrait');

        return $pdf->download('id-card-'.$student->student_code.'.pdf');
    }

    private function branchesForForm()
    {
        $user = auth()->user();
        if ($user?->isBranchScoped()) {
            return Branch::query()->where('id', $user->branch_id)->orderBy('name')->get();
        }

        return Branch::query()->orderBy('name')->get();
    }
}
