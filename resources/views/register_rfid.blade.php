<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Kartu RFID - Esensi Admin</title>
    <!-- Menggunakan Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen">

    <!-- ========================================== -->
    <!-- NAVBAR START -->
    <!-- ========================================== -->
    <nav class="bg-emerald-600 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Bagian Kiri: Logo & Menu -->
                <div class="flex">
                    <!-- Logo / Brand -->
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-white font-extrabold text-xl tracking-wider">ESENSI<span class="text-emerald-300">ADMIN</span></span>
                    </div>
                    <!-- Menu Desktop -->
                    <div class="hidden sm:ml-8 sm:flex sm:space-x-8">
                        <a href="{{ route('dashboard') }}" class="border-transparent text-emerald-100 hover:border-emerald-200 hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Dashboard</a>
                        <a href="{{ route('register.web') }}" class="border-white text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-bold">Registrasi RFID</a>
                        <a href="{{ route('rekap.absen') }}" class="border-transparent text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-bold">Laporan Absensi</a>
                        <a href="{{ route('laporan.mingguan') }}" class="border-transparent text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-bold">Rekap Laporan</a>
                    </div>
                </div>
                
                <!-- Bagian Kanan: Profil & Logout -->
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <span class="text-emerald-100 text-sm font-medium mr-4">Halo, Administrator</span>
                    <button class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded-lg text-sm font-bold transition duration-300 shadow-sm">
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </nav>
    <!-- ========================================== -->
    <!-- NAVBAR END -->
    <!-- ========================================== -->

    <!-- KONTEN UTAMA -->
    <main class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- CARD FORM REGISTRASI -->
            <div class="bg-white rounded-2xl shadow-lg w-full overflow-hidden border border-gray-100">
                <div class="bg-emerald-500 p-6 text-center text-white">
                    <h2 class="text-2xl font-bold">Registrasi Siswa Baru (RFID)</h2>
                    <p class="text-emerald-100 mt-1">Tempelkan kartu ke scanner PC Anda</p>
                </div>

                <div class="p-8">
                    <!-- Notifikasi Sukses -->
                    @if(session('success'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-lg" role="alert">
                            <p class="font-bold">Berhasil</p>
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                    <!-- Notifikasi Error -->
                    @if(session('error') || $errors->any())
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg" role="alert">
                            <p class="font-bold">Terjadi Kesalahan</p>
                            <ul class="list-disc ml-5 mt-1">
                                @if(session('error')) <li>{{ session('error') }}</li> @endif
                                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- ... KODE SEBELUMNYA (Form Method & Token) ... -->

                    <form action="{{ route('register.web.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <!-- KOLOM UID -->
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">UID Kartu <span class="text-emerald-600 text-sm font-normal">(Tap RFID Sekarang)</span></label>
                            <input type="text" name="uid" id="uid" value="{{ old('uid') }}" required autofocus autocomplete="off"
                                class="w-full px-4 py-3 border-2 border-emerald-400 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-emerald-50 text-emerald-900 font-mono text-lg transition-colors placeholder-emerald-300"
                                placeholder="Menunggu scan kartu...">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- KOLOM NAMA DENGAN AUTOCOMPLETE -->
                            <div class="relative"> <!-- Tambahkan 'relative' di sini -->
                                <label class="block text-gray-700 font-semibold mb-2">Nama Lengkap</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required autocomplete="off" placeholder="Ketik untuk mencari data siswa..."
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
                                
                                <!-- WADAH DROPDOWN HASIL PENCARIAN -->
                                <ul id="autocomplete-list" class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg shadow-xl mt-1 hidden max-h-60 overflow-y-auto divide-y divide-gray-100">
                                    <!-- List akan dimuat di sini oleh JavaScript -->
                                </ul>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Kelas</label>
                                <input type="text" name="kelas" id="kelas" value="{{ old('kelas') }}" required placeholder="Terisi otomatis"
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="Terisi otomatis"
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Password</label>
                                <input type="password" name="password" id="password" required value="123456"
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Upload Foto Siswa (Opsional)</label>
                            <input type="file" name="foto" accept="image/*"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:border-emerald-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-100 file:text-emerald-700 hover:file:bg-emerald-200 transition-colors">
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                            <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-emerald-700 hover:shadow-lg transform transition-all duration-200">
                                Simpan Data Siswa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- JAVASCRIPT KHUSUS UNTUK RFID & AUTOCOMPLETE -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uidInput = document.getElementById('uid');
            const nameInput = document.getElementById('name');
            const kelasInput = document.getElementById('kelas');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const autocompleteList = document.getElementById('autocomplete-list');

            // 1. FOKUS OTOMATIS KE RFID
            uidInput.focus();

            // 2. CEGAH RFID SUBMIT FORM & PINDAH KE NAMA
            uidInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault(); 
                    if(uidInput.value.trim() !== '') {
                        nameInput.focus(); 
                        uidInput.classList.remove('border-emerald-400', 'bg-emerald-50', 'text-emerald-900');
                        uidInput.classList.add('border-green-500', 'bg-green-100', 'text-green-800');
                    }
                }
            });

            // 3. LOGIKA AUTOCOMPLETE PENCARIAN
            nameInput.addEventListener('input', function() {
                let val = this.value;
                if (!val) {
                    autocompleteList.innerHTML = '';
                    autocompleteList.classList.add('hidden');
                    return;
                }

                // Ambil data dari backend Laravel
                fetch(`/search-siswa?q=${val}`)
                .then(response => response.json())
                .then(data => {
                    autocompleteList.innerHTML = ''; // Bersihkan list lama
                    
                    if(data.length > 0) {
                        autocompleteList.classList.remove('hidden');
                        
                        // Looping hasil dari database
                        data.forEach(item => {
                            let li = document.createElement('li');
                            li.className = "px-4 py-3 hover:bg-emerald-50 cursor-pointer transition-colors";
                            li.innerHTML = `
                                <div class="font-bold text-gray-800">${item.Nama}</div>
                                <div class="text-xs text-gray-500 font-medium mt-1">
                                    <span class="bg-gray-200 px-2 py-0.5 rounded text-gray-700">${item.kelas}</span> 
                                    NIS: ${item.NISN}
                                </div>
                            `;
                            
                            // JIKA SALAH SATU SISWA DIKLIK
                            li.addEventListener('click', function() {
                                // Isi otomatis form yang kosong
                                nameInput.value = item.Nama;
                                kelasInput.value = item.kelas;
                                
                                // Auto-generate email menggunakan NIS agar rapi (Contoh: 1234@smkn1narmada.sch.id)
                                emailInput.value = item.NISN + "@smkn1narmada.sch.id";

                                // Sembunyikan dropdown list
                                autocompleteList.classList.add('hidden');
                                
                            });
                            
                            autocompleteList.appendChild(li);
                        });
                    } else {
                        autocompleteList.classList.add('hidden');
                    }
                });
            });

            // 4. SEMBUNYIKAN DROPDOWN JIKA KLIK DI LUAR KOTAK NAMA
            document.addEventListener('click', function(e) {
                if(e.target !== nameInput) {
                    autocompleteList.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>