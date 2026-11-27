<?php

namespace Tests\Feature;

use App\Models\DetectionLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MapTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_tampil_tanpa_data(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Pola Serangan Tikus di Sawah')
            ->assertSee('Membutuhkan lebih banyak variasi data log');
    }

    public function test_upload_csv_menyimpan_data_dan_melewati_duplikat(): void
    {
        $csv = "waktu,node,lat,lng\n"
            ."2026/11/20 18:15:00,PIR 1,-7.252,112.7944\n"
            ."2026/11/21 20:05:00,PIR 2,-7.2521,112.7946\n";

        $this->post('/upload-csv', ['csv_file' => UploadedFile::fake()->createWithContent('a.csv', $csv)])
            ->assertRedirect('/')
            ->assertSessionHas('success', 'Proses selesai. 2 data baru berhasil disimpan.');

        $this->post('/upload-csv', ['csv_file' => UploadedFile::fake()->createWithContent('a.csv', $csv)])
            ->assertSessionHas('success', 'Proses selesai. 0 data baru berhasil disimpan. (2 data duplikat otomatis dilewati).');

        $this->assertSame(2, DetectionLog::count());
    }

    public function test_filter_tanggal_dan_agregasi_grafik(): void
    {
        DetectionLog::factory()->create(['node_name' => 'PIR 1', 'detected_at' => '2026-11-20 18:15:00']); // Jumat
        DetectionLog::factory()->create(['node_name' => 'PIR 3', 'detected_at' => '2026-11-23 02:00:00']); // Senin
        DetectionLog::factory()->create(['node_name' => 'PIR 3', 'detected_at' => '2026-10-01 02:00:00']); // di luar filter

        $this->get('/?start_date=2026-11-01&end_date=2026-11-30')
            ->assertOk()
            ->assertViewHas('totalSerangan', 2)
            ->assertViewHas('chartJamData', [1, 0, 0, 0, 1, 0, 0])
            ->assertViewHas('chartHariData', [1, 0, 0, 0, 1, 0, 0])
            ->assertViewHas('chartNodeData', [1, 0, 1, 0, 0]);
    }
}
