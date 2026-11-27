@props(['user', 'size' => 'size-9'])

<span {{ $attributes->class([$size, 'grid shrink-0 place-items-center rounded-full bg-emerald-100 text-xs font-semibold text-emerald-700 ring-2 ring-white']) }}>
    {{ $user->initials() }}
</span>
