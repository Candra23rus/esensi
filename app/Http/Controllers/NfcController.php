<?php

namespace App\Http\Controllers;
use App\Exports\SiswaExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NfcController extends Controller
{
    // ==========================================
    // 1. FUNGSI REGISTRASI KARTU BARU
    // ==========================================
    public function registerCard(Request $request)
    {
        // Validasi input dari Android
        $request->validate([
            'uid'      => 'required|string',
            'name'     => 'required|string|max:255',
            'kelas'    => 'required|string|max:25',
            'email'    => 'required|string|email|max:255|unique:users',
            'foto'     => 'required|string|max:255', // Diperlebar agar aman untuk path URL
            'password' => 'required|string|min:6',
        ]);

        $uid = $request->uid;

        // Cek apakah kartu sudah pernah didaftarkan
        $cekKartu = User::where('nfc_uid', $uid)->first();
        if ($cekKartu) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kartu ini sudah terdaftar atas nama: ' . $cekKartu->name
            ], 400);
        }

        // Simpan Data User Baru
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->nfc_uid = $uid;
        $user->kelas = $request->kelas;
        $user->foto_url = $request->foto;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi Berhasil: ' . $user->name
        ], 200);
    }

    // ==========================================
    // 2. FUNGSI ABSENSI (DATANG & PULANG)
    // ==========================================
    public function absen(Request $request)
    {
        $request->validate(['uid' => 'required|string']);
        $uid = $request->uid;

        $user = User::where('nfc_uid', $uid)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kartu Belum Diregistrasi!'
            ], 404);
        }

        $today = now()->toDateString();
        $waktuSekarang = now()->format('H:i:s');

        // Cari data absensi siswa untuk hari ini
        $absensiHariIni = Attendance::where('user_id', $user->id)
                                    ->whereDate('check_in', $today)
                                    ->first();

        // AMBIL NOMOR WA ORANG TUA DARI TABLE_SISWA
        // Sesuaikan 'no_wa_ortu' dengan nama kolom asli di table_siswa Anda
        $siswaMaster = DB::table('table_siswa')->where('Nama', $user->name)->first();
        $nomorWaOrtu = $siswaMaster ? $siswaMaster->HP : null;

        // SKENARIO 1: Belum Absen Sama Sekali (Absen Datang)
        if (!$absensiHariIni) {
            Attendance::create([
                'user_id'  => $user->id,
                'check_in' => now(),
                'status'   => 'Hadir'
            ]);

            $tipeAbsen = 'Datang';
            $pesan = 'Absen Datang Berhasil!';

            // ---> KIRIM WA DATANG <---
            if (!empty($nomorWaOrtu)) {
                $teksWa = "🔔 *NOTIFIKASI KEHADIRAN*\n\nHalo Bapak/Ibu, ananda *{$user->name}* telah *Tiba* di sekolah pada pukul {$waktuSekarang} WITA.\n\nTerima kasih,\nSistem Esensi SMKN 1 Narmada.";
                $this->kirimWhatsApp($nomorWaOrtu, $teksWa);
            }

        } 
        // SKENARIO 2: Sudah Absen Datang, Belum Absen Pulang (Absen Pulang)
        elseif (is_null($absensiHariIni->check_out)) {
            
            if (now()->hour < 10) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Halo ' . $user->name . ', absen pulang ditolak! Anda baru bisa absen pulang setelah jam 10:00.'
                ], 400); 
            }

            $absensiHariIni->check_out = now();
            $absensiHariIni->save();

            $tipeAbsen = 'Pulang';
            $pesan = 'Absen Pulang Berhasil!';

            // ---> KIRIM WA PULANG <---
            if (!empty($nomorWaOrtu)) {
                $teksWa = "🔔 *NOTIFIKASI KEPULANGAN*\n\nHalo Bapak/Ibu, ananda *{$user->name}* telah *Pulang* dari sekolah pada pukul {$waktuSekarang} WITA.\n\nTerima kasih,\nSistem Esensi SMKN 1 Narmada.";
                $this->kirimWhatsApp($nomorWaOrtu, $teksWa);
            }

        } 
        // SKENARIO 3: Sudah Absen Datang & Pulang
        else {
            return response()->json([
                'status' => 'error',
                'message' => 'Halo ' . $user->name . ', Anda sudah absen datang dan pulang hari ini.'
            ], 400);
        }

        return response()->json([
            'status'  => 'success',
            'message' => $pesan,
            'data'    => [
                'nama'       => $user->name,
                'kelas'      => $user->kelas ?? 'Kelas Belum Diatur',
                'tipe_absen' => $tipeAbsen, 
                'waktu'      => $waktuSekarang,
                'foto_url'   => $user->foto_url ? asset('foto_siswa/' . $user->foto_url) : null
            ]
        ], 200);
    }

    // ... (Fungsi showRegisterWeb, searchSiswa, rekapAbsen dll dibiarkan) ...

    // ==========================================
    // FUNGSI HELPER UNTUK MENGIRIM WHATSAPP
    // ==========================================
    private function kirimWhatsApp($target, $pesan)
    {
        // PENTING: Jika menggunakan Fonnte, ganti 'TOKEN_API_ANDA_DISINI' dengan token asli Anda.
        // Jika menggunakan API lain (misal API lokal Node.js), ubah URL CURLOPT_URL ke URL lokal Anda.
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $target,
                'message' => $pesan,
                'countryCode' => '62', // Otomatis mengubah angka 08 menjadi +62
            ),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Q65LbkzxxevJmhAgbGT6' // <--- GANTI INI DENGAN TOKEN API WHATSAPP GATEWAY ANDA
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        
        return $response;
    }

    // ==========================================
    // 3. FUNGSI REGISTRASI VIA WEB (RFID SCANNER PC)
    // ==========================================
    
    public function showRegisterWeb()
    {
        return view('register_rfid');
    }

    public function processRegisterWeb(Request $request)
    {
        $request->validate([
            'uid'      => 'required|string',
            'name'     => 'required|string|max:255',
            'kelas'    => 'required|string|max:25',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'foto'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048' // Validasi file gambar web
        ]);

        // Cek apakah kartu sudah pernah didaftarkan
        $cekKartu = User::where('nfc_uid', $request->uid)->first();
        if ($cekKartu) {
            return back()->with('error', 'Kartu ini sudah terdaftar atas nama: ' . $cekKartu->name)->withInput();
        }

        // Proses Upload Foto (jika ada)
        $namaFileFoto = 'default.png'; // Jika tidak upload foto
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $namaFileFoto = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $file->move(public_path('foto_siswa'), $namaFileFoto);
        }

        // Simpan Data User Baru
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->nfc_uid = $request->uid;
        $user->kelas = $request->kelas;
        $user->foto_url = $namaFileFoto; // Menyimpan nama file, bukan string manual lagi
        $user->save();

        return back()->with('success', 'Siswa ' . $user->name . ' berhasil diregistrasi!');
    }


    // ==========================================
    // 4. FUNGSI PENCARIAN AUTOCOMPLETE
    // ==========================================
    public function searchSiswa(Request $request)
    {
        $search = $request->q;

        // Jika ketikan kosong, kembalikan array kosong
        if ($search == '') {
            return response()->json([]);
        }

        // Cari data di table_siswa yang namanya mirip dengan ketikan
        $siswa = DB::table('table_siswa')
            ->select('Nama', 'kelas', 'NISN') // Ganti dengan nama kolom di database Anda
            ->where('Nama', 'LIKE', '%' . $search . '%')
            ->limit(5) // Batasi 5 hasil saja agar dropdown tidak kepanjangan
            ->get();

        return response()->json($siswa);
    }
    // ==========================================
    // 5. FUNGSI REKAP ABSENSI HARIAN PER KELAS
    // ==========================================
    public function rekapAbsen(Request $request)
    {
        // Ambil parameter dari URL, jika kosong gunakan hari ini
        $tanggal = $request->input('tanggal', now()->toDateString());
        $kelasAktif = $request->input('kelas', '');
        $userLogin = Auth::guard('admin')->user();

        if ($userLogin->role == 'walikelas') {
            // Jika dia wali kelas, paksa dropdown hanya berisi kelas binaannya saja
            $daftarKelas = collect([$userLogin->kelas_binaan]);
            $kelasAktif = $userLogin->kelas_binaan; // Paksa kelas aktif ke kelasnya
        } else {
            // Jika dia Admin, ambil semua kelas
            $daftarKelas = DB::table('table_siswa')
                ->select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

            if (empty($kelasAktif) && count($daftarKelas) > 0) {
                $kelasAktif = $daftarKelas[0];
            }
        }

        // 2. Ambil Master Data Siswa berdasarkan kelas yang dipilih
        $siswaMaster = DB::table('table_siswa')
            ->where('kelas', $kelasAktif)
            ->orderBy('Nama')
            ->get();

        // 3. Ambil data User yang sudah Registrasi RFID (berdasarkan kelas)
        // Kita jadikan 'name' sebagai kunci/index untuk pencocokan
        $usersRegistered = User::where('kelas', $kelasAktif)->get()->keyBy('name');

        // 4. Ambil data Absensi pada tanggal yang dipilih
        $userIds = $usersRegistered->pluck('id');
        $absensi = Attendance::whereIn('user_id', $userIds)
            ->whereDate('check_in', $tanggal)
            ->get()
            ->keyBy('user_id');

        // 5. Gabungkan dan olah ketiga data di atas
        $rekap = [];
        foreach ($siswaMaster as $siswa) {
            // Cari apakah siswa ini sudah registrasi RFID (Pencocokan lewat Nama)
            $user = $usersRegistered->get($siswa->Nama);
            
            $jamDatang = '-';
            $jamPulang = '-';
            $status = 'Belum Registrasi';

            if ($user) {
                // Jika sudah registrasi, cari data absennya hari ini
                $absen = $absensi->get($user->id);
                
                if ($absen) {
                    $status = 'Hadir';
                    $jamDatang = Carbon::parse($absen->check_in)->format('H:i:s');
                    $jamPulang = $absen->check_out ? Carbon::parse($absen->check_out)->format('H:i:s') : '-';
                } else {
                    $status = 'Alpa'; // Punya kartu, tapi tidak tap hari ini
                }
            }

            $rekap[] = (object) [
                'nisn' => $siswa->NISN,
                'nama' => $siswa->Nama,
                'kelas' => $siswa->kelas,
                'jam_datang' => $jamDatang,
                'jam_pulang' => $jamPulang,
                'status' => $status
            ];
        }

        return view('rekap_absen', compact('daftarKelas', 'kelasAktif', 'tanggal', 'rekap'));
    }
    // ==========================================
    // 6. FUNGSI LAPORAN KEHADIRAN RENTANG TANGGAL
    // ==========================================
    public function laporanMingguan(Request $request)
    {
        // Default rentang: Senin minggu ini sampai hari ini
        $startDate = $request->input('start_date', now()->startOfWeek()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $kelasAktif = $request->input('kelas', '');
        $userLogin = Auth::guard('admin')->user(); // Ambil data user yang sedang login

        // --- LOGIKA PEMBATASAN AKSES ---
        if ($userLogin->role == 'walikelas') {
            // Jika dia wali kelas, paksa dropdown hanya berisi kelas binaannya saja
            $daftarKelas = collect([$userLogin->kelas_binaan]);
            $kelasAktif = $userLogin->kelas_binaan; // Paksa kelas aktif ke kelasnya
        } else {
            // Jika dia Admin, ambil semua kelas
            $daftarKelas = DB::table('table_siswa')
                ->select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

            if (empty($kelasAktif) && count($daftarKelas) > 0) {
                $kelasAktif = $daftarKelas[0];
            }
        }
        

        // 2. Hitung total hari efektif (Hanya Senin - Jumat) dalam rentang tanggal
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        $totalHariAktif = $start->diffInDaysFiltered(function(Carbon $date) {
            return $date->isWeekday(); // isWeekday() akan mengabaikan Sabtu (6) dan Minggu (0)
        }, $end);

        // 3. Ambil Data Master Siswa
        $siswaMaster = DB::table('table_siswa')
            ->where('kelas', $kelasAktif)
            ->orderBy('Nama')
            ->get();

        // 4. Ambil User RFID yang cocok
        $usersRegistered = User::where('kelas', $kelasAktif)->get()->keyBy('name');

        // 5. Ambil data Absensi dalam rentang tanggal
        $userIds = $usersRegistered->pluck('id');
        $absensi = Attendance::whereIn('user_id', $userIds)
            ->whereBetween('check_in', [$start, $end])
            ->get()
            ->groupBy('user_id');

        // 6. Olah data (Hitung Total Hadir dan Alpa)
        $laporan = [];
        foreach ($siswaMaster as $siswa) {
            $user = $usersRegistered->get($siswa->Nama);
            
            $hadir = 0;
            $alpa = $totalHariAktif;
            $statusReg = 'Sudah';

            if ($user) {
                // Ambil semua absen milik user ini di rentang tanggal tersebut
                $dataAbsenSiswa = $absensi->get($user->id);
                
                if ($dataAbsenSiswa) {
                    // Kelompokkan berdasarkan tanggal untuk menghindari double count 
                    // (jika misal 1 hari siswa tap 2 kali karena error)
                    $hadir = $dataAbsenSiswa->groupBy(function($item) {
                        return Carbon::parse($item->check_in)->format('Y-m-d');
                    })->count();
                }
                
                // Hitung Alpa (Total Hari Efektif - Total Hadir)
                $alpa = $totalHariAktif - $hadir;
                if ($alpa < 0) $alpa = 0; // Jaga-jaga jika siswa absen di hari Sabtu/Minggu

            } else {
                $statusReg = 'Belum Registrasi';
            }

            $laporan[] = (object) [
                'nisn' => $siswa->NISN,
                'nama' => $siswa->Nama,
                'status_reg' => $statusReg,
                'hadir' => $hadir,
                'alpa' => $alpa
            ];
        }

        return view('laporan_mingguan', compact('daftarKelas', 'kelasAktif', 'startDate', 'endDate', 'totalHariAktif', 'laporan'));
    }
    // ==========================================
    // 8. FUNGSI EXPORT KE EXCEL
    // ==========================================
    public function exportExcel(Request $request)
    {
        $tanggal = $request->input('tanggal', now()->toDateString());
        $kelasAktif = $request->input('kelas', '');

        // Ambil Data Master & User RFID
        $siswaMaster = \Illuminate\Support\Facades\DB::table('table_siswa')
                        ->where('kelas', $kelasAktif)
                        ->orderBy('Nama')
                        ->get();
                        
        $usersRegistered = \App\Models\User::where('kelas', $kelasAktif)->get()->keyBy('name');
        $userIds = $usersRegistered->pluck('id');
        
        $absensi = \App\Models\Attendance::whereIn('user_id', $userIds)
                        ->whereDate('check_in', $tanggal)
                        ->get()
                        ->keyBy('user_id');

        $rekap = [];
        foreach ($siswaMaster as $siswa) {
            $user = $usersRegistered->get($siswa->Nama);
            $jamDatang = '-';
            $jamPulang = '-';
            $status = 'Belum Registrasi';

            if ($user) {
                $absen = $absensi->get($user->id);
                if ($absen) {
                    $status = 'Hadir';
                    $jamDatang = \Carbon\Carbon::parse($absen->check_in)->format('H:i:s');
                    $jamPulang = $absen->check_out ? \Carbon\Carbon::parse($absen->check_out)->format('H:i:s') : '-';
                } else {
                    $status = 'Alpa';
                }
            }

            // Data yang akan masuk ke Excel
            $rekap[] = [
                'nisn' => $siswa->NISN,
                'nama' => $siswa->Nama,
                'kelas' => $siswa->kelas,
                'jam_datang' => $jamDatang,
                'jam_pulang' => $jamPulang,
                'status' => $status
            ];
        }

        $namaFile = "Rekap_Absen_" . str_replace(' ', '_', $kelasAktif) . "_{$tanggal}.xlsx";
        return Excel::download(new SiswaExport($rekap), $namaFile);
    }
    public function exportLaporanMingguan(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $kelasAktif = $request->input('kelas');

        // 1. Hitung hari efektif (Senin-Jumat)
        $start = \Carbon\Carbon::parse($startDate)->startOfDay();
        $end = \Carbon\Carbon::parse($endDate)->endOfDay();
        $totalHariAktif = $start->diffInDaysFiltered(function(\Carbon\Carbon $date) {
            return $date->isWeekday();
        }, $end);

        // 2. Ambil data master dan absensi
        $siswaMaster = \Illuminate\Support\Facades\DB::table('table_siswa')->where('kelas', $kelasAktif)->orderBy('Nama')->get();
        $usersRegistered = \App\Models\User::where('kelas', $kelasAktif)->get()->keyBy('name');
        $userIds = $usersRegistered->pluck('id');
        $absensi = \App\Models\Attendance::whereIn('user_id', $userIds)
                    ->whereBetween('check_in', [$start, $end])
                    ->get()
                    ->groupBy('user_id');

        $laporan = [];
        foreach ($siswaMaster as $siswa) {
            $user = $usersRegistered->get($siswa->Nama);
            $hadir = 0;
            $statusReg = 'Belum Registrasi';

            if ($user) {
                $statusReg = 'Aktif';
                $dataAbsenSiswa = $absensi->get($user->id);
                if ($dataAbsenSiswa) {
                    $hadir = $dataAbsenSiswa->groupBy(fn($item) => \Carbon\Carbon::parse($item->check_in)->format('Y-m-d'))->count();
                }
            }

            $alpa = $user ? ($totalHariAktif - $hadir) : $totalHariAktif;
            if ($alpa < 0) $alpa = 0;

            $laporan[] = [
                'nisn' => $siswa->NISN,
                'nama' => $siswa->Nama,
                'hadir' => $hadir,
                'alpa' => $alpa,
                'status_reg' => $statusReg
            ];
        }

        $namaFile = "Laporan_Kehadiran_{$kelasAktif}_{$startDate}_sd_{$endDate}.xlsx";
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\LaporanRentangExport($laporan), $namaFile);
    }
}