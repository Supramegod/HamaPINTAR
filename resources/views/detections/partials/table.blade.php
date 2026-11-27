{{-- Dipakai di Dashboard (tanpa aksi) dan halaman Data Deteksi (dengan aksi hapus untuk admin). --}}
@php($canDelete = ($withActions ?? false) && auth()->user()->can('delete-detections'))

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <th scope="col" class="px-5 py-3">Waktu Deteksi</th>
                <th scope="col" class="px-5 py-3">Node</th>
                <th scope="col" class="px-5 py-3 text-right">Latitude</th>
                <th scope="col" class="px-5 py-3 text-right">Longitude</th>
                @if ($canDelete)
                    <th scope="col" class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
            @forelse ($logs as $log)
                <tr class="transition hover:bg-slate-50">
                    <td class="whitespace-nowrap px-5 py-3">
                        <span class="font-medium text-slate-900">{{ $log->detected_at->translatedFormat('d M Y') }}</span>
                        <span class="ml-1 text-slate-500">{{ $log->detected_at->format('H:i:s') }}</span>
                    </td>
                    <td class="whitespace-nowrap px-5 py-3"><x-detection.node-badge :node="$log->node_name" /></td>
                    <td class="whitespace-nowrap px-5 py-3 text-right font-mono text-xs tabular-nums text-slate-600">{{ number_format($log->latitude, 6) }}</td>
                    <td class="whitespace-nowrap px-5 py-3 text-right font-mono text-xs tabular-nums text-slate-600">{{ number_format($log->longitude, 6) }}</td>
                    @if ($canDelete)
                        <td class="whitespace-nowrap px-5 py-3 text-right">
                            <form method="POST" action="{{ route('detections.destroy', $log) }}"
                                  data-confirm="Hapus data deteksi {{ $log->node_name }} pada {{ $log->detected_at->translatedFormat('d M Y H:i') }}?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-md p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" aria-label="Hapus">
                                    <x-icon name="trash" class="size-4" />
                                </button>
                            </form>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $canDelete ? 5 : 4 }}">
                        <x-empty-state title="Belum ada data deteksi" description="Tidak ada data pada periode ini. Unggah berkas CSV dari alat untuk mulai memantau.">
                            <x-button icon="upload" data-open-dialog="upload-dialog">Unggah Data</x-button>
                        </x-empty-state>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
