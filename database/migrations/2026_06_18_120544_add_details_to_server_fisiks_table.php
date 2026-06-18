<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('server_fisiks', function (Blueprint $table) {
            $table->string('alamat_ip')->nullable();
            $table->string('versi_proxmox')->nullable();
            $table->string('lokasi_rak')->nullable();
            $table->integer('tahun_pembelian')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('server_fisiks', function (Blueprint $table) {
            $table->dropColumn(['alamat_ip', 'versi_proxmox', 'lokasi_rak', 'tahun_pembelian']);
        });
    }
};
