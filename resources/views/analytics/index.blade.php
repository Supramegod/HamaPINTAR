<x-layouts.app title="Analisis Tren" description="Pola aktivitas berdasarkan jam, hari, dan proyeksi beberapa hari ke depan.">
    <x-slot:toolbar>
        <x-period-filter :start="$startDate" :end="$endDate" class="self-start" />
    </x-slot:toolbar>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-chart-card title="Prediksi Tren Hari Berikutnya" chart="prediction" icon="sparkles" height="h-80" class="lg:col-span-2"
                      description="Aktual 7 hari terakhir dan proyeksi (garis putus-putus). Memakai seluruh riwayat data." />
        <x-detection.prediction-card :prediction="$prediction" />

        <x-chart-card title="Harian (Jam)" description="Jumlah deteksi per slot 2 jam pada periode terpilih" chart="hour" icon="clock" class="lg:col-span-2" />
        <div class="flex flex-col gap-6">
            <x-stat label="Jam Puncak" icon="clock" color="indigo" :value="$stats->peakHour() ?? '—'" hint="Slot dengan deteksi terbanyak" />
            <x-stat label="Hari Tersering" icon="calendar" color="emerald" :value="$stats->peakDay() ?? '—'" hint="Hari dengan deteksi terbanyak" />
            <x-stat label="Total Serangan" icon="signal" color="rose" :value="number_format($stats->total, 0, ',', '.')" hint="Pada periode terpilih" />
        </div>

        <x-chart-card title="Mingguan (Hari)" description="Jumlah deteksi per hari dalam seminggu" chart="day" icon="calendar" class="lg:col-span-2" />
        <x-detection.node-ranking :stats="$stats" />
    </div>

    <script id="page-data" type="application/json">@json($payload)</script>
</x-layouts.app>
