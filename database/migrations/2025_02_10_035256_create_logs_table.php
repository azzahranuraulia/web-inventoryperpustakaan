<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id(); 
            $table->timestamp('timestamp')->useCurrent(); // Waktu log dicatat
            $table->unsignedBigInteger('user_id'); // ID pengguna yang melakukan tindakan
            $table->string('action'); // Jenis tindakan (CREATE, UPDATE, DELETE, LOGIN, dll.)
            $table->string('table_name'); // Nama tabel yang terpengaruh
            $table->text('old_value')->nullable(); // Nilai sebelum perubahan (jika ada)
            $table->text('new_value')->nullable(); // Nilai setelah perubahan (jika ada)
            $table->string('ip_address')->nullable(); // Alamat IP pengguna
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('logs');
    }
}