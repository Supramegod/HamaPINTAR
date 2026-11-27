@php
    $insufficient = $prediction->status === \App\Enums\TrendStatus::Insufficient;
    $topNode = $stats->topNode();
    $period = request()->only(['start_date', 'end_date']);
@endphp

<x-layouts.app title="Dashboard" description="Ringkasan serangan tikus pada periode {{ $startDate->translatedFormat('d M Y') }} – {{ $endDate->translatedFormat('d M Y') }}.">
    <x-slot:actions>
        <x-button icon="upload" data-open-dialog="upload-dialog">Unggah Data</x-button>
    </x-slot:actions>

    <x-slot:toolbar>
        <x-period-filter :start="$startDate" :end="$endDate" class="self-start" />
    </x-slot:toolbar>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Total Serangan" icon="signal" color="rose"
                :value="number_format($stats->total, 0, ',', '.')" hint="Deteksi pada periode terpilih" />
        <x-stat label="Node Paling Rawan" icon="pin" color="amber"
                :value="$topNode ?? '—'" :hint="$topNode ? $stats->byNode[$topNode].' deteksi' : 'Belum ada data'" />
        <x-stat label="Jam Puncak" icon="clock" color="indigo"
                :value="$stats->peakHour() ?? '—'" :hint="$stats->peakDay() ? 'Hari tersering: '.$stats->peakDay() : 'Belum ada data'" />
        <x-stat label="Tren Serangan" :icon="$prediction->slope > 0 ? 'trend-up' : 'trend-down'"
                :color="match ($prediction->status->tone()) { 'danger' => 'rose', 'success' => 'emerald', default => 'amber' }"
                :value="$prediction->status->label()"
                :hint="$insufficient ? 'Butuh minimal 2 hari data' : sprintf('%+.2f deteksi per hari', $prediction->slope)" />
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <x-card title="Deteksi Terbaru" icon="list" class="xl:col-span-2" :padded="false"
                :description="'Menampilkan '.$recentLogs->count().' dari '.number_format($stats->total, 0, ',', '.').' data'">
            <x-slot:actions>
                <x-button variant="ghost" :href="route('detections.index', $period)">
                    Lihat semua <x-icon name="arrow-right" class="size-4" />
                </x-button>
            </x-slot:actions>

            @include('detections.partials.table', ['logs' => $recentLogs, 'withActions' => false])
        </x-card>

        <div class="flex flex-col gap-6">
            <x-detection.prediction-card :prediction="$prediction" />
            <x-detection.node-ranking :stats="$stats" />
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @foreach ([
            ['map', 'Peta Sebaran', 'Lihat posisi setiap deteksi di lahan sawah.', 'map'],
            ['analytics', 'Analisis Tren', 'Pola per jam, per hari, dan proyeksi ke depan.', 'chart'],
        ] as [$route, $label, $text, $icon])
            <a href="{{ route($route, $period) }}" class="group flex items-center gap-4 rounded-xl bg-white p-5 shadow-xs ring-1 ring-slate-200 transition hover:ring-emerald-300">
                <span class="grid size-11 place-items-center rounded-lg bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100 ring-inset"><x-icon :name="$icon" /></span>
                <span class="flex-1">
                    <span class="block text-sm font-semibold text-slate-900">{{ $label }}</span>
                    <span class="block text-sm text-slate-500">{{ $text }}</span>
                </span>
                <x-icon name="arrow-right" class="size-4 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-emerald-600" />
            </a>
        @endforeach
    </div>
</x-layouts.app>
