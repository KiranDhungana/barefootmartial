<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Student;
use App\Models\StudentCertificate;
use App\Services\CloudinaryService;
use App\Support\BranchScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StudentCertificateController extends Controller
{
    public function __construct(
        private CloudinaryService $cloudinary
    ) {
        $this->middleware('auth');
    }

    public function store(Request $request, Student $student): RedirectResponse
    {
        BranchScope::assertStudentAccess($student);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'certificate_type' => 'required|in:general,belt,event',
            'event_id' => 'nullable|required_if:certificate_type,event|exists:events,id',
            'file' => 'required|file|mimes:jpeg,jpg,png,webp,gif,pdf|max:10240',
            'issued_on' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'certificate_number' => 'nullable|string|max:64',
        ]);

        if ($data['certificate_type'] === StudentCertificate::TYPE_EVENT) {
            return $this->attachEventCertificate($request, $student, $data);
        }

        $uploaded = $this->cloudinary->uploadFile($request->file('file'), 'certificates/'.$student->id);

        StudentCertificate::create([
            'student_id' => $student->id,
            'title' => $data['title'],
            'certificate_type' => $data['certificate_type'],
            'file_url' => $uploaded['url'],
            'public_id' => $uploaded['public_id'],
            'resource_type' => $uploaded['resource_type'],
            'original_filename' => $request->file('file')->getClientOriginalName(),
            'issued_on' => $data['issued_on'] ?? null,
            'notes' => $data['notes'] ?? null,
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()
            ->route('erp.students.show', [$student, 'tab' => 'certificates'])
            ->with('success', 'Certificate attached.');
    }

    public function update(Request $request, Student $student, StudentCertificate $certificate): RedirectResponse
    {
        BranchScope::assertStudentAccess($student);
        abort_unless((int) $certificate->student_id === (int) $student->id, 404);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'certificate_type' => 'required|in:general,belt',
            'issued_on' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'file' => 'nullable|file|mimes:jpeg,jpg,png,webp,gif,pdf|max:10240',
        ]);

        $payload = [
            'title' => $data['title'],
            'certificate_type' => $data['certificate_type'],
            'issued_on' => $data['issued_on'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];

        if ($request->hasFile('file')) {
            if ($certificate->public_id) {
                try {
                    $this->cloudinary->delete($certificate->public_id, $certificate->resource_type ?: 'image');
                } catch (\Throwable $e) {
                    // continue replacing
                }
            }
            $uploaded = $this->cloudinary->uploadFile($request->file('file'), 'certificates/'.$student->id);
            $payload['file_url'] = $uploaded['url'];
            $payload['public_id'] = $uploaded['public_id'];
            $payload['resource_type'] = $uploaded['resource_type'];
            $payload['original_filename'] = $request->file('file')->getClientOriginalName();
        }

        $certificate->update($payload);

        return redirect()
            ->route('erp.students.show', [$student, 'tab' => 'certificates'])
            ->with('success', 'Certificate updated.');
    }

    public function destroy(Student $student, StudentCertificate $certificate): RedirectResponse
    {
        BranchScope::assertStudentAccess($student);
        abort_unless((int) $certificate->student_id === (int) $student->id, 404);

        if ($certificate->public_id) {
            try {
                $this->cloudinary->delete($certificate->public_id, $certificate->resource_type ?: 'image');
            } catch (\Throwable $e) {
                // still remove DB row
            }
        }
        $certificate->delete();

        return redirect()
            ->route('erp.students.show', [$student, 'tab' => 'certificates'])
            ->with('success', 'Certificate removed.');
    }

    private function attachEventCertificate(Request $request, Student $student, array $data): RedirectResponse
    {
        $event = Event::query()->findOrFail((int) $data['event_id']);
        $user = auth()->user();

        if ($user?->isBranchScoped() && $event->branch_id && (int) $event->branch_id !== (int) $user->branch_id) {
            abort(403);
        }

        if ($event->branch_id && $student->branch_id && (int) $event->branch_id !== (int) $student->branch_id) {
            throw ValidationException::withMessages([
                'event_id' => 'This event belongs to a different branch than the student.',
            ]);
        }

        $registration = EventRegistration::query()
            ->where('event_id', $event->id)
            ->where('student_id', $student->id)
            ->first();

        if (! $registration) {
            $registration = EventRegistration::create([
                'event_id' => $event->id,
                'student_id' => $student->id,
                'fee_amount' => $event->fee_amount ?? 0,
                'status' => 'completed',
                'notes' => 'Registered when attaching certificate from student profile.',
            ]);
        }

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
            $request->file('file'),
            'events/'.$event->id.'/certificates'
        );

        $registration->update([
            'certificate_url' => $uploaded['url'],
            'certificate_public_id' => $uploaded['public_id'],
            'certificate_resource_type' => $uploaded['resource_type'],
            'certificate_title' => $data['title'],
            'certificate_issued_on' => $data['issued_on'] ?? now()->toDateString(),
            'certificate_number' => $data['certificate_number']
                ?: ($registration->certificate_number ?: EventRegistration::generateCertificateNumber()),
            'certificate_uploaded_by' => auth()->id(),
            'status' => $registration->status === 'registered' ? 'completed' : $registration->status,
            'notes' => $data['notes'] ?? $registration->notes,
        ]);

        return redirect()
            ->route('erp.students.show', [$student, 'tab' => 'certificates'])
            ->with('success', 'Event certificate attached for '.$event->title.'.');
    }
}
