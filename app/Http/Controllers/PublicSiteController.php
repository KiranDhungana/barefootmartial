<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\file;
use App\Models\OnlineRegistration;
use App\Models\SiteMedia;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public function home(): View
    {
        $slides = SiteMedia::query()
            ->ofType(SiteMedia::TYPE_SLIDER)
            ->active()
            ->ordered()
            ->get();

        $galleryPreview = SiteMedia::query()
            ->ofType(SiteMedia::TYPE_GALLERY)
            ->active()
            ->ordered()
            ->limit(6)
            ->get();

        return view('welcome', compact('slides', 'galleryPreview'));
    }

    public function gallery(): View
    {
        $gallery = SiteMedia::query()
            ->ofType(SiteMedia::TYPE_GALLERY)
            ->active()
            ->ordered()
            ->get();

        return view('galary', compact('gallery'));
    }

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

        OnlineRegistration::create([
            ...$data,
            'status' => 'pending',
        ]);

        return redirect()->route('public.register')
            ->with('success', 'Thank you! Your registration request was submitted. A super admin will review it and contact you once approved.');
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

    public function eventShow(Event $event): View
    {
        abort_unless($event->is_published, 404);
        $event->load('branch');

        return view('public.event-show', compact('event'));
    }

    public function eventRegister(Request $request, Event $event): RedirectResponse
    {
        abort_unless($event->is_published, 404);

        if (! $event->isOpenForRegistration()) {
            return back()->withInput()->withErrors([
                'registration' => 'Registration for this event is closed.',
            ]);
        }

        $data = $request->validate([
            'registrant_name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'student_code' => 'nullable|string|max:64',
            'category' => 'nullable|string|max:128',
            'notes' => 'nullable|string|max:1000',
        ]);

        $student = null;
        if (! empty($data['student_code'])) {
            $student = Student::query()
                ->where('student_code', $data['student_code'])
                ->first();
            if (! $student) {
                return back()->withInput()->withErrors([
                    'student_code' => 'No student found with that membership ID.',
                ]);
            }
        } else {
            $student = Student::query()
                ->where(function ($q) use ($data) {
                    $q->where('phone', $data['phone'])
                        ->orWhere('parent_contact', $data['phone']);
                })
                ->first();
        }

        if ($student) {
            $exists = EventRegistration::query()
                ->where('event_id', $event->id)
                ->where('student_id', $student->id)
                ->exists();
            if ($exists) {
                return back()->withInput()->withErrors([
                    'registration' => 'This student is already registered for this event.',
                ]);
            }
        } else {
            $exists = EventRegistration::query()
                ->where('event_id', $event->id)
                ->whereNull('student_id')
                ->where('phone', $data['phone'])
                ->exists();
            if ($exists) {
                return back()->withInput()->withErrors([
                    'phone' => 'This phone number is already registered for this event.',
                ]);
            }
        }

        EventRegistration::create([
            'event_id' => $event->id,
            'student_id' => $student?->id,
            'registrant_name' => $data['registrant_name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'category' => $data['category'] ?? null,
            'notes' => $data['notes'] ?? null,
            'fee_amount' => $event->fee_amount,
            'status' => 'registered',
        ]);

        return redirect()
            ->route('public.events.show', $event)
            ->with('success', 'You are registered for '.$event->title.'. We will contact you with details.');
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
