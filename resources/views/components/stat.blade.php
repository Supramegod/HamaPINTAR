@props(['label', 'value', 'hint' => null, 'icon', 'color' => 'emerald'])

@php
    $badge = [
        'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
        'rose' => 'bg-rose-50 text-rose-600 ring-rose-100',
        'amber' => 'bg-amber-50 text-amber-600 ring-amber-100',
        'indigo' => 'bg-indigo-50 text-indigo-600 ring-indigo-100',
    ][$color];
@endphp

<div class="flex items-start justify-between gap-4 rounded-xl bg-white p-5 shadow-xs ring-1 ring-slate-200">
    <div class="min-w-0">
        <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
        <p class="mt-2 truncate text-2xl font-semibold tracking-tight text-slate-900">{{ $value }}</p>
        @if ($hint)
            <p class="mt-1 truncate text-xs text-slate-500">{{ $hint }}</p>
        @endif
    </div>
    <span class="grid size-10 shrink-0 place-items-center rounded-lg ring-1 ring-inset {{ $badge }}">
        <x-icon :name="$icon" />
    </span>
</div>
