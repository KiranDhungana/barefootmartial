<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin')->except(['index', 'show']);
    }

    public function index(): View
    {
        $user = auth()->user();
        $branches = $user?->isSuperAdmin()
            ? Branch::query()->withCount('students')->orderBy('name')->get()
            : Branch::query()->where('id', $user->branch_id)->withCount('students')->get();

        return view('erp.branches.index', compact('branches'));
    }

    public function create(): View
    {
        return view('erp.branches.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:32|unique:branches,code',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        Branch::create($data);

        return redirect()->route('erp.branches.index')->with('success', 'Branch created.');
    }

    public function show(Branch $branch): View
    {
        $this->assertBranchAccess($branch);
        $branch->load(['schedules' => fn ($q) => $q->orderBy('day_of_week')->orderBy('start_time')]);

        return view('erp.branches.show', compact('branch'));
    }

    public function edit(Branch $branch): View
    {
        return view('erp.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:32|unique:branches,code,'.$branch->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $branch->update($data);

        return redirect()->route('erp.branches.index')->with('success', 'Branch updated.');
    }

    private function assertBranchAccess(Branch $branch): void
    {
        $user = auth()->user();
        if ($user?->isBranchScoped() && (int) $user->branch_id !== (int) $branch->id) {
            abort(403);
        }
    }
}
