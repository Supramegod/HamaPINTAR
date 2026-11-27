<?php

namespace Tests\Unit;

use App\Services\Detection\DetectionAnalyzer;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class DetectionAnalyzerTest extends TestCase
{
    public function test_mengelompokkan_per_slot_jam_hari_dan_node(): void
    {
        $analyzer = new DetectionAnalyzer(
            nodes: ['PIR 1' => '#000', 'PIR 2' => '#000'],
            hourSlots: [18, 20, 22, 0, 2, 4, 6],
        );

        $log = fn (string $node, string $at) => (object) ['node_name' => $node, 'detected_at' => CarbonImmutable::parse($at)];

        $stats = $analyzer->analyze([
            $log('PIR 1', '2026-11-20 18:15'), // Jumat, slot 18
            $log('PIR 1', '2026-11-20 19:59'), // Jumat, slot 18
            $log('PIR 2', '2026-11-23 01:30'), // Senin, slot 00
            $log('PIR 9', '2026-11-23 12:00'), // node & jam di luar daftar: hanya dihitung total & hari
        ]);

        $this->assertSame(4, $stats->total);
        $this->assertSame(['18:00' => 2, '20:00' => 0, '22:00' => 0, '00:00' => 1, '02:00' => 0, '04:00' => 0, '06:00' => 0], $stats->byHour);
        $this->assertSame(['Sen' => 2, 'Sel' => 0, 'Rab' => 0, 'Kam' => 0, 'Jum' => 2, 'Sab' => 0, 'Min' => 0], $stats->byDay);
        $this->assertSame(['PIR 1' => 2, 'PIR 2' => 1], $stats->byNode);
        $this->assertSame('18:00', $stats->peakHour());
        $this->assertSame('PIR 1', $stats->topNode());
    }

    public function test_tanpa_data_tidak_ada_puncak(): void
    {
        $stats = (new DetectionAnalyzer(['PIR 1' => '#000'], [18]))->analyze([]);

        $this->assertSame(0, $stats->total);
        $this->assertNull($stats->peakHour());
        $this->assertNull($stats->topNode());
    }
}
