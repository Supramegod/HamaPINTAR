<x-layouts.app title="Peta Sebaran" description="Posisi setiap deteksi tikus berdasarkan koordinat sensor PIR.">
    <x-slot:toolbar>
        <x-period-filter :start="$startDate" :end="$endDate" class="self-start" />
    </x-slot:toolbar>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-4">
        <x-card title="Peta Sebaran Deteksi" icon="map" :padded="false" class="xl:col-span-3"
                :description="number_format($stats->total, 0, ',', '.').' titik pada periode terpilih'">
            <div id="map" class="h-[480px] w-full md:h-[600px] xl:h-[calc(100vh-17rem)] xl:min-h-[560px]"></div>
        </x-card>

        <div class="flex flex-col gap-6">
            <x-card title="Legenda Node" icon="pin">
                <ul class="space-y-3">
                    @foreach (config('hamapintar.nodes') as $node => $color)
                        <li class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2.5 font-medium text-slate-700">
                                <span class="size-3 rounded-full shadow ring-2 ring-white" style="background-color: {{ $color }}"></span>{{ $node }}
                            </span>
                            <span class="tabular-nums text-slate-500">{{ $stats->byNode[$node] }} titik</span>
                        </li>
                    @endforeach
                </ul>
            </x-card>

            <x-card title="Ringkasan" icon="info">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Total titik</dt><dd class="font-semibold text-slate-900">{{ number_format($stats->total, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Node paling rawan</dt><dd class="font-semibold text-slate-900">{{ $stats->topNode() ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Jam puncak</dt><dd class="font-semibold text-slate-900">{{ $stats->peakHour() ?? '—' }}</dd></div>
                </dl>
                <p class="mt-4 text-xs text-slate-500">Klik titik pada peta untuk melihat node dan waktu deteksi.</p>
            </x-card>
        </div>
    </div>

    <script id="page-data" type="application/json">@json($payload)</script>
</x-layouts.app>
