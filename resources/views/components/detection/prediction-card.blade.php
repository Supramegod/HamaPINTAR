@props(['prediction'])

<x-card title="Prediksi Tren" icon="sparkles" description="Regresi linear harian" {{ $attributes }}>
    <x-slot:actions>
        <x-badge :tone="$prediction->status->tone()">{{ $prediction->status->label() }}</x-badge>
    </x-slot:actions>

    <p class="text-sm leading-relaxed text-slate-600">{{ $prediction->message() }}</p>

    <dl class="mt-4 grid grid-cols-3 divide-x divide-slate-100 rounded-lg bg-slate-50 ring-1 ring-slate-100">
        @foreach ($prediction->forecast as $date => $value)
            <div class="px-3 py-3 text-center">
                <dt class="text-xs text-slate-500">{{ \Carbon\CarbonImmutable::parse($date)->translatedFormat('d M') }}</dt>
                <dd class="mt-1 text-lg font-semibold text-slate-900">{{ $value }}</dd>
            </div>
        @endforeach
    </dl>
</x-card>
