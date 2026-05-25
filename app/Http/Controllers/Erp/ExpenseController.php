<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Expense;
use App\Services\AuditLogger;
use App\Support\BranchScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('finance');
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        $q = Expense::query()->with(['branch', 'recordedBy'])->latest('expense_date');

        if ($user->isBranchScoped()) {
            $q->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $q->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('month')) {
            $y = (int) substr($request->month, 0, 4);
            $m = (int) substr($request->month, 5, 2);
            $q->whereYear('expense_date', $y)->whereMonth('expense_date', $m);
        }

        $total = (clone $q)->sum('amount');
        $expenses = $q->paginate(25)->withQueryString();
        $branches = $user->isSuperAdmin()
            ? Branch::query()->orderBy('name')->get()
            : collect();

        return view('erp.expenses.index', compact('expenses', 'total', 'branches'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $branches = $user->isSuperAdmin()
            ? Branch::query()->orderBy('name')->get()
            : Branch::query()->where('id', $user->branch_id)->get();

        return view('erp.expenses.create', [
            'branches' => $branches,
            'categories' => config('academy.expense_categories', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'category' => 'required|string|max:64',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);

        if ($user->isBranchScoped() && (int) $data['branch_id'] !== (int) $user->branch_id) {
            abort(403);
        }

        $expense = Expense::create([
            ...$data,
            'recorded_by' => $user->id,
        ]);

        AuditLogger::log('expense.created', $expense, null, $expense->getAttributes());

        return redirect()->route('erp.expenses.index')->with('success', 'Expense recorded.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $user = auth()->user();
        if ($user->isBranchScoped() && (int) $expense->branch_id !== (int) $user->branch_id) {
            abort(403);
        }

        AuditLogger::log('expense.deleted', $expense, $expense->getAttributes(), null);
        $expense->delete();

        return back()->with('success', 'Expense removed.');
    }
}
