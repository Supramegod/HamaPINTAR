<?php

namespace Tests\Feature;

use App\Models\DetectionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_dashboard_kosong_tetap_tampil(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ringkasan serangan tikus')
            ->assertSee('Membutuhkan lebih banyak variasi data log')
            ->assertSee('Belum ada data deteksi');
    }

    public function test_filter_tanggal_membatasi_data(): void
    {
        DetectionLog::factory()->create(['node_name' => 'PIR 1', 'detected_at' => '2026-11-20 18:15:00']);
        DetectionLog::factory()->create(['node_name' => 'PIR 3', 'detected_at' => '2026-11-23 02:00:00']);
        DetectionLog::factory()->create(['node_name' => 'PIR 3', 'detected_at' => '2026-10-01 02:00:00']);

        $response = $this->get(route('dashboard', ['start_date' => '2026-11-01', 'end_date' => '2026-11-30']))->assertOk();

        $stats = $response->viewData('stats');
        $this->assertSame(2, $stats->total);
        $this->assertSame(['PIR 1' => 1, 'PIR 2' => 0, 'PIR 3' => 1, 'PIR 4' => 0, 'PIR 5' => 0], $stats->byNode);
        $this->assertCount(2, $response->viewData('recentLogs'));
    }

    public function test_tanggal_akhir_sebelum_tanggal_mulai_ditolak(): void
    {
        $this->get(route('dashboard', ['start_date' => '2026-11-30', 'end_date' => '2026-11-01']))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('end_date');
    }
}
