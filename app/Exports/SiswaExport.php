<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiswaExport implements FromCollection, WithHeadings
{
    protected $data;

    // Menangkap data yang dikirim dari Controller
    public function __construct($data)
    {
        $this->data = $data;
    }

    // Mengubah data menjadi format yang bisa dibaca Excel
    public function collection()
    {
        return collect($this->data);
    }

    // Membuat Judul/Header di baris paling atas Excel
    public function headings(): array
    {
        return [
            'NISN',
            'Nama Lengkap',
            'Kelas',
            'Jam Datang',
            'Jam Pulang',
            'Status'
        ];
    }
}