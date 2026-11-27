@props(['icon' => 'document', 'title', 'description' => null])

<div {{ $attributes->class('px-6 py-14 text-center') }}>
    <x-icon :name="$icon" class="mx-auto size-10 text-slate-300" />
    <p class="mt-3 text-sm font-semibold text-slate-900">{{ $title }}</p>
    @if ($description)
        <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
    @endif
    @if (! $slot->isEmpty())
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
