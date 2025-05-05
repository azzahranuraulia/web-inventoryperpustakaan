<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTanggalKembaliOnPeminjamanTable extends Migration
{
    public function up()
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->date('tanggal_kembali')->nullable()->change(); // Ubah kolom menjadi nullable
        });
    }

    public function down()
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->date('tanggal_kembali')->nullable(false)->change(); // Kembalikan ke tidak nullable jika perlu
        });
    }
}