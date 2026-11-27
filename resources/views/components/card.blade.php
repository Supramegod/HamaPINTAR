@props(['title' => null, 'description' => null, 'icon' => null, 'padded' => true])

<section {{ $attributes->class('overflow-hidden rounded-xl bg-white shadow-xs ring-1 ring-slate-200') }}>
    @if ($title)
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div class="flex items-center gap-3">
                @if ($icon)
                    <span class="grid size-8 place-items-center rounded-lg bg-slate-100 text-slate-600">
                        <x-icon :name="$icon" class="size-4" />
                    </span>
                @endif
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">{{ $title }}</h2>
                    @if ($description)
                        <p class="text-xs text-slate-500">{{ $description }}</p>
                    @endif
                </div>
            </div>
            {{ $actions ?? '' }}
        </header>
    @endif

    <div @class(['p-5' => $padded])>
        {{ $slot }}
    </div>
</section>
