<?php

namespace Tests\Unit;

use App\Enums\TrendStatus;
use App\Services\Detection\TrendPredictor;
use PHPUnit\Framework\TestCase;

class TrendPredictorTest extends TestCase
{
    private TrendPredictor $predictor;

    protected function setUp(): void
    {
        $this->predictor = new TrendPredictor(days: 3, historyDays: 7);
    }

    public function test_data_kurang_dari_dua_hari_tidak_bisa_diprediksi(): void
    {
        $result = $this->predictor->predict(['2026-11-20' => 5]);

        $this->assertSame(TrendStatus::Insufficient, $result->status);
        $this->assertSame([0, 0, 0], array_values($result->forecast));
        $this->assertSame(['2026-11-21', '2026-11-22', '2026-11-23'], array_keys($result->forecast));
    }

    public function test_tren_naik_linear_diproyeksikan_tepat(): void
    {
        $result = $this->predictor->predict(['2026-11-01' => 2, '2026-11-02' => 4, '2026-11-03' => 6]);

        $this->assertSame(TrendStatus::Rising, $result->status);
        $this->assertEqualsWithDelta(2.0, $result->slope, 1e-9);
        $this->assertSame(['2026-11-04' => 8, '2026-11-05' => 10, '2026-11-06' => 12], $result->forecast);
    }

    public function test_tren_turun_tidak_menghasilkan_nilai_negatif(): void
    {
        $result = $this->predictor->predict(['2026-11-01' => 6, '2026-11-02' => 3, '2026-11-03' => 0]);

        $this->assertSame(TrendStatus::Stable, $result->status);
        $this->assertSame([0, 0, 0], array_values($result->forecast));
    }

    public function test_hari_kosong_diisi_nol(): void
    {
        $result = $this->predictor->predict(['2026-11-01' => 4, '2026-11-04' => 4]);

        $this->assertSame(
            ['2026-11-01' => 4, '2026-11-02' => 0, '2026-11-03' => 0, '2026-11-04' => 4],
            $result->history,
        );
    }
}
