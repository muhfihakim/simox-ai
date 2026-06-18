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
        Schema::create('virtual_machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_fisik_id')->constrained('server_fisiks')->onDelete('cascade');
            $table->string('hostname');
            $table->string('ip_public_private');
            $table->string('status')->default('Running');
            $table->integer('allocated_cpu');
            $table->integer('allocated_ram_gb');
            $table->integer('allocated_disk_gb');
            $table->string('os_distro');
            $table->string('fungsi_layanan');
            $table->string('vlan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virtual_machines');
    }
};
