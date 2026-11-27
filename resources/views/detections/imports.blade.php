<x-layouts.app title="Riwayat Unggahan" description="Daftar berkas CSV yang pernah diunggah. Berkas yang isinya sama tidak dapat diunggah dua kali.">
    <x-slot:actions>
        <x-button icon="upload" data-open-dialog="upload-dialog">Unggah Data</x-button>
    </x-slot:actions>

    <x-card title="Berkas Terunggah" icon="document" :padded="false"
            :description="number_format($imports->total(), 0, ',', '.').' berkas'">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th scope="col" class="px-5 py-3">Berkas</th>
                        <th scope="col" class="px-5 py-3">Diunggah oleh</th>
                        <th scope="col" class="px-5 py-3">Waktu</th>
                        <th scope="col" class="px-5 py-3 text-right">Baru</th>
                        <th scope="col" class="px-5 py-3 text-right">Duplikat</th>
                        <th scope="col" class="px-5 py-3 text-right">Tidak valid</th>
                        @can('delete-detections')
                            <th scope="col" class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($imports as $import)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-500"><x-icon name="document" class="size-4" /></span>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-slate-900">{{ $import->original_name }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ $import->sizeForHumans() }} ·
                                            <span class="font-mono" title="SHA-256 {{ $import->file_hash }}">{{ substr($import->file_hash, 0, 12) }}</span>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-slate-700">{{ $import->uploaderName() }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-slate-500" title="{{ $import->created_at->translatedFormat('d M Y H:i:s') }}">
                                {{ $import->created_at->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-right font-semibold tabular-nums text-emerald-700">{{ number_format($import->created_count, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-amber-700">{{ number_format($import->duplicate_count, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums {{ $import->invalid_count ? 'text-rose-700' : 'text-slate-400' }}">{{ number_format($import->invalid_count, 0, ',', '.') }}</td>
                            @can('delete-detections')
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <form method="POST" action="{{ route('detections.imports.destroy', $import) }}"
                                          data-confirm="Hapus riwayat &quot;{{ $import->original_name }}&quot;? Data deteksinya TIDAK ikut terhapus, dan berkas yang sama dapat diunggah ulang.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" aria-label="Hapus riwayat {{ $import->original_name }}">
                                            <x-icon name="trash" class="size-4" />
                                        </button>
                                    </form>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state icon="upload" title="Belum ada berkas yang diunggah" description="Riwayat akan muncul setelah berkas CSV pertama diunggah.">
                                    <x-button icon="upload" data-open-dialog="upload-dialog">Unggah Data</x-button>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $imports->links() }}
    </x-card>
</x-layouts.app>
