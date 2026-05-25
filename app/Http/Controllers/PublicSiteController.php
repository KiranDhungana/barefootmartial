<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Event;
use App\Models\file;
use App\Models\OnlineRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public function branches(): View
    {
        $branches = Branch::query()
            ->where('is_active', true)
            ->withCount(['students as official_students' => fn ($q) => $q->where('registration_status', 'official')])
            ->orderBy('name')
            ->get();

        return view('public.branches', compact('branches'));
    }

    public function registerForm(): View
    {
        $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();

        return view('public.register', compact('branches'));
    }

    public function registerStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'student_name' => 'required|string|max:255',
            'parent_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email',
            'message' => 'nullable|string|max:1000',
        ]);

        OnlineRegistration::create($data);

        return redirect()->route('public.register')
            ->with('success', 'Thank you! We received your registration request and will contact you soon.');
    }

    public function events(): View
    {
        $events = Event::query()
            ->where('is_published', true)
            ->with('branch')
            ->orderByDesc('event_date')
            ->get();

        return view('public.events', compact('events'));
    }

    public function coaches(): View
    {
        return view('public.coaches');
    }

    public function notices(): View
    {
        $notices = file::query()->orderByDesc('id')->limit(10)->get();

        return view('public.notices', compact('notices'));
    }
}
