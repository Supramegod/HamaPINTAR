@props(['start', 'end', 'node' => null, 'withNode' => false])

@php
    $presets = [7 => '7 hari', 30 => '30 hari', 90 => '90 hari'];
    $today = now()->toDateString();
    $activeDays = $end->isToday() ? (int) $start->diffInDays($end) : null;
    $inputClass = 'block rounded-lg border-0 bg-white px-3 py-1.5 text-sm text-slate-900 shadow-xs ring-1 ring-slate-300 ring-inset focus:ring-2 focus:ring-emerald-600 focus:outline-none';
@endphp

<form action="{{ url()->current() }}" method="GET"
      {{ $attributes->class('flex flex-wrap items-end gap-3 rounded-xl bg-white p-3 shadow-xs ring-1 ring-slate-200') }}>
    <div class="inline-flex rounded-lg bg-slate-100 p-1" role="group" aria-label="Rentang cepat">
        @foreach ($presets as $days => $label)
            <a href="{{ url()->current().'?'.http_build_query(array_filter(['start_date' => now()->subDays($days)->toDateString(), 'end_date' => $today, 'node' => $node])) }}"
               @class([
                   'rounded-md px-2.5 py-1 text-xs font-semibold whitespace-nowrap transition',
                   'bg-white text-slate-900 shadow-xs' => $activeDays === $days,
                   'text-slate-500 hover:text-slate-900' => $activeDays !== $days,
               ])>{{ $label }}</a>
        @endforeach
    </div>

    <label class="block">
        <span class="mb-1 block text-xs font-medium text-slate-500">Mulai dari</span>
        <input type="date" name="start_date" value="{{ $start->toDateString() }}" max="{{ $today }}" class="{{ $inputClass }}">
    </label>
    <label class="block">
        <span class="mb-1 block text-xs font-medium text-slate-500">Sampai dengan</span>
        <input type="date" name="end_date" value="{{ $end->toDateString() }}" class="{{ $inputClass }}">
    </label>

    @if ($withNode)
        <label class="block">
            <span class="mb-1 block text-xs font-medium text-slate-500">Node</span>
            <select name="node" class="{{ $inputClass }} pr-8">
                <option value="">Semua node</option>
                @foreach (array_keys(config('hamapintar.nodes')) as $option)
                    <option value="{{ $option }}" @selected($node === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </label>
    @endif

    <x-button type="submit" variant="secondary" icon="funnel">Terapkan</x-button>
</form>
