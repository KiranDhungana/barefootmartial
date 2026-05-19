<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request): View
    {
        $q = Invoice::query()->with('student')->latest();

        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }

        $invoices = $q->paginate(20)->withQueryString();

        return view('erp.invoices.index', compact('invoices'));
    }

    public function create(Request $request): View
    {
        $students = Student::query()->orderBy('name')->get();
        $studentId = $request->integer('student_id') ?: $students->first()?->id;

        return view('erp.invoices.create', compact('students', 'studentId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $invoice = new Invoice($data);
        $invoice->invoice_number = Invoice::generateInvoiceNumber();
        $invoice->status = 'pending';
        $invoice->save();

        return redirect()->route('erp.invoices.show', $invoice)->with('success', 'Invoice created.');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load('student');

        return view('erp.invoices.show', compact('invoice'));
    }

    public function pdf(Invoice $invoice): \Symfony\Component\HttpFoundation\Response
    {
        $invoice->load('student.branch');

        return Pdf::loadView('erp.pdf.invoice', ['invoice' => $invoice])
            ->download($invoice->invoice_number.'.pdf');
    }

    public function markPaid(Invoice $invoice): RedirectResponse
    {
        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Marked as paid.');
    }

    public function markPending(Invoice $invoice): RedirectResponse
    {
        $invoice->update([
            'status' => 'pending',
            'paid_at' => null,
        ]);

        return back()->with('success', 'Marked as pending.');
    }
}
