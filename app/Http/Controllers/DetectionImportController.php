<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportDetectionCsvRequest;
use App\Models\DetectionImport;
use App\Services\Detection\DetectionCsvImporter;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DetectionImportController extends Controller
{
    /** Riwayat berkas yang pernah diunggah. */
    public function index(): View
    {
        return view('detections.imports', [
            'imports' => DetectionImport::with('user')->latest()->paginate(20),
        ]);
    }

    public function store(ImportDetectionCsvRequest $request, DetectionCsvImporter $importer): RedirectResponse
    {
        $file = $request->file('csv_file');

        try {
            $result = DB::transaction(function () use ($request, $file, $importer) {
                // Sidik jari dicatat lebih dulu: unique index mencegah dua unggahan bersamaan
                // atas berkas yang sama sama-sama diproses.
                $record = DetectionImport::create([
                    'user_id' => $request->user()->id,
                    'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
                    'file_size' => $file->getSize(),
                    'file_hash' => $request->fingerprint(),
                ]);

                $result = $importer->import($file->getRealPath());

                $record->update([
                    'created_count' => $result->created,
                    'duplicate_count' => $result->duplicates,
                    'invalid_count' => count($result->invalidLines),
                ]);

                return $result;
            });
        } catch (UniqueConstraintViolationException) {
            return back()->withErrors(['csv_file' => 'Berkas yang sama baru saja diunggah. Tidak ada data yang diproses.']);
        }

        return back()->with(
            $result->created > 0 ? 'success' : 'warning',
            $result->message(),
        );
    }

    /** Hapus entri riwayat (data deteksi tetap ada) agar berkas yang sama boleh diunggah ulang. */
    public function destroy(DetectionImport $import): RedirectResponse
    {
        Gate::authorize('delete-detections');

        $import->delete();

        return back()->with('success', "Riwayat \"{$import->original_name}\" dihapus. Berkas tersebut kini dapat diunggah ulang.");
    }
}
