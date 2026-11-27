<?php

namespace Tests\Unit;

use App\Services\Detection\CsvFingerprint;
use PHPUnit\Framework\TestCase;

class CsvFingerprintTest extends TestCase
{
    private const CSV = "waktu,node,latitude,longitude\n2026/11/20 18:15:00,PIR 1,-7.252,112.7944\n";

    public function test_isi_sama_menghasilkan_sidik_jari_sama_walau_format_akhir_baris_berbeda(): void
    {
        $base = CsvFingerprint::ofContent(self::CSV);

        $this->assertSame($base, CsvFingerprint::ofContent(str_replace("\n", "\r\n", self::CSV))); // Windows
        $this->assertSame($base, CsvFingerprint::ofContent("\xEF\xBB\xBF".self::CSV));             // BOM Excel
        $this->assertSame($base, CsvFingerprint::ofContent(rtrim(self::CSV)));                      // tanpa newline akhir
        $this->assertSame($base, CsvFingerprint::ofContent(self::CSV."\n\n"));                      // baris kosong di akhir
        $this->assertSame($base, CsvFingerprint::ofContent(str_replace("\n", "  \n", self::CSV)));  // spasi di ujung baris
    }

    public function test_perubahan_isi_menghasilkan_sidik_jari_berbeda(): void
    {
        $base = CsvFingerprint::ofContent(self::CSV);

        $this->assertNotSame($base, CsvFingerprint::ofContent(str_replace('PIR 1', 'PIR 2', self::CSV)));
        $this->assertNotSame($base, CsvFingerprint::ofContent(self::CSV."2026/11/21 19:00:00,PIR 3,-7.25,112.79\n"));
    }
}
