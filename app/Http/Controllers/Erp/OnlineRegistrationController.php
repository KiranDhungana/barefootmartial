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
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        $q = OnlineRegistration::query()->with('branch')->latest();

        if ($user?->isBranchScoped()) {
            $q->where('branch_id', $user->branch_id);
        }
        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }

        $registrations = $q->paginate(25)->withQueryString();

        return view('erp.online-registrations.index', compact('registrations'));
    }

    public function updateStatus(Request $request, OnlineRegistration $onlineRegistration): RedirectResponse
    {
        $this->assertAccess($onlineRegistration);
        $data = $request->validate(['status' => 'required|in:pending,contacted,enrolled,rejected']);

        $onlineRegistration->update(['status' => $data['status']]);

        return back()->with('success', 'Status updated.');
    }

    public function convert(OnlineRegistration $onlineRegistration): RedirectResponse
    {
        $this->assertAccess($onlineRegistration);
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
            ->with('success', 'Student record created from online registration. Complete profile and mark official when ready.');
    }

    private function assertAccess(OnlineRegistration $reg): void
    {
        $user = auth()->user();
        if ($user?->isBranchScoped() && $reg->branch_id && (int) $reg->branch_id !== (int) $user->branch_id) {
            abort(403);
        }
    }
}
