<?php

namespace Tests\Feature;

use App\Models\DetectionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['name' => 'Petugas Satu']));
        DetectionLog::factory()->count(5)->create();
    }

    public function test_semua_halaman_monitoring_tampil_dengan_navigasi_aktif(): void
    {
        foreach (['dashboard' => 'Dashboard', 'map' => 'Peta Sebaran', 'analytics' => 'Analisis Tren', 'detections.index' => 'Data Deteksi'] as $route => $title) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee("<title>{$title}", false)
                ->assertSee('aria-current="page"', false)
                ->assertSee('Petugas Satu');
        }
    }

    public function test_menu_pengguna_tidak_tampil_untuk_petugas(): void
    {
        $this->get(route('dashboard'))->assertDontSee(route('users.index'));
    }

    public function test_menu_pengguna_tampil_untuk_admin(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('dashboard'))
            ->assertSee(route('users.index'));
    }

    public function test_periode_filter_terbawa_di_link_sidebar(): void
    {
        $this->get(route('map', ['start_date' => '2026-11-01', 'end_date' => '2026-11-20']))
            ->assertSee(route('analytics', ['start_date' => '2026-11-01', 'end_date' => '2026-11-20']));
    }

    public function test_peta_dan_analisis_mengirim_payload_js(): void
    {
        $this->get(route('map'))->assertSee('id="page-data"', false)->assertSee('"points"', false);
        $this->get(route('analytics'))->assertSee('id="page-data"', false)->assertSee('"charts"', false);
    }
}
