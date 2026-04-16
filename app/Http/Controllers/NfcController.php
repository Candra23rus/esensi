<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;

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
            'foto_url'    => 'required|string|max:25',
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
        $user->foto_url = $request->foto_url;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi Berhasil: ' . $user->name
        ], 200);
    }

    // ==========================================
    // 2. FUNGSI ABSENSI HARIAN
    // ==========================================
    public function absen(Request $request)
    {
        $request->validate(['uid' => 'required|string']);
        $uid = $request->uid;

        $user = User::where('nfc_uid', $uid)->first();

        // Jika kartu tidak ada di database
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kartu Belum Diregistrasi!'
            ], 404);
        }

        // Cek apakah hari ini sudah absen
        $sudahAbsen = Attendance::where('user_id', $user->id)
                                ->whereDate('check_in', now()->toDateString())
                                ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'status' => 'error',
                'message' => 'Halo ' . $user->name . ', Anda sudah absen hari ini.'
            ], 400);
        }

        // Catat Absen
        Attendance::create([
            'user_id' => $user->id,
            'check_in' => now(),
            'status' => 'Hadir'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Absen Berhasil!',
            'data' => [
                'nama' => $user->name,
                'kelas' => $user->kelas ?? 'Kelas Belum Diatur', // Jika kolom kelas kosong
                // Jika foto kosong, kirimkan null. Jika ada, kirim link lengkapnya
                'foto_url' => $user->foto_url ? asset('foto_siswa/' . $user->foto_url) : null
            ]
        ], 200);
    }
}