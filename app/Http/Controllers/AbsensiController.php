<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function store(Request $request)
{
    $user = User::where('nfc_uid', $request->nfc_uid)->first();

    if (!$user) {
        return response()->json(['message' => 'Kartu tidak terdaftar!'], 404);
    }

    // Cek apakah hari ini sudah absen
    $alreadyCheckedIn = Attendance::where('user_id', $user->id)
                                    ->whereDate('check_in', now())
                                    ->exists();

    if ($alreadyCheckedIn) {
        return response()->json(['message' => 'Anda sudah absen hari ini!'], 400);
    }

    Attendance::create([
        'user_id' => $user->id,
        'check_in' => now(),
        'status' => 'Hadir'
    ]);

    return response()->json(['message' => 'Absensi Berhasil! Halo, ' . $user->name]);
}
}
