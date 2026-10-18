<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $fillable = [
        'kode_pesanan',
        'nama_pelanggan',
        'sku_produk',
        'jumlah',
        'status',
    ];
}
