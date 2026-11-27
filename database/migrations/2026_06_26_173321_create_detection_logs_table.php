<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detection_logs', function (Blueprint $table) {
            $table->id();
            $table->string('node_name', 50);     // Nama node sensor (PIR 1 - PIR 5)
            $table->dateTime('detected_at');     // Waktu deteksi dari alat
            $table->decimal('latitude', 10, 8);  // Koordinat GPS lintang
            $table->decimal('longitude', 11, 8); // Koordinat GPS bujur
            $table->timestamps();

            // Satu node tidak mungkin mencatat dua deteksi di detik yang sama:
            // dipakai untuk menolak data duplikat saat import CSV.
            $table->unique(['node_name', 'detected_at']);
            $table->index('detected_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detection_logs');
    }
};
