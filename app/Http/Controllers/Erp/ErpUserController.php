<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ErpUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin');
    }

    public function index(): View
    {
        $users = User::query()
            ->with('branch')
            ->whereIn('role', config('academy.erp_roles', []))
            ->orderBy('name')
            ->paginate(20);

        return view('erp.users.index', compact('users'));
    }

    public function create(): View
    {
        $branches = Branch::query()->orderBy('name')->get();
        $roles = config('academy.erp_roles', []);

        return view('erp.users.create', compact('branches', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $roles = config('academy.erp_roles', []);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in($roles)],
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        if (in_array($data['role'], [User::ROLE_BRANCH_ADMIN, User::ROLE_ACCOUNTANT, User::ROLE_COACH], true)
            && empty($data['branch_id'])) {
            return back()->withInput()->withErrors(['branch_id' => 'Branch is required for this role.']);
        }

        if ($data['role'] === User::ROLE_SUPER_ADMIN) {
            $data['branch_id'] = null;
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'branch_id' => $data['branch_id'] ?? null,
            'is_admin' => $data['role'] === User::ROLE_SUPER_ADMIN ? 1 : 0,
        ]);

        AuditLogger::log('user.created', $user, null, $user->only(['name', 'email', 'role', 'branch_id']));

        return redirect()->route('erp.users.index')->with('success', 'ERP user created.');
    }
}
