<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Student;
use App\Services\CloudinaryService;
use App\Support\BranchScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        private CloudinaryService $cloudinary
    ) {
    }

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
        $event->load(['branch', 'registrations.student', 'registrations.certificateUploader']);
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

    public function destroy(Event $event): RedirectResponse
    {
        $this->assertEventAccess($event);
        $event->delete();

        return redirect()->route('erp.events.index')->with('success', 'Event deleted.');
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
                'registrant_name' => $student->name,
                'phone' => $student->phone,
                'category' => $data['category'] ?? null,
                'fee_amount' => $data['fee_amount'] ?? $event->fee_amount,
                'status' => 'registered',
            ]
        );

        return back()->with('success', 'Student registered for event.');
    }

    public function attachCertificate(Request $request, Event $event, EventRegistration $registration): RedirectResponse
    {
        $this->assertEventAccess($event);
        abort_unless((int) $registration->event_id === (int) $event->id, 404);

        $data = $request->validate([
            'certificate' => 'required|file|mimes:jpeg,jpg,png,webp,gif,pdf|max:10240',
            'certificate_title' => 'nullable|string|max:255',
            'certificate_issued_on' => 'nullable|date',
            'certificate_number' => 'nullable|string|max:64',
        ]);

        if ($registration->certificate_public_id) {
            try {
                $this->cloudinary->delete(
                    $registration->certificate_public_id,
                    $registration->certificate_resource_type ?: 'image'
                );
            } catch (\Throwable $e) {
                // continue replacing
            }
        }

        $uploaded = $this->cloudinary->uploadFile(
            $request->file('certificate'),
            'events/'.$event->id.'/certificates'
        );

        $registration->update([
            'certificate_url' => $uploaded['url'],
            'certificate_public_id' => $uploaded['public_id'],
            'certificate_resource_type' => $uploaded['resource_type'],
            'certificate_title' => $data['certificate_title']
                ?: ($event->title.' — '.$registration->displayName()),
            'certificate_issued_on' => $data['certificate_issued_on'] ?? now()->toDateString(),
            'certificate_number' => $data['certificate_number']
                ?: ($registration->certificate_number ?: EventRegistration::generateCertificateNumber()),
            'certificate_uploaded_by' => auth()->id(),
            'status' => $registration->status === 'registered' ? 'completed' : $registration->status,
        ]);

        return back()->with('success', 'Certificate attached for '.$registration->displayName().'.');
    }

    public function removeCertificate(Event $event, EventRegistration $registration): RedirectResponse
    {
        $this->assertEventAccess($event);
        abort_unless((int) $registration->event_id === (int) $event->id, 404);

        if ($registration->certificate_public_id) {
            try {
                $this->cloudinary->delete(
                    $registration->certificate_public_id,
                    $registration->certificate_resource_type ?: 'image'
                );
            } catch (\Throwable $e) {
                // still clear DB fields
            }
        }

        $registration->update([
            'certificate_url' => null,
            'certificate_public_id' => null,
            'certificate_resource_type' => null,
            'certificate_title' => null,
            'certificate_issued_on' => null,
            'certificate_uploaded_by' => null,
        ]);

        return back()->with('success', 'Certificate removed.');
    }

    public function updateRegistrationStatus(Request $request, Event $event, EventRegistration $registration): RedirectResponse
    {
        $this->assertEventAccess($event);
        abort_unless((int) $registration->event_id === (int) $event->id, 404);

        $data = $request->validate([
            'status' => 'required|in:registered,confirmed,completed,cancelled',
        ]);
        $registration->update(['status' => $data['status']]);

        return back()->with('success', 'Registration status updated.');
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
