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
        Schema::create('server_fisiks', function (Blueprint $table) {
            $table->id();
            $table->string('nama_server');
            $table->string('status')->default('Online');
            $table->integer('kapasitas_cpu')->nullable();
            $table->integer('kapasitas_ram')->nullable();
            $table->string('storage_fisik')->nullable();
            $table->string('uptime')->nullable();
            $table->boolean('is_master')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_fisiks');
    }
};
