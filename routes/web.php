<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NfcController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;


Route::post('/nfc/register', [NfcController::class, 'registerCard']);
Route::post('/nfc/absen', [NfcController::class, 'absen']);
// Route::get('/register-rfid', [NfcController::class, 'showRegisterWeb'])->name('register.web');
// Route::post('/register-rfid', [NfcController::class, 'processRegisterWeb'])->name('register.web.process');
Route::get('/search-siswa', [NfcController::class, 'searchSiswa'])->name('search.siswa');
// Route::get('/rekap-absen', [NfcController::class, 'rekapAbsen'])->name('rekap.absen');
// Route::get('/laporan-mingguan', [NfcController::class, 'laporanMingguan'])->name('laporan.mingguan');
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'processLogin'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Route yang WAJIB LOGIN (Dilindungi Middleware)
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/register-rfid', [NfcController::class, 'showRegisterWeb'])->name('register.web');
    Route::post('/register-rfid', [NfcController::class, 'processRegisterWeb'])->name('register.web.process');
    Route::get('/rekap-absen', [NfcController::class, 'rekapAbsen'])->name('rekap.absen');
    Route::get('/laporan-mingguan', [NfcController::class, 'laporanMingguan'])->name('laporan.mingguan');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/export-absen', [NfcController::class, 'exportExcel'])->name('export.absen');
    Route::get('/export-laporan-mingguan', [NfcController::class, 'exportLaporanMingguan'])->name('export.laporan.mingguan');
});