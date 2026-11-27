@props(['variant' => 'primary', 'icon' => null, 'href' => null])

@php
    $classes = [
        'inline-flex items-center justify-center gap-2 rounded-lg px-3.5 py-2 text-sm font-semibold shadow-xs transition focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-wait disabled:opacity-60',
        match ($variant) {
            'primary' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus-visible:outline-emerald-600',
            'secondary' => 'bg-white text-slate-700 ring-1 ring-slate-300 ring-inset hover:bg-slate-50 focus-visible:outline-slate-400',
            'ghost' => 'text-slate-600 shadow-none hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-slate-400',
        },
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)<x-icon :name="$icon" class="size-4" />@endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button'])->class($classes) }}>
        @if ($icon)<x-icon :name="$icon" class="size-4" />@endif
        {{ $slot }}
    </button>
@endif
