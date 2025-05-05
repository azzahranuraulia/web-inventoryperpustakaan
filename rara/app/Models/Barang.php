<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';
    protected $primaryKey = 'id_barang';
    public $timestamps = false;

    protected $fillable = [
        'nama_barang',
        'stok', 
        'kategori',
    ];

    public function kurangiStok($jumlah)
    {
        $this->stok -= $jumlah;
        $this->save();
    }

    public function tambahStok($jumlah)
    {
        $this->stok += $jumlah;
        $this->save();
    }
}