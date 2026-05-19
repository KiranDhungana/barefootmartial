<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Trainer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainerController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(): View
    {
        $trainers = Trainer::query()->orderBy('name')->paginate(20);

        return view('erp.trainers.index', compact('trainers'));
    }

    public function create(): View
    {
        return view('erp.trainers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'role_title' => 'nullable|string|max:255',
            'salary_mode' => 'required|in:fixed,attendance',
            'monthly_amount' => 'nullable|numeric|min:0',
            'per_day_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        Trainer::query()->create($data);

        return redirect()->route('erp.trainers.index')->with('success', 'Trainer added.');
    }

    public function edit(Trainer $trainer): View
    {
        return view('erp.trainers.edit', compact('trainer'));
    }

    public function update(Request $request, Trainer $trainer): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'role_title' => 'nullable|string|max:255',
            'salary_mode' => 'required|in:fixed,attendance',
            'monthly_amount' => 'nullable|numeric|min:0',
            'per_day_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $trainer->update($data);

        return redirect()->route('erp.trainers.index')->with('success', 'Trainer updated.');
    }

    public function destroy(Trainer $trainer): RedirectResponse
    {
        $trainer->delete();

        return redirect()->route('erp.trainers.index')->with('success', 'Trainer removed.');
    }
}
