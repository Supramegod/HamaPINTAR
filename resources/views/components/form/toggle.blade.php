@props(['name', 'label', 'description' => null, 'checked' => false, 'disabled' => false])

<label @class(['flex items-start justify-between gap-4', 'cursor-pointer' => ! $disabled, 'cursor-not-allowed opacity-60' => $disabled])>
    <span>
        <span class="block text-sm font-medium text-slate-700">{{ $label }}</span>
        @if ($description)
            <span class="block text-xs text-slate-500">{{ $description }}</span>
        @endif
    </span>
    <span class="relative inline-flex shrink-0">
        <input type="hidden" name="{{ $name }}" value="0" @disabled($disabled)>
        <input type="checkbox" name="{{ $name }}" value="1" class="peer sr-only" @checked(old($name, $checked)) @disabled($disabled)>
        <span class="h-6 w-11 rounded-full bg-slate-200 transition peer-checked:bg-emerald-600 peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-600 peer-focus-visible:ring-offset-2"></span>
        <span class="absolute top-0.5 left-0.5 size-5 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5"></span>
    </span>
</label>
