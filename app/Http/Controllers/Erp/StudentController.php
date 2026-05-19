<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Student;
use App\Services\QrCodeService;
use App\Support\WhatsApp;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $q = Student::query()->with('branch')->orderByDesc('id');

        if ($request->filled('search')) {
            $s = '%'.$request->string('search').'%';
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', $s)
                    ->orWhere('student_code', 'like', $s)
                    ->orWhere('phone', 'like', $s);
            });
        }

        $students = $q->paginate(15)->withQueryString();

        return view('erp.students.index', compact('students'));
    }

    public function create(): View
    {
        $branches = Branch::query()->orderBy('name')->get();

        return view('erp.students.create', compact('branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'join_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:4096',
        ]);

        $student = new Student($data);
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('students', 'public');
            $student->photo_path = $path;
        }
        $student->save();

        return redirect()->route('erp.students.show', $student)->with('success', 'Student created.');
    }

    public function show(Student $student): View
    {
        $student->load('branch');
        $canViewFinance = auth()->user()?->isAdmin() ?? false;
        if ($canViewFinance) {
            $student->load(['invoices' => fn ($q) => $q->latest()->limit(10)]);
        }

        $feeReminderMessage = $this->feeReminderMessage($student, $canViewFinance);
        $whatsappUrl = WhatsApp::waMeUrl($student->phone, $feeReminderMessage);

        return view('erp.students.show', compact('student', 'feeReminderMessage', 'whatsappUrl', 'canViewFinance'));
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
        $branches = Branch::query()->orderBy('name')->get();

        return view('erp.students.edit', compact('student', 'branches'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'join_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:4096',
        ]);

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

    public function destroy(Student $student): RedirectResponse
    {
        if ($student->photo_path) {
            Storage::disk('public')->delete($student->photo_path);
        }
        $student->delete();

        return redirect()->route('erp.students.index')->with('success', 'Student removed.');
    }

    public function idCardPdf(Student $student, QrCodeService $qr): \Symfony\Component\HttpFoundation\Response
    {
        $student->load('branch');
        $verifyUrl = $student->profileUrl();
        $svg = $qr->svg($verifyUrl, 140);

        $pdf = Pdf::loadView('erp.pdf.id-card', [
            'student' => $student,
            'qrSvg' => $svg,
        ])->setPaper([0, 0, 270, 426], 'portrait');

        return $pdf->download('id-card-'.$student->student_code.'.pdf');
    }
}
