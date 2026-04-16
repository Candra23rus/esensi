<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NfcController;
use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/nfc/register', [NfcController::class, 'registerCard']);
Route::post('/nfc/absen', [NfcController::class, 'absen']);