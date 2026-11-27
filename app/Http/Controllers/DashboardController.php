<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeriodFilterRequest;
use App\Services\Detection\DetectionReportService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(PeriodFilterRequest $request, DetectionReportService $report): View
    {
        $start = $request->startDate();
        $end = $request->endDate();
        $logs = $report->logs($start, $end);

        return view('dashboard.index', [
            'startDate' => $start,
            'endDate' => $end,
            'stats' => $report->stats($logs),
            'prediction' => $report->prediction(),
            'recentLogs' => $logs->take(config('hamapintar.recent_logs_limit')),
        ]);
    }
}
