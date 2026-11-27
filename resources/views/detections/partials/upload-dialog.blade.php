<dialog id="upload-dialog" @if ($errors->has('csv_file')) data-auto-open @endif
        class="m-auto w-[calc(100%-2rem)] max-w-lg rounded-2xl p-0 shadow-2xl ring-1 ring-slate-200 backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm">
    <form action="{{ route('detections.import') }}" method="POST" enctype="multipart/form-data" data-loading-form>
        @csrf
        <div class="flex items-start justify-between border-b border-slate-100 px-6 py-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Unggah Data Alat</h2>
                <p class="mt-0.5 text-sm text-slate-500">Sistem HamaPINTAR siap memproses file .CSV</p>
            </div>
            <button type="button" data-close-dialog class="rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                <x-icon name="x" />
            </button>
        </div>

        <div class="space-y-4 px-6 py-5">
            <label for="csv_file" data-dropzone
                   class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 px-6 py-10 text-center transition hover:border-emerald-500 hover:bg-emerald-50/40 data-[dragging]:border-emerald-500 data-[dragging]:bg-emerald-50">
                <span class="grid size-12 place-items-center rounded-full bg-emerald-50 text-emerald-600">
                    <x-icon name="upload" class="size-6" />
                </span>
                <span class="mt-3 text-sm font-semibold text-slate-900" data-file-name>Klik untuk memilih atau seret berkas ke sini</span>
                <span class="mt-1 text-xs text-slate-500">Format .CSV · maks. {{ config('hamapintar.import.max_file_kb') / 1024 }} MB</span>
                <input id="csv_file" type="file" name="csv_file" accept=".csv,text/csv" required class="sr-only" data-file-input>
            </label>

            @error('csv_file')
                <p class="text-sm text-rose-600">{{ $message }}</p>
            @enderror

            <div class="rounded-lg bg-slate-50 p-4 ring-1 ring-slate-100">
                <p class="text-xs font-semibold text-slate-700">Format kolom (baris pertama = header)</p>
                <pre class="mt-2 overflow-x-auto font-mono text-xs leading-relaxed text-slate-600">waktu,node,latitude,longitude
2026/11/20 18:15:00,PIR 1,-7.2520,112.7944</pre>
                <ul class="mt-2 list-inside list-disc space-y-0.5 text-xs text-slate-500">
                    <li>Baris duplikat (node &amp; waktu sama) otomatis dilewati.</li>
                    <li>Berkas yang isinya sama persis dengan unggahan sebelumnya ditolak, walau namanya berbeda.
                        <a href="{{ route('detections.imports.index') }}" class="font-medium text-emerald-700 hover:underline">Lihat riwayat</a></li>
                </ul>
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
            <x-button variant="secondary" data-close-dialog>Batal</x-button>
            <x-button type="submit" icon="upload"><span data-label>Unggah</span></x-button>
        </div>
    </form>
</dialog>
