<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Absensi NFC</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* Sedikit kustomisasi agar background sedikit abu-abu dan kartu terlihat menonjol */
        body { background-color: #f8f9fa; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-nfc me-2"></i>E-Absensi
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Data Siswa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Laporan</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-light btn-sm mt-1 fw-semibold text-primary" href="#">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-2 pb-5">
        <h2 class="fw-bold mb-4 text-dark">Dashboard Rekap Absensi</h2>

        <div class="row g-4 mb-4">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card shadow-sm border-0 h-100 border-start border-primary border-4">
                    <div class="card-body">
                        <p class="text-muted small fw-bold text-uppercase mb-1">Total Pengguna</p>
                        <h3 class="display-6 fw-bold text-dark mb-0">{{ $stats['total_user'] }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card shadow-sm border-0 h-100 border-start border-success border-4">
                    <div class="card-body">
                        <p class="text-muted small fw-bold text-uppercase mb-1">Hadir Hari Ini</p>
                        <h3 class="display-6 fw-bold text-success mb-2">{{ $stats['hadir_today'] }}</h3>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ ($stats['hadir_today'] / max($stats['total_user'], 1)) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card shadow-sm border-0 h-100 border-start border-warning border-4">
                    <div class="card-body">
                        <p class="text-muted small fw-bold text-uppercase mb-1">Terlambat</p>
                        <h3 class="display-6 fw-bold text-warning mb-0">{{ $stats['terlambat'] }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card shadow-sm border-0 h-100 border-start border-danger border-4">
                    <div class="card-body">
                        <p class="text-muted small fw-bold text-uppercase mb-1">Belum Absen</p>
                        <h3 class="display-6 fw-bold text-danger mb-0">{{ $stats['absent'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <div class="col-12 col-lg-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white pt-4 pb-2 border-0">
                        <h6 class="fw-bold mb-0">Tren Kehadiran (7 Hari Terakhir)</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="attendanceChart" height="110"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white pt-4 pb-2 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0">Aktivitas Terbaru</h6>
                        <a href="#" class="text-primary text-decoration-none small">Lihat Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-muted small">
                                    <tr>
                                        <th class="ps-4">Nama</th>
                                        <th>Waktu</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recent_attendances as $attendance)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $attendance->user->name }}</td>
                                        <td class="text-muted small">{{ $attendance->check_in->format('H:i') }} WIB</td>
                                        <td>
                                            @if($attendance->status == 'Hadir')
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success">Hadir</span>
                                            @else
                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning text-dark">Terlambat</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">Belum ada data absensi hari ini.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            
            // Mengambil data dari PHP Controller ke JavaScript
            const labels = {!! json_encode($chartLabels) !!};
            const dataHadir = {!! json_encode($chartDataHadir) !!};
            const dataTerlambat = {!! json_encode($chartDataTerlambat) !!};

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Hadir',
                            data: dataHadir,
                            backgroundColor: 'rgba(25, 135, 84, 0.85)', // Bootstrap Success
                            borderRadius: 4
                        },
                        {
                            label: 'Terlambat',
                            data: dataTerlambat,
                            backgroundColor: 'rgba(255, 193, 7, 0.85)', // Bootstrap Warning
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // Membiarkan grafik mengisi tinggi card
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 } // Pastikan angkanya bulat
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>