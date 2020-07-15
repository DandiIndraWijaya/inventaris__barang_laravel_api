<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';

    public $fillable = ['nama_barang', 'kode', 'deskripsi', 'gambar'];
}
