@php($filters = array_filter(request()->only(['start_date', 'end_date', 'node'])))

<x-layouts.app title="Data Deteksi" description="Seluruh catatan deteksi dari sensor PIR. Unggah data alat atau ekspor untuk diolah lebih lanjut.">
    <x-slot:actions>
        <x-button variant="secondary" icon="download" :href="route('detections.export', $filters)">Ekspor CSV</x-button>
        <x-button icon="upload" data-open-dialog="upload-dialog">Unggah Data</x-button>
    </x-slot:actions>

    <x-slot:toolbar>
        <x-period-filter :start="$startDate" :end="$endDate" :node="$node" with-node class="self-start" />
    </x-slot:toolbar>

    <x-card title="Catatan Deteksi" icon="list" :padded="false"
            :description="number_format($logs->total(), 0, ',', '.').' data'.($node ? ' untuk '.$node : '').' pada periode terpilih'">
        @include('detections.partials.table', ['logs' => $logs, 'withActions' => true])
        {{ $logs->links() }}
    </x-card>
</x-layouts.app>
