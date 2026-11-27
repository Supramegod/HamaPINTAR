<?php

namespace Tests\Feature;

use App\Models\DetectionImport;
use App\Models\DetectionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DuplicateFileUploadTest extends TestCase
{
    use RefreshDatabase;

    private const CSV = "waktu,node,latitude,longitude\n"
        ."2026/11/20 18:15:00,PIR 1,-7.252,112.7944\n"
        ."2026/11/21 20:05:00,PIR 2,-7.2521,112.7946\n"
        ."rusak,PIR 3,-7.25,112.79\n";

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['name' => 'Siti Petugas']);
        $this->actingAs($this->user);
    }

    private function upload(string $content, string $name = 'data-alat.csv')
    {
        return $this->post(route('detections.import'), [
            'csv_file' => UploadedFile::fake()->createWithContent($name, $content),
        ]);
    }

    public function test_unggahan_pertama_tercatat_di_riwayat(): void
    {
        $this->upload(self::CSV)->assertSessionHasNoErrors();

        $import = DetectionImport::sole();
        $this->assertSame('data-alat.csv', $import->original_name);
        $this->assertTrue($import->user->is($this->user));
        $this->assertSame([2, 0, 1], [$import->created_count, $import->duplicate_count, $import->invalid_count]);
    }

    public function test_berkas_sama_ditolak_walau_namanya_berbeda_dan_tersimpan_di_windows(): void
    {
        $this->upload(self::CSV)->assertSessionHasNoErrors();

        $this->upload(str_replace("\n", "\r\n", self::CSV), 'salinan (1).csv')
            ->assertSessionHasErrors('csv_file');

        $message = session('errors')->first('csv_file');
        $this->assertStringContainsString('sudah pernah diunggah', $message);
        $this->assertStringContainsString('Siti Petugas', $message);
        $this->assertStringContainsString('"data-alat.csv"', $message);

        $this->assertSame(1, DetectionImport::count());
        $this->assertSame(2, DetectionLog::count());
    }

    public function test_berkas_berbeda_dengan_sebagian_baris_sama_tetap_diproses(): void
    {
        $this->upload(self::CSV);

        $this->upload(self::CSV."2026/11/22 02:30:00,PIR 4,-7.2523,112.7941\n", 'data-alat-lanjutan.csv')
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Proses selesai. 1 data baru berhasil disimpan. (2 data duplikat otomatis dilewati). 1 baris tidak valid diabaikan (baris 4).');

        $this->assertSame(2, DetectionImport::count());
        $this->assertSame(3, DetectionLog::count());
    }

    public function test_halaman_riwayat_menampilkan_berkas(): void
    {
        $this->upload(self::CSV);

        $this->get(route('detections.imports.index'))
            ->assertOk()
            ->assertSee('data-alat.csv')
            ->assertSee('Siti Petugas')
            ->assertDontSee(route('detections.imports.destroy', DetectionImport::sole())); // petugas tidak bisa hapus
    }

    public function test_admin_menghapus_riwayat_agar_berkas_bisa_diunggah_ulang(): void
    {
        $this->upload(self::CSV);
        $import = DetectionImport::sole();

        $this->delete(route('detections.imports.destroy', $import))->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('detections.imports.destroy', $import))
            ->assertSessionHas('success');

        $this->assertModelMissing($import);
        $this->assertSame(2, DetectionLog::count(), 'data deteksi tidak ikut terhapus');

        $this->upload(self::CSV)->assertSessionHasNoErrors();
    }

    public function test_riwayat_tetap_ada_saat_pengunggah_dihapus(): void
    {
        $this->upload(self::CSV);
        $this->user->delete();

        $this->assertNull(DetectionImport::sole()->user_id);
        $this->assertSame('pengguna yang telah dihapus', DetectionImport::sole()->uploaderName());
    }
}
