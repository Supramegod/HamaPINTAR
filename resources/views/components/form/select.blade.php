@props(['name', 'label' => null, 'options' => [], 'value' => null, 'placeholder' => null])

@php($error = $errors->first($name))

<div>
    @if ($label)
        <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-slate-700">{{ $label }}</label>
    @endif
    <select id="{{ $name }}" name="{{ $name }}"
            {{ $attributes->class([
                'block w-full rounded-lg border-0 bg-white py-2 pr-8 pl-3 text-sm text-slate-900 shadow-xs ring-1 ring-inset focus:ring-2 focus:outline-none disabled:bg-slate-50 disabled:text-slate-500',
                'ring-rose-300 focus:ring-rose-500' => $error,
                'ring-slate-300 focus:ring-emerald-600' => ! $error,
            ]) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) old($name, $value) === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if ($error)
        <p class="mt-1.5 text-xs text-rose-600">{{ $error }}</p>
    @endif
</div>
