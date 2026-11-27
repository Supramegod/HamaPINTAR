<?php

use App\Http\Controllers\MapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MapController::class, 'index'])->name('map');
Route::post('/upload-csv', [MapController::class, 'uploadCsv'])->name('upload.csv');
