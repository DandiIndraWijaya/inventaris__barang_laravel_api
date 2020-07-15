<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Riwayat extends Model
{
    protected $table = 'riwayat';
    public $fillable = ['kode_barang','peminjam', 'alamat_peminjam', 'kontak','keperluan', 'tenggat', 'aktif'];
}
