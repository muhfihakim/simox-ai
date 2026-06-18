<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServerFisik extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_server',
        'status',
        'kapasitas_cpu',
        'kapasitas_ram',
        'storage_fisik',
        'uptime',
        'is_master',
        'alamat_ip',
        'versi_proxmox',
        'lokasi_rak',
        'tahun_pembelian',
    ];

    public function virtualMachines()
    {
        return $this->hasMany(VirtualMachine::class);
    }
}
