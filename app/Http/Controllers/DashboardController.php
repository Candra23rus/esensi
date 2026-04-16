<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon; // Tambahkan ini jika ingin format tanggal lebih rapi

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Data untuk 4 Kotak Summary di atas
        $stats = [
            'total_user' => User::count(),
            'hadir_today' => Attendance::whereDate('check_in', now())->where('status', 'Hadir')->count(),
            'terlambat' => Attendance::whereDate('check_in', now())->where('status', 'Terlambat')->count(),
            'absent' => User::count() - Attendance::whereDate('check_in', now())->count(),
        ];

        // 2. Data untuk Tabel Aktivitas Terbaru (5 data terakhir)
        $recent_attendances = Attendance::with('user')->latest()->take(5)->get();

        // 3. Data untuk Grafik Bar Chart.js (7 Hari Terakhir)
        $chartLabels = [];
        $chartDataHadir = [];
        $chartDataTerlambat = [];

        // Looping mundur dari 6 hari yang lalu sampai hari ini (0)
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            
            // Format tanggal untuk label di bawah grafik (Contoh: "14 Apr")
            $chartLabels[] = $date->translatedFormat('d M'); 
            
            // Hitung yang hadir pada tanggal tersebut
            $chartDataHadir[] = Attendance::whereDate('check_in', $date->toDateString())
                                          ->where('status', 'Hadir')->count();
                                          
            // Hitung yang terlambat pada tanggal tersebut
            $chartDataTerlambat[] = Attendance::whereDate('check_in', $date->toDateString())
                                              ->where('status', 'Terlambat')->count();
        }

        // 4. Kirim SEMUA variabel ke View
        return view('dashboard', compact(
            'stats', 
            'recent_attendances', 
            'chartLabels', 
            'chartDataHadir', 
            'chartDataTerlambat'
        ));
    }
}