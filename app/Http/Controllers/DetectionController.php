<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeriodFilterRequest;
use App\Models\DetectionLog;
use App\Services\Detection\DetectionReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DetectionController extends Controller
{
    public function index(PeriodFilterRequest $request, DetectionReportService $report): View
    {
        $start = $request->startDate();
        $end = $request->endDate();

        return view('detections.index', [
            'startDate' => $start,
            'endDate' => $end,
            'node' => $request->node(),
            'logs' => $report->query($start, $end, $request->node())->paginate(25)->withQueryString(),
        ]);
    }

    /** Ekspor dengan format yang sama dengan berkas import, sehingga bisa diunggah ulang. */
    public function export(PeriodFilterRequest $request, DetectionReportService $report): StreamedResponse
    {
        $start = $request->startDate();
        $end = $request->endDate();
        $query = $report->query($start, $end, $request->node());
        $filename = sprintf('deteksi_%s_%s.csv', $start->format('Ymd'), $end->format('Ymd'));

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['waktu', 'node', 'latitude', 'longitude'], ',', '"', '\\');
            foreach ($query->lazyById(500, 'id') as $log) {
                fputcsv($out, [
                    $log->detected_at->format('Y/m/d H:i:s'),
                    $log->node_name,
                    number_format($log->latitude, 8, '.', ''),
                    number_format($log->longitude, 8, '.', ''),
                ], ',', '"', '\\');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function destroy(DetectionLog $detection): RedirectResponse
    {
        Gate::authorize('delete-detections');

        $detection->delete();

        return back()->with('success', "Data deteksi {$detection->node_name} pada {$detection->detected_at->translatedFormat('d M Y H:i')} dihapus.");
    }
}
