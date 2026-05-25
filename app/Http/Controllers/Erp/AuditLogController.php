<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Support\BranchScope;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! $request->user()?->canViewAuditLogs()) {
                return redirect()->route('erp.dashboard')->with('error', 'You cannot view audit logs.');
            }

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        $q = AuditLog::query()->with(['user', 'branch'])->orderByDesc('created_at');

        if ($user->isBranchScoped()) {
            $q->where('branch_id', $user->branch_id);
        }

        if ($request->filled('action')) {
            $q->where('action', 'like', '%'.$request->string('action').'%');
        }

        $logs = $q->paginate(30)->withQueryString();

        return view('erp.audit.index', compact('logs'));
    }
}
