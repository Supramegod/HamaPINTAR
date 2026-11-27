<?php

namespace App\Enums;

enum TrendStatus: string
{
    case Rising = 'rising';
    case Stable = 'stable';
    case Insufficient = 'insufficient';

    public function label(): string
    {
        return match ($this) {
            self::Rising => 'Meningkat',
            self::Stable => 'Menurun / Stabil',
            self::Insufficient => 'Data Kurang',
        };
    }

    public function message(float $slope): string
    {
        return match ($this) {
            self::Rising => 'PERINGATAN: Tren grafik menunjukkan kurva MENINGKAT sebesar '.round($slope, 2)
                .' poin per hari. Disarankan memperketat jebakan di area titik merah.',
            self::Stable => 'INFO KONDUSIF: Tren kurva menurun/stabil. Skema mitigasi di area persawahan berjalan efektif.',
            self::Insufficient => 'Membutuhkan lebih banyak variasi data log untuk memproyeksikan tren.',
        };
    }

    /** Nama tone warna untuk komponen UI (lihat components/alert). */
    public function tone(): string
    {
        return match ($this) {
            self::Rising => 'danger',
            self::Stable => 'success',
            self::Insufficient => 'warning',
        };
    }
}
