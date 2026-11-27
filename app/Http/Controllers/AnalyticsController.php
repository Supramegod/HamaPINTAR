<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeriodFilterRequest;
use App\Services\Detection\DetectionReportService;
use App\View\Presenters\ChartPayload;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __invoke(PeriodFilterRequest $request, DetectionReportService $report): View
    {
        $start = $request->startDate();
        $end = $request->endDate();
        $stats = $report->stats($report->logs($start, $end));
        $prediction = $report->prediction();

        return view('analytics.index', [
            'startDate' => $start,
            'endDate' => $end,
            'stats' => $stats,
            'prediction' => $prediction,
            'payload' => ChartPayload::make($stats, $prediction),
        ]);
    }
}
