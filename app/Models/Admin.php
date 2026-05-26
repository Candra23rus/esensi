<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // <-- Ubah extends ini

class Admin extends Authenticatable
{
    protected $fillable = ['name', 'username', 'password', 'role', 'kelas_binaan'];
}