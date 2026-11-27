<?php

namespace Tests\Feature;

use App\Models\DetectionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DetectionImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function upload(string $csv)
    {
        return $this->post(route('detections.import'), [
            'csv_file' => UploadedFile::fake()->createWithContent('data.csv', $csv),
        ]);
    }

    public function test_import_menyimpan_data_dan_melewati_duplikat(): void
    {
        $csv = "waktu,node,lat,lng\n"
            ."2026/11/20 18:15:00,PIR 1,-7.252,112.7944\n"
            ."2026/11/21 20:05:00,PIR 2,-7.2521,112.7946\n";

        $this->upload($csv)
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', 'Proses selesai. 2 data baru berhasil disimpan.');

        // Berkas berbeda (header lain) tetapi barisnya sama → diproses, semua baris dilewati
        $this->upload(str_replace('waktu,node,lat,lng', 'Waktu,Node,Latitude,Longitude', $csv))
            ->assertSessionHas('warning', 'Proses selesai. 0 data baru berhasil disimpan. (2 data duplikat otomatis dilewati).');

        $this->assertSame(2, DetectionLog::count());
    }

    public function test_duplikat_di_dalam_satu_file_dan_baris_rusak(): void
    {
        $csv = "waktu,node,lat,lng\n"
            ."2026-11-20 18:15:00,PIR 1,-7.252,112.7944\n"
            ."2026-11-20 18:15:00,PIR 1,-7.252,112.7944\n"
            ."bukan-tanggal,PIR 1,-7.252,112.7944\n"
            ."2026-11-20 19:00:00,PIR 2,abc,112.7944\n"
            ."2026-11-20 19:00:00,PIR 2\n";

        $this->upload($csv)->assertSessionHas(
            'success',
            'Proses selesai. 1 data baru berhasil disimpan. (1 data duplikat otomatis dilewati). 3 baris tidak valid diabaikan (baris 4, 5, 6).',
        );
    }

    public function test_file_wajib_dan_harus_csv(): void
    {
        $this->post(route('detections.import'))->assertSessionHasErrors('csv_file');

        $this->post(route('detections.import'), ['csv_file' => UploadedFile::fake()->create('foto.png', 10, 'image/png')])
            ->assertSessionHasErrors('csv_file');
    }
}
