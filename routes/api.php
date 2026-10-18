<?php

use App\Http\Controllers\PesananController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/pesanan', [PesananController::class, 'store']);
    Route::get('/pesanan/{kode}', [PesananController::class, 'show']);
    Route::post('/pesanan/{kode}/konfirmasi', [PesananController::class, 'konfirmasi']);
    Route::post('/pesanan/{kode}/batal', [PesananController::class, 'batal']);
});
