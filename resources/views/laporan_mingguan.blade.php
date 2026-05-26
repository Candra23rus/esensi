<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kehadiran - Esensi Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen">

    <!-- NAVBAR (Sama seperti Halaman Registrasi) -->
    <nav class="bg-emerald-600 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-white font-extrabold text-xl tracking-wider">ESENSI<span class="text-emerald-300">ADMIN</span></span>
                    </div>
                    <div class="hidden sm:ml-8 sm:flex sm:space-x-8">
                        <a href="{{ route('dashboard') }}" class="border-transparent text-emerald-100 hover:border-emerald-200 hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Dashboard</a>
                        <a href="{{ route('register.web') }}" class="border-transparent text-emerald-100 hover:border-emerald-200 hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Registrasi RFID</a>
                        <a href="{{ route('rekap.absen') }}" class="border-transparent text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-bold">Laporan Absensi</a>
                        <a href="{{ route('laporan.mingguan') }}" class="border-white text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-bold">Rekap Laporan</a>
                    </div>
                </div>
                <!-- Bagian Kanan: Profil & Logout -->
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <span class="text-emerald-100 text-sm font-medium mr-4">
                        Halo, {{ Auth::guard('admin')->user()->name }} 
                        <span class="bg-emerald-800 text-xs px-2 py-1 rounded ml-1 uppercase">{{ Auth::guard('admin')->user()->role }}</span>
                    </span>
                    
                    <!-- Form Logout -->
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded-lg text-sm font-bold transition duration-300 shadow-sm">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
    </nav>

    <!-- KONTEN UTAMA -->
    <main class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="mb-8">
                <h1 class="text-3xl font-extrabold text-slate-800">Laporan Kehadiran Berkala</h1>
                <p class="text-gray-500 mt-1">Pilih rentang tanggal untuk menghitung kalkulasi Hadir & Alpa.</p>
            </div>

            <!-- FILTER FORM BOX -->
            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 mb-8">
                <form method="GET" action="{{ route('laporan.mingguan') }}" class="flex flex-col md:flex-row gap-6 items-end">
                    <div class="flex-1 w-full">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:border-emerald-500">
                    </div>
                    <div class="flex-1 w-full">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:border-emerald-500">
                    </div>
                    <div class="flex-1 w-full">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Kelas</label>
                        <select name="kelas" class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:border-emerald-500">
                            @foreach($daftarKelas as $kls)
                                <option value="{{ $kls }}" {{ $kelasAktif == $kls ? 'selected' : '' }}>{{ $kls }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-2.5 rounded-lg font-bold transition-colors w-full md:w-auto h-full shadow-sm">
                            Kalkulasi Data
                        </button>
                    </div>
                    <button type="submit" formaction="{{ route('export.laporan.mingguan') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-bold transition-colors flex items-center gap-2 shadow-sm">
            <span>📊</span> Export
        </button>
                </form>
            </div>

            <!-- WIDGET INFO HARI -->
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-6 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0 text-emerald-500 text-2xl">📅</div>
                    <div class="ml-3">
                        <p class="text-sm text-emerald-800 font-medium">Informasi Rentang Waktu</p>
                        <p class="text-lg font-bold text-emerald-900">Total Hari Efektif Sekolah: <span class="text-xl bg-emerald-200 px-2 rounded">{{ $totalHariAktif }} Hari</span></p>
                        <p class="text-xs text-emerald-700 mt-1">*Penghitungan hari ini secara otomatis mengabaikan hari Minggu.</p>
                    </div>
                </div>
            </div>

            <!-- TABEL REKAPITULASI -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-emerald-600">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider w-16">No</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">NISN</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider w-1/3">Nama Lengkap Siswa</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Total Hadir</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Total Alpa</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Status Kartu</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($laporan as $index => $row)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-700">{{ $row->nisn }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">{{ $row->nama }}</td>
                                
                                <!-- Kolom Hadir -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="inline-flex items-center justify-center bg-green-100 text-green-700 w-10 h-10 rounded-full font-bold text-lg border border-green-200">
                                        {{ $row->hadir }}
                                    </div>
                                </td>
                                
                                <!-- Kolom Alpa -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="inline-flex items-center justify-center {{ $row->alpa > 0 ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-gray-100 text-gray-400' }} w-10 h-10 rounded-full font-bold text-lg">
                                        {{ $row->alpa }}
                                    </div>
                                </td>

                                <!-- Status Registrasi RFID -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($row->status_reg == 'Sudah')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-blue-100 text-blue-700">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-yellow-100 text-yellow-800">
                                            Belum Registrasi
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500 font-medium text-lg">
                                    Tidak ada data siswa yang ditemukan untuk kelas ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </main>

</body>
</html>