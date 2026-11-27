<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeriodFilterRequest;
use App\Services\Detection\DetectionReportService;
use App\View\Presenters\MapPayload;
use Illuminate\View\View;

class MapController extends Controller
{
    public function __invoke(PeriodFilterRequest $request, DetectionReportService $report): View
    {
        $start = $request->startDate();
        $end = $request->endDate();
        $logs = $report->logs($start, $end);

        return view('map.index', [
            'startDate' => $start,
            'endDate' => $end,
            'stats' => $report->stats($logs),
            'payload' => MapPayload::make($logs),
        ]);
    }
}
