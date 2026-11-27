@props(['tone' => 'info', 'dismissible' => false])

@php
    [$classes, $icon] = match ($tone) {
        'success' => ['bg-emerald-50 text-emerald-800 ring-emerald-200', 'check'],
        'warning' => ['bg-amber-50 text-amber-800 ring-amber-200', 'warning'],
        'danger' => ['bg-rose-50 text-rose-800 ring-rose-200', 'warning'],
        default => ['bg-sky-50 text-sky-800 ring-sky-200', 'info'],
    };
@endphp

<div role="alert" data-alert {{ $attributes->class(['flex items-start gap-3 rounded-xl p-4 text-sm ring-1 ring-inset', $classes]) }}>
    <x-icon :name="$icon" class="mt-0.5 size-5" />
    <div class="flex-1">{{ $slot }}</div>
    @if ($dismissible)
        <button type="button" data-dismiss-alert aria-label="Tutup" class="rounded-md p-0.5 opacity-60 hover:opacity-100">
            <x-icon name="x" class="size-4" />
        </button>
    @endif
</div>
