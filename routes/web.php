<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DetectionController;
use App\Http\Controllers\DetectionImportController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Monitoring
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/peta', MapController::class)->name('map');
    Route::get('/analisis', AnalyticsController::class)->name('analytics');

    // Data deteksi
    Route::get('/deteksi', [DetectionController::class, 'index'])->name('detections.index');
    Route::get('/deteksi/ekspor', [DetectionController::class, 'export'])->name('detections.export');
    Route::post('/deteksi/import', [DetectionImportController::class, 'store'])->name('detections.import');
    Route::get('/deteksi/riwayat', [DetectionImportController::class, 'index'])->name('detections.imports.index');
    Route::delete('/deteksi/riwayat/{import}', [DetectionImportController::class, 'destroy'])->name('detections.imports.destroy');
    Route::delete('/deteksi/{detection}', [DetectionController::class, 'destroy'])->name('detections.destroy');

    // Profil
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/kata-sandi', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Administrasi
    Route::resource('pengguna', UserController::class)
        ->except('show')
        ->parameters(['pengguna' => 'user'])
        ->names('users')
        ->middleware('can:manage-users');
});
