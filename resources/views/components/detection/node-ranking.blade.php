@props(['stats'])

@php
    $colors = config('hamapintar.nodes');
    $ranking = collect($stats->byNode)->sortDesc();
@endphp

<x-card title="Persentase Kerawanan Node" icon="pin" description="Proporsi deteksi per sensor PIR" {{ $attributes }}>
    <ul class="space-y-4">
        @foreach ($ranking as $node => $count)
            @php($percent = $stats->total > 0 ? round($count / $stats->total * 100, 1) : 0)
            <li>
                <div class="mb-1.5 flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2 font-medium text-slate-700">
                        <span class="size-2.5 rounded-full" style="background-color: {{ $colors[$node] }}"></span>{{ $node }}
                    </span>
                    <span class="tabular-nums text-slate-500">{{ $count }} · <span class="font-semibold text-slate-900">{{ $percent }}%</span></span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full" style="width: {{ $percent }}%; background-color: {{ $colors[$node] }}"></div>
                </div>
            </li>
        @endforeach
    </ul>
</x-card>
