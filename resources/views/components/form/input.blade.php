@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'bag' => 'default'])

@php($error = $errors->getBag($bag)->first($name))

<div>
    <label for="{{ $attributes->get('id', $name) }}" class="mb-1.5 block text-sm font-medium text-slate-700">{{ $label }}</label>
    <input id="{{ $attributes->get('id', $name) }}" name="{{ $name }}" type="{{ $type }}"
           @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
           @if ($error) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
           {{ $attributes->except('id')->class([
               'block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-xs ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:outline-none disabled:bg-slate-50 disabled:text-slate-500',
               'ring-rose-300 focus:ring-rose-500' => $error,
               'ring-slate-300 focus:ring-emerald-600' => ! $error,
           ]) }}>
    @if ($error)
        <p id="{{ $name }}-error" class="mt-1.5 text-xs text-rose-600">{{ $error }}</p>
    @elseif ($hint)
        <p class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
