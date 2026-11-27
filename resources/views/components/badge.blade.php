@props(['tone' => 'neutral'])

@php
    $tones = [
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'danger' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        'neutral' => 'bg-slate-50 text-slate-600 ring-slate-500/20',
    ];
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset', $tones[$tone] ?? $tones['neutral']]) }}>{{ $slot }}</span>
