<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('denda', function (Blueprint $table) {
            $table->id('id_denda');
            $table->foreignId('id_peminjaman')->constrained('peminjaman')->onDelete('cascade');
            $table->string('nisn');
            $table->decimal('jumlah_denda', 10, 2);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Menambahkan foreign key untuk nisn
            $table->foreign('nisn')->references('nisn')->on('siswa')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('denda', function (Blueprint $table) {
            // Hapus foreign key sebelum menghapus tabel
            $table->dropForeign(['nisn']);
            $table->dropForeign(['id_peminjaman']);
        });

        Schema::dropIfExists('denda');
    }
};