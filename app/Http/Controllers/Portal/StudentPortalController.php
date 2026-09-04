<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\BeltPromotion;
use App\Models\file;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentCertificate;
use App\Services\AttendanceAnalyticsService;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class StudentPortalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'student_portal']);
    }

    public function dashboard(): View
    {
        ['students' => $students, 'student' => $student] = $this->resolveStudentContext();

        $attendanceSummary = null;
        $feeSummary = [
            'total_billed' => 0,
            'total_paid' => 0,
            'total_remaining' => 0,
        ];
        $certificateCount = 0;
        $openInvoices = 0;
        $recentInvoices = collect();

        if ($student) {
            $attendanceSummary = $this->attendanceFor($student);
            $invoices = Invoice::query()->where('student_id', $student->id)->latest()->get();
            $recentInvoices = $invoices->take(5);
            $feeSummary = [
                'total_billed' => round($invoices->sum(fn (Invoice $inv) => $inv->totalAmount()), 2),
                'total_paid' => round($invoices->sum(fn (Invoice $inv) => (float) $inv->amount_paid), 2),
                'total_remaining' => round($invoices->sum(fn (Invoice $inv) => $inv->balanceDue()), 2),
            ];
            $openInvoices = $invoices->filter(fn (Invoice $inv) => $inv->balanceDue() > 0)->count();
            $certificateCount = $student->beltPromotions()->count()
                + $student->eventCertificates()->count()
                + $student->certificates()->count();
        }

        $recentNotices = file::query()->orderByDesc('id')->limit(3)->get();

        return view('portal.dashboard', compact(
            'students',
            'student',
            'attendanceSummary',
            'feeSummary',
            'certificateCount',
            'openInvoices',
            'recentInvoices',
            'recentNotices'
        ));
    }

    public function profile(): View
    {
        ['students' => $students, 'student' => $student] = $this->resolveStudentContext();

        return view('portal.profile', compact('students', 'student'));
    }

    public function attendance(): View
    {
        ['students' => $students, 'student' => $student] = $this->resolveStudentContext();
        $attendanceSummary = $student ? $this->attendanceFor($student) : null;

        return view('portal.attendance', compact('students', 'student', 'attendanceSummary'));
    }

    public function fees(): View
    {
        ['students' => $students, 'student' => $student] = $this->resolveStudentContext();

        $invoices = collect();
        $payments = collect();
        $feeSummary = [
            'total_billed' => 0,
            'total_paid' => 0,
            'total_remaining' => 0,
        ];

        if ($student) {
            $invoices = Invoice::query()
                ->where('student_id', $student->id)
                ->with(['payments' => fn ($q) => $q->orderByDesc('paid_at'), 'lineItems'])
                ->latest()
                ->get();

            $payments = Payment::query()
                ->whereHas('invoice', fn ($q) => $q->where('student_id', $student->id))
                ->with('invoice')
                ->orderByDesc('paid_at')
                ->get();

            $feeSummary = [
                'total_billed' => round($invoices->sum(fn (Invoice $inv) => $inv->totalAmount()), 2),
                'total_paid' => round($invoices->sum(fn (Invoice $inv) => (float) $inv->amount_paid), 2),
                'total_remaining' => round($invoices->sum(fn (Invoice $inv) => $inv->balanceDue()), 2),
            ];
        }

        return view('portal.fees', compact('students', 'student', 'invoices', 'payments', 'feeSummary'));
    }

    public function invoiceShow(Request $request, Invoice $invoice): View
    {
        $this->assertInvoiceAccess($invoice);
        $invoice->load(['student.branch', 'lineItems', 'payments']);

        ['students' => $students, 'student' => $student] = $this->resolveStudentContext(
            $invoice->student_id
        );

        return view('portal.invoice-show', compact('students', 'student', 'invoice'));
    }

    public function invoicePdf(Invoice $invoice): Response
    {
        $this->assertInvoiceAccess($invoice);
        $invoice->load(['student.branch', 'lineItems']);

        return Pdf::loadView('erp.pdf.invoice', ['invoice' => $invoice])
            ->download($invoice->invoice_number.'.pdf');
    }

    public function receiptPdf(Invoice $invoice, Payment $payment, QrCodeService $qr): Response
    {
        $this->assertInvoiceAccess($invoice);
        abort_unless((int) $payment->invoice_id === (int) $invoice->id, 404);

        $invoice->load(['student.branch', 'lineItems', 'branch', 'payments']);
        $qrSvg = $qr->svg($invoice->student->verifyUrl(), 90);

        return Pdf::loadView('erp.pdf.receipt', [
            'invoice' => $invoice,
            'payment' => $payment,
            'qrSvg' => $qrSvg,
        ])->setPaper('a4')->download($payment->receipt_number.'.pdf');
    }

    public function certificates(): View
    {
        ['students' => $students, 'student' => $student] = $this->resolveStudentContext();

        $beltPromotions = collect();
        $beltCertificates = collect();
        $eventCertificates = collect();
        $studentCertificates = collect();

        if ($student) {
            $beltPromotions = $student->beltPromotions()->orderByDesc('promoted_at')->get();
            $allUploaded = $student->certificates()->get();
            $beltCertificates = $allUploaded->where('certificate_type', StudentCertificate::TYPE_BELT)->values();
            $studentCertificates = $allUploaded->where('certificate_type', '!=', StudentCertificate::TYPE_BELT)->values();
            $eventCertificates = $student->eventCertificates()->with('event')->get();
        }

        return view('portal.certificates', compact(
            'students',
            'student',
            'beltPromotions',
            'beltCertificates',
            'eventCertificates',
            'studentCertificates'
        ));
    }

    public function beltCertificatePdf(Student $student, BeltPromotion $promotion): Response
    {
        $this->assertStudentAccess($student);
        abort_unless((int) $promotion->student_id === (int) $student->id, 404);

        return Pdf::loadView('erp.pdf.belt-certificate', [
            'student' => $student,
            'promotion' => $promotion,
        ])->setPaper('a4', 'landscape')->download($promotion->certificate_number.'.pdf');
    }

    public function notices(): View
    {
        ['students' => $students, 'student' => $student] = $this->resolveStudentContext();
        $notices = file::query()->orderByDesc('id')->limit(30)->get();

        return view('portal.notices', compact('students', 'student', 'notices'));
    }

    /**
     * @return array{students: Collection<int, Student>, student: ?Student}
     */
    private function resolveStudentContext(?int $preferStudentId = null): array
    {
        $students = auth()->user()->portalStudents();
        $studentId = $preferStudentId
            ?: (request()->integer('student_id') ?: $students->first()?->id);
        $student = $students->firstWhere('id', $studentId);

        return compact('students', 'student');
    }

    private function assertStudentAccess(Student $student): void
    {
        $allowed = auth()->user()->portalStudents()->contains(fn (Student $s) => (int) $s->id === (int) $student->id);
        abort_unless($allowed, 403, 'You do not have access to this student.');
    }

    private function assertInvoiceAccess(Invoice $invoice): void
    {
        $invoice->loadMissing('student');
        $this->assertStudentAccess($invoice->student);
    }

    private function attendanceFor(Student $student): mixed
    {
        return app(AttendanceAnalyticsService::class)
            ->monthlySummary((int) now()->format('Y'), (int) now()->format('m'), $student->branch_id)
            ->firstWhere(fn ($r) => $r->student->id === $student->id);
    }
}
