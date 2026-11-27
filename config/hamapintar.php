<?php

return [

    /*
    | Node sensor PIR yang terpasang di sawah => warna marker/legenda.
    | Urutan menentukan urutan pada grafik.
    */
    'nodes' => [
        'PIR 1' => '#ef4444',
        'PIR 2' => '#f59e0b',
        'PIR 3' => '#10b981',
        'PIR 4' => '#3b82f6',
        'PIR 5' => '#8b5cf6',
    ],

    /*
    | Slot jam aktivitas tikus (interval 2 jam). Deteksi pada jam ganjil masuk ke
    | slot sebelumnya, mis. 19:40 -> slot 18:00.
    */
    'hour_slots' => [18, 20, 22, 0, 2, 4, 6],

    // Rentang default filter dashboard (hari ke belakang dari hari ini).
    'default_range_days' => 30,

    // Jumlah hari yang diproyeksikan oleh model regresi linear.
    'prediction_days' => 3,

    // Jumlah hari historis yang ditampilkan berdampingan dengan proyeksi.
    'prediction_history_days' => 7,

    // Jumlah log terbaru yang ditampilkan pada tabel.
    'recent_logs_limit' => 10,

    'map' => [
        'center' => [-7.2520, 112.7944],
        'zoom' => 17,
    ],

    'import' => [
        'max_file_kb' => 5120,
        'chunk_size' => 500,
    ],
];
