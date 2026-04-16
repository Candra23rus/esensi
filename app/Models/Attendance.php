<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * Kolom-kolom yang diizinkan untuk diisi secara massal (Mass Assignment)
     */
    protected $fillable = [
        'user_id',
        'check_in',
        'status',
    ];

    /**
     * Casting tipe data agar Laravel otomatis mengubah 'check_in' menjadi objek Carbon/Datetime.
     * Ini sangat berguna agar kita bisa melakukan format jam seperti ->format('H:i:s') di tampilan Blade.
     */
    protected function casts(): array
    {
        return [
            'check_in' => 'datetime',
        ];
    }

    /**
     * Relasi ke Model User (Satu Absensi dimiliki oleh Satu User)
     * Ini wajib ada karena pada Dashboard kita menggunakan fungsi Attendance::with('user')
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}