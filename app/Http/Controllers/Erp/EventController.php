<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Student;
use App\Support\BranchScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $events = Event::query()
            ->with(['branch', 'registrations'])
            ->when($user?->isBranchScoped(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->orderByDesc('event_date')
            ->get();

        return view('erp.events.index', compact('events'));
    }

    public function create(): View
    {
        return view('erp.events.create', ['branches' => $this->branchesForForm()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateEvent($request);
        $this->assertBranchId($data['branch_id'] ?? null);
        Event::create($data);

        return redirect()->route('erp.events.index')->with('success', 'Event created.');
    }

    public function show(Event $event): View
    {
        $this->assertEventAccess($event);
        $event->load(['branch', 'registrations.student']);
        $students = BranchScope::students()
            ->where('registration_status', Student::REG_OFFICIAL)
            ->orderBy('name')
            ->get();

        return view('erp.events.show', compact('event', 'students'));
    }

    public function edit(Event $event): View
    {
        $this->assertEventAccess($event);

        return view('erp.events.edit', [
            'event' => $event,
            'branches' => $this->branchesForForm(),
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $this->assertEventAccess($event);
        $data = $this->validateEvent($request);
        $this->assertBranchId($data['branch_id'] ?? null);
        $event->update($data);

        return redirect()->route('erp.events.show', $event)->with('success', 'Event updated.');
    }

    public function registerStudent(Request $request, Event $event): RedirectResponse
    {
        $this->assertEventAccess($event);
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'category' => 'nullable|string|max:128',
            'fee_amount' => 'nullable|numeric|min:0',
        ]);

        $student = Student::query()->findOrFail($data['student_id']);
        BranchScope::assertStudentAccess($student);

        EventRegistration::updateOrCreate(
            ['event_id' => $event->id, 'student_id' => $student->id],
            [
                'category' => $data['category'] ?? null,
                'fee_amount' => $data['fee_amount'] ?? $event->fee_amount,
                'status' => 'registered',
            ]
        );

        return back()->with('success', 'Student registered for event.');
    }

    private function validateEvent(Request $request): array
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'nullable|date',
            'registration_deadline' => 'nullable|date',
            'fee_amount' => 'nullable|numeric|min:0',
            'is_published' => 'boolean',
        ]);
        $data['is_published'] = $request->boolean('is_published', true);

        return $data;
    }

    private function branchesForForm()
    {
        $user = auth()->user();
        if ($user?->isBranchScoped()) {
            return Branch::query()->where('id', $user->branch_id)->get();
        }

        return Branch::query()->orderBy('name')->get();
    }

    private function assertBranchId(?int $branchId): void
    {
        $user = auth()->user();
        if ($branchId && $user?->isBranchScoped() && (int) $user->branch_id !== $branchId) {
            abort(403);
        }
    }

    private function assertEventAccess(Event $event): void
    {
        $user = auth()->user();
        if ($user?->isBranchScoped() && $event->branch_id && (int) $event->branch_id !== (int) $user->branch_id) {
            abort(403);
        }
    }
}
