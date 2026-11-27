<div class="space-y-3 empty:hidden">
    @foreach (['success', 'warning'] as $tone)
        @if (session($tone))
            <x-alert :tone="$tone" dismissible>{{ session($tone) }}</x-alert>
        @endif
    @endforeach

    @if ($errors->any())
        <x-alert tone="danger" dismissible>
            <p class="font-semibold">Permintaan tidak dapat diproses</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif
</div>
