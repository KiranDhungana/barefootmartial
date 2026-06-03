<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\OnlineRegistration;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnlineRegistrationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'super_admin']);
    }

    public function index(Request $request): View
    {
        $q = OnlineRegistration::query()->with('branch')->latest();

        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }

        $registrations = $q->paginate(25)->withQueryString();

        return view('erp.online-registrations.index', compact('registrations'));
    }

    public function updateStatus(Request $request, OnlineRegistration $onlineRegistration): RedirectResponse
    {
        $data = $request->validate(['status' => 'required|in:pending,contacted,enrolled,rejected']);

        $onlineRegistration->update(['status' => $data['status']]);

        return back()->with('success', 'Registration status updated.');
    }

    public function convert(OnlineRegistration $onlineRegistration): RedirectResponse
    {
        if ($onlineRegistration->student_id) {
            return redirect()->route('erp.students.show', $onlineRegistration->student_id)
                ->with('success', 'Already linked to a student.');
        }

        $student = new Student([
            'branch_id' => $onlineRegistration->branch_id,
            'name' => $onlineRegistration->student_name,
            'parent_name' => $onlineRegistration->parent_name,
            'phone' => $onlineRegistration->phone,
            'parent_contact' => $onlineRegistration->phone,
            'registration_status' => Student::REG_PENDING,
            'status' => Student::STATUS_ACTIVE,
            'notes' => $onlineRegistration->message,
        ]);
        $student->save();

        $onlineRegistration->update([
            'student_id' => $student->id,
            'status' => 'enrolled',
        ]);

        return redirect()->route('erp.students.show', $student)
            ->with('success', 'Registration approved. Student record created — complete the profile and mark official when ready.');
    }
}
