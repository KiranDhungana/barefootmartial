<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Services\InventoryService;
use App\Services\InvoiceBillingService;
use App\Services\StudentRegistrationService;
use App\Support\BranchScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        private StudentRegistrationService $registration,
        private InvoiceBillingService $billing,
        private InventoryService $inventory
    ) {
        $this->middleware('finance');
    }

    public function index(Request $request): View
    {
        $this->billing->refreshOverdueStatuses(
            auth()->user()?->isBranchScoped() ? auth()->user()->branch_id : null
        );

        $q = BranchScope::invoices()->with('student.branch')->latest();

        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }

        $invoices = $q->paginate(20)->withQueryString();

        return view('erp.invoices.index', compact('invoices'));
    }

    public function create(Request $request): View
    {
        $students = BranchScope::students()
            ->where('registration_status', Student::REG_OFFICIAL)
            ->orderBy('name')
            ->get();
        $studentId = $request->integer('student_id') ?: $students->first()?->id;
        $student = $studentId ? $students->firstWhere('id', $studentId) : null;
        $branchId = $student?->branch_id ?? auth()->user()?->branch_id;

        $feeTypes = config('academy.fee_types', []);
        $inventoryItems = $this->inventory->itemsWithStock($branchId);
        $paymentMethods = config('academy.payment_methods', ['cash']);

        return view('erp.invoices.create', compact(
            'students',
            'studentId',
            'student',
            'feeTypes',
            'inventoryItems',
            'paymentMethods'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'late_fee' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'scholarship_waiver' => 'nullable|boolean',
            'initial_payment' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'fees' => 'nullable|array',
            'inventory' => 'nullable|array',
        ]);

        $student = Student::query()->findOrFail($request->integer('student_id'));
        BranchScope::assertStudentAccess($student);
        try {
            $this->registration->assertOfficialForAction($student, 'billing');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        $feeLines = [];
        foreach ($request->input('fees', []) as $key => $row) {
            if (empty($row['selected'])) {
                continue;
            }
            $feeLines[] = [
                'fee_type' => $key,
                'selected' => true,
                'unit_price' => $row['unit_price'] ?? 0,
                'quantity' => $row['quantity'] ?? 1,
            ];
        }

        $inventoryLines = [];
        foreach ($request->input('inventory', []) as $row) {
            if (empty($row['inventory_item_id']) || empty($row['quantity'])) {
                continue;
            }
            $inventoryLines[] = $row;
        }

        try {
            $invoice = $this->billing->createInvoice(
                $student,
                $feeLines,
                $inventoryLines,
                [
                    'due_date' => $request->input('due_date'),
                    'notes' => $request->input('notes'),
                    'late_fee' => $request->input('late_fee', 0),
                    'discount_percent' => $request->input('discount_percent'),
                    'scholarship_waiver' => $request->boolean('scholarship_waiver')
                        || $student->hasFullScholarship(),
                    'initial_payment' => $request->input('initial_payment', 0),
                    'payment_method' => $request->input('payment_method', 'cash'),
                ]
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        return redirect()->route('erp.invoices.show', $invoice)
            ->with('success', 'Invoice created. Total: '.number_format($invoice->amount, 2));
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['student.branch', 'lineItems.inventoryItem', 'payments.recordedBy']);
        BranchScope::assertStudentAccess($invoice->student);

        $paymentMethods = config('academy.payment_methods', ['cash']);

        return view('erp.invoices.show', compact('invoice', 'paymentMethods'));
    }

    public function pdf(Invoice $invoice): \Symfony\Component\HttpFoundation\Response
    {
        $invoice->load(['student.branch', 'lineItems']);
        BranchScope::assertStudentAccess($invoice->student);

        return Pdf::loadView('erp.pdf.invoice', ['invoice' => $invoice])
            ->download($invoice->invoice_number.'.pdf');
    }

    public function receiptPdf(Invoice $invoice, Payment $payment): \Symfony\Component\HttpFoundation\Response
    {
        abort_unless((int) $payment->invoice_id === (int) $invoice->id, 404);
        $invoice->load(['student.branch', 'lineItems']);
        BranchScope::assertStudentAccess($invoice->student);

        return Pdf::loadView('erp.pdf.receipt', compact('invoice', 'payment'))
            ->download($payment->receipt_number.'.pdf');
    }

    public function paymentSlipPdf(Invoice $invoice): \Symfony\Component\HttpFoundation\Response
    {
        $invoice->load(['student.branch', 'lineItems', 'payments']);
        BranchScope::assertStudentAccess($invoice->student);

        return Pdf::loadView('erp.pdf.payment-slip', ['invoice' => $invoice])
            ->download('payment-slip-'.$invoice->invoice_number.'.pdf');
    }

    public function storePayment(Request $request, Invoice $invoice): RedirectResponse
    {
        BranchScope::assertStudentAccess($invoice->student);

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->billing->recordPayment($invoice, (float) $data['amount'], [
                'payment_method' => $data['payment_method'],
                'notes' => $data['notes'] ?? null,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Payment recorded.');
    }

    public function applyLateFee(Request $request, Invoice $invoice): RedirectResponse
    {
        BranchScope::assertStudentAccess($invoice->student);
        $data = $request->validate(['late_fee' => 'nullable|numeric|min:0']);
        $this->billing->applyLateFee($invoice, isset($data['late_fee']) ? (float) $data['late_fee'] : null);

        return back()->with('success', 'Late fee applied.');
    }

    public function markPaid(Invoice $invoice): RedirectResponse
    {
        BranchScope::assertStudentAccess($invoice->student);
        $balance = $invoice->balanceDue();
        if ($balance > 0) {
            try {
                $this->billing->recordPayment($invoice, $balance, [
                    'payment_method' => 'cash',
                    'notes' => 'Marked paid in full',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return back()->withErrors($e->errors());
            }
        }

        return back()->with('success', 'Invoice paid in full.');
    }

    public function markPending(Invoice $invoice): RedirectResponse
    {
        BranchScope::assertStudentAccess($invoice->student);
        $invoice->payments()->delete();
        $invoice->update([
            'amount_paid' => 0,
            'status' => Invoice::STATUS_PENDING,
            'paid_at' => null,
        ]);
        $this->billing->syncStatus($invoice);

        return back()->with('success', 'Payments cleared; invoice pending.');
    }
}
