<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Event;
use App\Models\file;
use App\Models\OnlineRegistration;
use App\Models\SiteMedia;
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
