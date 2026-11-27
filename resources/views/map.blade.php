<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebGIS Monitoring Hama Sawah</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-green-50 font-sans text-slate-800">

<nav class="sticky top-0 z-[1030] mb-8 border-b border-slate-200 bg-white px-[30px] py-3 shadow-[0_2px_4px_rgba(0,0,0,0.02)]">
    <div class="text-2xl font-bold tracking-tight">
        <span class="text-red-500">Hama</span><span class="text-green-500">PINTAR</span>
    </div>
</nav>

<div class="px-3 md:px-6">
    <h1 class="mb-6 text-center text-[1.75rem] font-bold uppercase text-slate-900">Pola Serangan Tikus di Sawah</h1>

    @if(session('success'))
        <div data-alert role="alert" class="mb-6 flex items-start justify-between gap-4 rounded-lg border border-green-200 bg-green-100 px-4 py-3 text-green-800">
            <span>{{ session('success') }}</span>
            <button type="button" data-dismiss-alert aria-label="Close" class="text-xl leading-none text-green-700 hover:text-green-900">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div role="alert" class="mb-6 rounded-lg border border-red-200 bg-red-100 px-4 py-3 text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 xl:grid-cols-4">
        {{-- Sidebar --}}
        <div class="lg:col-span-1">
            <div class="card">
                <div class="card-header">UNGGAH DATA ALAT</div>
                <div class="card-body">
                    <form action="{{ route('upload.csv') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label">Pilih Berkas .CSV</label>
                            <input type="file" name="csv_file" required
                                   class="form-input file:mr-3 file:rounded-md file:border-0 file:bg-slate-200 file:px-3 file:py-1 file:text-sm file:font-semibold file:text-slate-700">
                            <div class="mt-1.5 text-xs text-green-600">✓ Sistem HamaPINTAR siap memproses file .CSV</div>
                        </div>
                        <button type="submit" class="btn bg-blue-700 hover:bg-blue-800">UNGGAH</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">FILTER WAKTU</div>
                <div class="card-body">
                    <form action="/" method="GET">
                        <div class="mb-4">
                            <label class="form-label">Mulai Dari</label>
                            <input type="date" name="start_date" class="form-input" value="{{ $startDate }}">
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Sampai Dengan</label>
                            <input type="date" name="end_date" class="form-input" value="{{ $endDate }}">
                        </div>
                        <button type="submit" class="btn bg-teal-700 hover:bg-teal-800">CARI</button>
                    </form>
                </div>
            </div>

            <div class="rounded-2xl bg-gradient-to-br from-red-600 to-red-900 p-5 text-center text-white">
                <span class="block text-xs font-semibold uppercase text-red-300">Total Serangan</span>
                <div class="my-2 text-[2.5rem] font-bold">{{ $totalSerangan }}</div>
                <span class="text-[0.8rem] text-red-300">Data Terfilter Terbaca</span>
            </div>

            <div class="mt-3.5 rounded-lg border-l-[5px] border-amber-600 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700">
                ⚡ {{ $statusPrediksi }}
            </div>
        </div>

        {{-- Peta --}}
        <div class="lg:col-span-2 xl:col-span-3">
            <div id="map" class="h-[695px] w-full rounded-2xl shadow-lg shadow-black/5"></div>
        </div>
    </div>

    <div class="my-8 border-t border-slate-300"></div>
    <h3 class="mb-6 text-center text-xl font-bold uppercase text-slate-900">ANALISIS DERET WAKTU</h3>

    <div class="grid grid-cols-1 gap-x-6 md:grid-cols-2">
        <div class="card">
            <div class="card-header text-center">HARIAN (JAM)</div>
            <div class="card-body"><div class="relative h-[440px]"><canvas id="chartJam"></canvas></div></div>
        </div>
        <div class="card">
            <div class="card-header text-center">MINGGUAN (HARI)</div>
            <div class="card-body"><div class="relative h-[440px]"><canvas id="chartHari"></canvas></div></div>
        </div>
        <div class="card">
            <div class="card-header text-center">PRESENTASE KERAWANAN NODE</div>
            <div class="card-body"><div class="relative h-[440px]"><canvas id="chartNode"></canvas></div></div>
        </div>
        <div class="card border-2 border-amber-700 shadow-[0_4px_12px_rgba(217,119,6,0.1)]">
            <div class="card-header text-center !text-amber-700">🔮 PREDIKSI TREN HARI BERIKUTNYA</div>
            <div class="card-body"><div class="relative h-[440px]"><canvas id="chartPrediksi"></canvas></div></div>
        </div>
    </div>
</div>

<script id="dashboard-data" type="application/json">
    {!! json_encode([
        'logs' => $logs,
        'chartJamData' => $chartJamData,
        'chartHariData' => $chartHariData,
        'chartNodeData' => $chartNodeData,
        'predictiveLabels' => $predictiveLabels,
        'predictiveValues' => $predictiveValues,
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
</script>
</body>
</html>
