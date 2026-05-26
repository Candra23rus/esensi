<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Admin; // Tambahkan ini

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Buat Akun Admin
        Admin::create([
            'name' => 'Super Admin',
            'username' => 'admin',
            'password' => bcrypt('admin123'),
            'role' => 'admin'
        ]);

        // Buat Akun Wali Kelas (Sesuaikan nama kelasnya dengan yang ada di table_siswa Anda)
        Admin::create([
            'name' => 'Wali Kelas XII RPL 1',
            'username' => 'walirpl1',
            'password' => bcrypt('wali123'),
            'role' => 'walikelas',
            'kelas_binaan' => 'XII RPL 1' // Wajib sama persis dengan yang di database
        ]);
    }
}