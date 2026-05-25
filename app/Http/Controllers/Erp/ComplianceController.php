<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Services\ComplianceService;
use Illuminate\View\View;

class ComplianceController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (! $user?->isSuperAdmin() && ! $user?->canManageFinance()) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function index(): View
    {
        $scores = app(ComplianceService::class)->branchScores()
            ->sortByDesc('overall')
            ->values();

        return view('erp.compliance.index', compact('scores'));
    }
}
