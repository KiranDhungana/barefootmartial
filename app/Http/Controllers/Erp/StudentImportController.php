<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Services\StudentImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentImportController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! $request->user()?->canImportStudents()) {
                return redirect()->route('erp.dashboard')->with('error', 'You cannot import students.');
            }

            return $next($request);
        });
    }

    public function index(): View
    {
        return view('erp.students.import');
    }

    public function template(): StreamedResponse
    {
        $headers = StudentImportService::csvTemplateHeaders();

        return response()->streamDownload(function () use ($headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            fputcsv($out, [
                'Ram Bahadur',
                'Yellow',
                'MAIN',
                '2024-01-15',
                'paid',
                'issued',
                'Sita Devi',
                '9800000000',
                '9801111111',
                'Kathmandu',
                'active',
            ]);
            fclose($out);
        }, 'student-import-template.csv', ['Content-Type' => 'text/csv']);
    }

    public function store(Request $request, StudentImportService $importer): RedirectResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $result = $importer->importFromCsv($request->file('csv_file'));

        $message = "{$result['created']} student(s) imported (pending official registration).";
        if (count($result['errors']) > 0) {
            return redirect()->route('erp.students.import')
                ->with('success', $message)
                ->with('import_errors', array_slice($result['errors'], 0, 20));
        }

        return redirect()->route('erp.students.index')->with('success', $message);
    }

    public function storeManual(Request $request, StudentImportService $importer): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'belt_rank' => 'nullable|string|max:64',
            'branch' => 'nullable|string|max:64',
            'join_date' => 'nullable|date',
            'fee_status' => 'nullable|string|max:64',
            'uniform_status' => 'nullable|string|max:64',
            'parent_name' => 'nullable|string|max:255',
            'parent_contact' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'status' => 'nullable|string|max:32',
        ]);

        $result = $importer->importRow([
            'name' => $request->input('name'),
            'belt' => $request->input('belt_rank'),
            'branch' => $request->input('branch'),
            'joining_date' => $request->input('join_date'),
            'fee_status' => $request->input('fee_status'),
            'uniform_status' => $request->input('uniform_status'),
            'parent_name' => $request->input('parent_name'),
            'parent_contact' => $request->input('parent_contact'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'status' => $request->input('status', 'active'),
        ], $request->user());

        if ($result !== true) {
            return back()->withInput()->withErrors(['import' => $result]);
        }

        return redirect()->route('erp.students.import')
            ->with('success', 'Student imported. Mark as official after verifying details.');
    }
}
