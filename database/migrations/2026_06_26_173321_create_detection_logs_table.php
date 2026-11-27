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
            $table->string('node_name');        // Menyimpan nama Node (PIR 1 - PIR 5)
            $table->dateTime('detected_at');    // Waktu deteksi dari alat
            $table->decimal('latitude', 10, 8); // Koordinat GPS Lintang
            $table->decimal('longitude', 11, 8); // Koordinat GPS Bujur
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detection_logs');
    }
};
