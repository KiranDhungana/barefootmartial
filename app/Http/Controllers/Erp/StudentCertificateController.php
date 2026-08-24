<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentCertificate;
use App\Services\CloudinaryService;
use App\Support\BranchScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
            'file' => 'required|file|mimes:jpeg,jpg,png,webp,gif,pdf|max:10240',
            'issued_on' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $uploaded = $this->cloudinary->uploadFile($request->file('file'), 'certificates/'.$student->id);

        StudentCertificate::create([
            'student_id' => $student->id,
            'title' => $data['title'],
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
            'issued_on' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'file' => 'nullable|file|mimes:jpeg,jpg,png,webp,gif,pdf|max:10240',
        ]);

        $payload = [
            'title' => $data['title'],
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
}
