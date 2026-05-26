<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi Siswa - Esensi Admin</title>
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
                        <a href="{{ route('rekap.absen') }}" class="border-white text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-bold">Laporan Absensi</a>
                        <a href="{{ route('laporan.mingguan') }}" class="border-transparent text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-bold">Rekap Laporan</a>
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
            
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <h1 class="text-3xl font-extrabold text-slate-800">Rekap Absensi Harian</h1>
                
                <!-- FILTER FORM -->
                <form method="GET" action="{{ route('rekap.absen') }}" class="flex bg-white p-2 rounded-lg shadow-sm border border-gray-200 gap-2">
                    <div>
                        <input type="date" name="tanggal" value="{{ $tanggal }}" class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    
                    <div>
                        <select name="kelas" class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm focus:outline-none focus:border-emerald-500">
                            @foreach($daftarKelas as $kls)
                                <option value="{{ $kls }}" {{ $kelasAktif == $kls ? 'selected' : '' }}>{{ $kls }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md font-bold text-sm transition-colors">
                        Tampilkan Data
                    </button>

                    <button type="submit" formaction="{{ route('export.absen') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-bold text-sm transition-colors flex items-center gap-2">
                        <span>📊</span> Export Excel
                    </button>
                </form>
            </div>

            <!-- TABEL DATA -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-emerald-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider w-16">No</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">NISN</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">Nama Lengkap</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-emerald-800 uppercase tracking-wider">Jam Datang</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-emerald-800 uppercase tracking-wider">Jam Pulang</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-emerald-800 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($rekap as $index => $row)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-700">{{ $row->nisn }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">{{ $row->nama }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-center font-mono">{{ $row->jam_datang }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-center font-mono">{{ $row->jam_pulang }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    
                                    <!-- LOGIKA WARNA STATUS -->
                                    @if($row->status == 'Hadir')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-700">
                                            ✅ Hadir
                                        </span>
                                    @elseif($row->status == 'Alpa')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-700">
                                            ❌ Alpa (Belum Tap)
                                        </span>
                                    @else
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-yellow-100 text-yellow-800">
                                            ⚠️ Belum Registrasi Kartu
                                        </span>
                                    @endif

                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500 font-medium">
                                    Tidak ada data siswa untuk kelas ini.
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