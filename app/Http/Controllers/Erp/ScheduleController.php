<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ClassSchedule;
use App\Support\BranchScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $branchId = $user?->isBranchScoped()
            ? $user->branch_id
            : ($request->integer('branch_id') ?: null);

        $schedules = ClassSchedule::query()
            ->with('branch')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('branch_id')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $branches = Branch::query()->orderBy('name')->get();

        return view('erp.schedules.index', compact('schedules', 'branches', 'branchId'));
    }

    public function create(): View
    {
        return view('erp.schedules.create', [
            'branches' => $this->branchesForForm(),
            'days' => config('academy.days_of_week', []),
            'belts' => config('academy.belt_ranks', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateSchedule($request);
        $this->assertBranchId($data['branch_id']);
        ClassSchedule::create($data);

        return redirect()->route('erp.schedules.index')->with('success', 'Class schedule added.');
    }

    public function edit(ClassSchedule $schedule): View
    {
        $this->assertBranchId($schedule->branch_id);

        return view('erp.schedules.edit', [
            'schedule' => $schedule,
            'branches' => $this->branchesForForm(),
            'days' => config('academy.days_of_week', []),
            'belts' => config('academy.belt_ranks', []),
        ]);
    }

    public function update(Request $request, ClassSchedule $schedule): RedirectResponse
    {
        $this->assertBranchId($schedule->branch_id);
        $data = $this->validateSchedule($request);
        $this->assertBranchId($data['branch_id']);
        $schedule->update($data);

        return redirect()->route('erp.schedules.index')->with('success', 'Schedule updated.');
    }

    public function destroy(ClassSchedule $schedule): RedirectResponse
    {
        $this->assertBranchId($schedule->branch_id);
        $schedule->delete();

        return redirect()->route('erp.schedules.index')->with('success', 'Schedule removed.');
    }

    private function validateSchedule(Request $request): array
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'coach_name' => 'nullable|string|max:255',
            'day_of_week' => 'required|string|max:16',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'belt_level' => 'nullable|string|max:64',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

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

    private function assertBranchId(int $branchId): void
    {
        $user = auth()->user();
        if ($user?->isBranchScoped() && (int) $user->branch_id !== $branchId) {
            abort(403);
        }
    }
}
