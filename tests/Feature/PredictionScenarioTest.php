<?php

namespace Tests\Feature;

use App\Data\TrendPrediction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Uji metode end-to-end memakai CSV dummy di tests/Fixtures/csv.
 * Nilai yang diharapkan (expected.json) dihitung terpisah dari implementasi PHP.
 */
class PredictionScenarioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private const DIR = __DIR__.'/../Fixtures/csv/';

    public static function scenarios(): iterable
    {
        $all = json_decode(file_get_contents(self::DIR.'expected.json'), true);

        foreach ($all as $scenario) {
            if (isset($scenario['status'])) {
                yield $scenario['file'] => [$scenario];
            }
        }
    }

    private function import(string $file): void
    {
        $this->post(route('detections.import'), [
            'csv_file' => new UploadedFile(self::DIR.$file, $file, 'text/csv', null, true),
        ])->assertRedirect(route('dashboard'));
    }

    #[DataProvider('scenarios')]
    public function test_prediksi_sesuai_hasil_hitung_manual(array $expected): void
    {
        $this->import($expected['file']);

        /** @var TrendPrediction $prediction */
        $prediction = $this->get(route('dashboard'))->assertOk()->viewData('prediction');

        $this->assertSame($expected['status'], $prediction->status->value);
        $this->assertEqualsWithDelta($expected['slope'], $prediction->slope, 1e-6);
        $this->assertSame($expected['forecast'], $prediction->forecast);
    }

    public function test_importer_memisahkan_data_valid_duplikat_dan_rusak(): void
    {
        $this->import('09-duplikat-dan-rusak.csv');

        $this->assertDatabaseCount('detection_logs', 4);
        $this->assertSame(
            'Proses selesai. 4 data baru berhasil disimpan. (1 data duplikat otomatis dilewati). 5 baris tidak valid diabaikan (baris 6, 7, 8, 9, 10).',
            session('success'),
        );
    }

    public function test_upload_ulang_file_yang_sama_ditolak(): void
    {
        $this->import('02-tren-naik.csv');

        $this->post(route('detections.import'), [
            'csv_file' => new UploadedFile(self::DIR.'02-tren-naik.csv', 'salinan.csv', 'text/csv', null, true),
        ])->assertSessionHasErrors('csv_file');

        $this->assertDatabaseCount('detection_logs', 164);
    }
}
