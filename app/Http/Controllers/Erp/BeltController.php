<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\BeltPromotionService;
use App\Support\BranchScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BeltController extends Controller
{
    public function __construct(private BeltPromotionService $belts)
    {
    }

    public function index(Request $request): View
    {
        $students = BranchScope::students()
            ->where('registration_status', Student::REG_OFFICIAL)
            ->orderBy('name')
            ->get()
            ->map(fn (Student $s) => (object) [
                'student' => $s,
                'next_belt' => $this->belts->nextBelt($s->belt_rank),
                'eligible' => $this->belts->isEligible($s),
                'reason' => $this->belts->eligibilityReason($s),
            ]);

        $eligible = $students->filter(fn ($r) => $r->eligible);

        return view('erp.belts.index', [
            'rows' => $students,
            'eligible' => $eligible,
            'beltRanks' => config('academy.belt_ranks', []),
        ]);
    }

    public function promoteForm(Student $student): View
    {
        BranchScope::assertStudentAccess($student);
        $student->load('beltPromotions.promotedByUser');

        return view('erp.belts.promote', [
            'student' => $student,
            'nextBelt' => $this->belts->nextBelt($student->belt_rank),
            'beltRanks' => config('academy.belt_ranks', []),
            'history' => $student->beltPromotions,
        ]);
    }

    public function promote(Request $request, Student $student): RedirectResponse
    {
        BranchScope::assertStudentAccess($student);

        $data = $request->validate([
            'to_belt' => 'required|string|max:64',
            'promoted_at' => 'nullable|date',
            'exam_passed' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        try {
            $promotion = $this->belts->promote($student, $data, auth()->user());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        return redirect()->route('erp.belts.promote', $student)
            ->with('success', 'Promoted to '.$promotion->to_belt.' (cert '.$promotion->certificate_number.').');
    }

    public function certificatePdf(Student $student, int $promotion): \Symfony\Component\HttpFoundation\Response
    {
        BranchScope::assertStudentAccess($student);
        $record = $student->beltPromotions()->findOrFail($promotion);

        return Pdf::loadView('erp.pdf.belt-certificate', [
            'student' => $student,
            'promotion' => $record,
        ])->setPaper('a4', 'landscape')->download($record->certificate_number.'.pdf');
    }
}
