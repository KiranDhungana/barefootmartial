<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\BeltPromotionService;
use Illuminate\View\View;

class QrVerifyController extends Controller
{
    public function show(string $token): View
    {
        $student = Student::query()
            ->with(['branch', 'beltPromotions' => fn ($q) => $q->limit(5)])
            ->where('qr_token', $token)
            ->firstOrFail();

        $beltService = app(BeltPromotionService::class);

        return view('verify.student', [
            'student' => $student,
            'nextBelt' => $beltService->nextBelt($student->belt_rank),
            'canCheckIn' => auth()->check() && auth()->user()->canAccessErp(),
            'checkInUrl' => route('erp.attendance.scan', ['token' => $token]),
        ]);
    }
}
