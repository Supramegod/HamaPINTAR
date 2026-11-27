<?php

namespace App\Services\Detection;

/**
 * Sidik jari isi berkas CSV untuk mendeteksi unggahan ganda.
 *
 * Dihitung dari isi (bukan nama berkas) setelah dinormalisasi, sehingga berkas yang
 * sama tetap dikenali walau di-rename, disimpan ulang di Windows (CRLF), atau ber-BOM.
 */
final class CsvFingerprint
{
    public static function of(string $path): string
    {
        return self::ofContent((string) file_get_contents($path));
    }

    public static function ofContent(string $content): string
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);  // UTF-8 BOM
        $content = str_replace(["\r\n", "\r"], "\n", $content);     // akhir baris Windows / Mac lama
        $lines = array_map('rtrim', explode("\n", $content));        // spasi di ujung baris
        $content = rtrim(implode("\n", $lines), "\n");                // baris kosong di akhir berkas

        return hash('sha256', $content);
    }
}
