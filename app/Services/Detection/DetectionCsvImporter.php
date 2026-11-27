<?php

namespace App\Services\Detection;

use App\Data\ImportResult;
use App\Models\DetectionLog;
use Carbon\CarbonImmutable;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\DB;
use SplFileObject;
use Throwable;

/**
 * Import CSV dari alat. Format per baris (baris pertama = header):
 *   waktu (Y/m/d H:i:s atau Y-m-d H:i:s), node, latitude, longitude
 *
 * Duplikat (node + waktu sama) ditolak oleh unique index lewat insertOrIgnore,
 * baik terhadap data di database maupun di dalam file itu sendiri.
 */
final class DetectionCsvImporter
{
    public function __construct(
        #[Config('hamapintar.import.chunk_size')] private readonly int $chunkSize = 500,
    ) {}

    public function import(string $path): ImportResult
    {
        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl(',', '"', '\\');

        $rows = [];
        $invalidLines = [];
        $now = now();

        foreach ($file as $index => $columns) {
            if ($index === 0) {
                continue; // header
            }

            $row = $this->parseRow($columns);
            if ($row === null) {
                $invalidLines[] = $index + 1;

                continue;
            }

            $rows[] = $row + ['created_at' => $now, 'updated_at' => $now];
        }

        $created = DB::transaction(function () use ($rows): int {
            $inserted = 0;
            foreach (array_chunk($rows, $this->chunkSize) as $chunk) {
                $inserted += DetectionLog::query()->insertOrIgnore($chunk);
            }

            return $inserted;
        });

        return new ImportResult(
            created: $created,
            duplicates: count($rows) - $created,
            invalidLines: $invalidLines,
        );
    }

    /**
     * @param  array<int, string|null>  $columns
     * @return array{node_name: string, detected_at: string, latitude: float, longitude: float}|null
     */
    private function parseRow(array $columns): ?array
    {
        if (count($columns) < 4) {
            return null;
        }

        [$rawTime, $node, $lat, $lng] = array_map(fn ($v) => trim((string) $v), array_slice($columns, 0, 4));

        if ($rawTime === '' || $node === '' || ! is_numeric($lat) || ! is_numeric($lng)) {
            return null;
        }

        if (abs((float) $lat) > 90 || abs((float) $lng) > 180) {
            return null;
        }

        try {
            $detectedAt = CarbonImmutable::parse(str_replace('/', '-', $rawTime));
        } catch (Throwable) {
            return null;
        }

        return [
            'node_name' => mb_substr($node, 0, 50),
            'detected_at' => $detectedAt->format('Y-m-d H:i:s'),
            'latitude' => (float) $lat,
            'longitude' => (float) $lng,
        ];
    }
}
