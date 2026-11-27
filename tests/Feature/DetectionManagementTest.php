<?php

namespace Tests\Feature;

use App\Models\DetectionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DetectionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_node_dan_paginasi(): void
    {
        $this->actingAs(User::factory()->create());
        DetectionLog::factory()->count(30)->create(['node_name' => 'PIR 1']);
        DetectionLog::factory()->count(3)->create(['node_name' => 'PIR 2']);

        $this->get(route('detections.index', ['node' => 'PIR 2']))
            ->assertOk()
            ->assertViewHas('logs', fn ($logs) => $logs->total() === 3);

        $this->get(route('detections.index'))
            ->assertViewHas('logs', fn ($logs) => $logs->total() === 33 && $logs->count() === 25);

        $this->get(route('detections.index', ['node' => 'PIR 99']))->assertSessionHasErrors('node');
    }

    public function test_ekspor_csv_dapat_diimpor_ulang(): void
    {
        $this->actingAs(User::factory()->create());
        DetectionLog::factory()->create(['node_name' => 'PIR 3', 'detected_at' => now()->subDay()->setTime(19, 5, 7), 'latitude' => -7.2521, 'longitude' => 112.7944]);
        DetectionLog::factory()->count(4)->create();

        $response = $this->get(route('detections.export'));
        $response->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();
        $rows = array_map(fn ($line) => str_getcsv($line, ',', '"', '\\'), array_filter(explode("\n", $csv)));
        $this->assertSame(['waktu', 'node', 'latitude', 'longitude'], $rows[0]);
        $this->assertContains([now()->subDay()->format('Y/m/d').' 19:05:07', 'PIR 3', '-7.25210000', '112.79440000'], $rows);

        // Round-trip: hasil ekspor bisa diunggah ulang tanpa kehilangan data
        DetectionLog::query()->delete();
        $this->post(route('detections.import'), [
            'csv_file' => UploadedFile::fake()->createWithContent('ekspor.csv', $csv),
        ])->assertSessionHas('success', 'Proses selesai. 5 data baru berhasil disimpan.');
    }

    public function test_hanya_admin_yang_bisa_menghapus_data(): void
    {
        $log = DetectionLog::factory()->create();

        $this->actingAs(User::factory()->create())->delete(route('detections.destroy', $log))->assertForbidden();
        $this->assertModelExists($log);

        $this->actingAs(User::factory()->admin()->create())->delete(route('detections.destroy', $log))->assertRedirect();
        $this->assertModelMissing($log);
    }
}
