<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\SalaryRecord;
use App\Models\Trainer;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin');
    }

    public function index(Request $request): View
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $records = SalaryRecord::query()
            ->with('trainer')
            ->where('year', $year)
            ->where('month', $month)
            ->orderBy('trainer_id')
            ->get();

        return view('erp.salary.index', compact('records', 'year', 'month'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year = $data['year'];
        $month = $data['month'];

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $weekdaysInMonth = 0;
        for ($d = 1; $d <= $start->daysInMonth; $d++) {
            $day = Carbon::createFromDate($year, $month, $d);
            if ($day->isWeekday()) {
                $weekdaysInMonth++;
            }
        }

        $trainers = Trainer::query()->get();

        foreach ($trainers as $trainer) {
            $days = null;
            $amount = 0.0;

            if ($trainer->salary_mode === 'fixed') {
                $amount = (float) ($trainer->monthly_amount ?? 0);
                $days = null;
            } else {
                $perDay = (float) ($trainer->per_day_amount ?? 0);
                $days = $weekdaysInMonth;
                $amount = round($perDay * $weekdaysInMonth, 2);
            }

            SalaryRecord::query()->updateOrCreate(
                [
                    'trainer_id' => $trainer->id,
                    'year' => $year,
                    'month' => $month,
                ],
                [
                    'amount' => $amount,
                    'attendance_days' => $days,
                    'meta' => [
                        'salary_mode' => $trainer->salary_mode,
                        'weekdays_in_month' => $weekdaysInMonth,
                    ],
                ]
            );
        }

        return redirect()
            ->route('erp.salary.index', ['year' => $year, 'month' => $month])
            ->with('success', 'Salary rows generated for '.$start->format('F Y').'.');
    }

    public function pdf(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $records = SalaryRecord::query()
            ->with('trainer')
            ->where('year', $year)
            ->where('month', $month)
            ->orderBy('trainer_id')
            ->get();

        $period = Carbon::createFromDate($year, $month, 1)->format('F Y');

        return Pdf::loadView('erp.pdf.salary-report', compact('records', 'period', 'year', 'month'))
            ->download('salary-'.$year.'-'.str_pad((string) $month, 2, '0', STR_PAD_LEFT).'.pdf');
    }
}
