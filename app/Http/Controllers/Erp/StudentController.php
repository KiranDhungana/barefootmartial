<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Student;
use App\Models\StudentCertificate;
use App\Services\CloudinaryService;
use App\Services\QrCodeService;
use App\Services\MonthlyFeeService;
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
        private StudentRegistrationService $registration,
        private MonthlyFeeService $monthlyFees,
        private CloudinaryService $cloudinary
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
            $request->validate([
            'certificates' => 'nullable|array',
            'certificates.*.file' => 'nullable|file|mimes:jpeg,jpg,png,webp,gif,pdf|max:10240',
            'certificates.*.title' => 'nullable|string|max:255',
            'certificates.*.certificate_type' => 'nullable|in:general,belt',
            'certificates.*.issued_on' => 'nullable|date',
        ]);

        $data = $request->validated();
        $student = new Student($data);
        $student->registration_status = Student::REG_PENDING;
        if ($request->hasFile('photo')) {
            $uploaded = $this->cloudinary->uploadImage($request->file('photo'), 'students');
            $student->photo_path = $uploaded['url'];
            $student->photo_public_id = $uploaded['public_id'];
        }
        $student->save();

        $this->storeRegistrationCertificates($request, $student);

        return redirect()->route('erp.students.show', [$student, 'tab' => 'certificates'])
            ->with('success', 'Student saved. Certificates attached where provided — complete registration and mark as official when ready.');
    }

    private function storeRegistrationCertificates(Request $request, Student $student): void
    {
        foreach ($request->input('certificates', []) as $index => $row) {
            $file = $request->file("certificates.$index.file");
            if (! $file) {
                continue;
            }

            $uploaded = $this->cloudinary->uploadFile($file, 'certificates/'.$student->id);
            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                $title = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) ?: 'Certificate';
            }

            StudentCertificate::create([
                'student_id' => $student->id,
                'title' => $title,
                'certificate_type' => ($row['certificate_type'] ?? StudentCertificate::TYPE_GENERAL) === StudentCertificate::TYPE_BELT
                    ? StudentCertificate::TYPE_BELT
                    : StudentCertificate::TYPE_GENERAL,
                'file_url' => $uploaded['url'],
                'public_id' => $uploaded['public_id'],
                'resource_type' => $uploaded['resource_type'],
                'original_filename' => $file->getClientOriginalName(),
                'issued_on' => $row['issued_on'] ?? null,
                'uploaded_by' => auth()->id(),
            ]);
        }
    }

    public function show(Student $student): View
    {
        BranchScope::assertStudentAccess($student);
        $student->load([
            'branch',
            'registeredByUser',
            'beltPromotions.promotedByUser',
            'certificates.uploader',
            'eventCertificates.event',
        ]);

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

        $user = auth()->user();
        $eventsForCertificates = Event::query()
            ->when($user?->isBranchScoped(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->when($student->branch_id, fn ($q) => $q->where(function ($qq) use ($student) {
                $qq->whereNull('branch_id')->orWhere('branch_id', $student->branch_id);
            }))
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->get(['id', 'title', 'event_date', 'branch_id']);

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
            'activeTab',
            'eventsForCertificates'
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
            $this->deleteStudentPhoto($student);
            $uploaded = $this->cloudinary->uploadImage($request->file('photo'), 'students');
            $student->photo_path = $uploaded['url'];
            $student->photo_public_id = $uploaded['public_id'];
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
            // Do not auto-create monthly fees unless explicitly enabled.
            if (config('academy.monthly_fee_auto_generate', false)) {
                $this->monthlyFees->generateDueInvoices(null, $student->fresh());
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        $msg = 'Student is now officially registered ('.$student->fresh()->student_code.').';
        if (config('academy.monthly_fee_auto_generate', false)) {
            $msg .= ' Monthly fees will auto-bill from the join date.';
        } else {
            $msg .= ' Create monthly invoices manually in ERP → Invoices when due.';
        }

        return redirect()->route('erp.students.show', $student)
            ->with('success', $msg);
    }

    public function destroy(Student $student): RedirectResponse
    {
        BranchScope::assertStudentAccess($student);
        $this->deleteStudentPhoto($student);
        $student->delete();

        return redirect()->route('erp.students.index')->with('success', 'Student removed.');
    }

    private function deleteStudentPhoto(Student $student): void
    {
        if ($student->photo_public_id) {
            try {
                $this->cloudinary->delete($student->photo_public_id, 'image');
            } catch (\Throwable $e) {
                // ignore Cloudinary delete failures
            }
            $student->photo_public_id = null;
        } elseif ($student->photo_path
            && ! str_starts_with($student->photo_path, 'http://')
            && ! str_starts_with($student->photo_path, 'https://')) {
            Storage::disk('public')->delete($student->photo_path);
        }

        $student->photo_path = null;
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
