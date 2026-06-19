<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualMachine extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_fisik_id',
        'tipe',
        'hostname',
        'ip_public_private',
        'status',
        'allocated_cpu',
        'allocated_ram_gb',
        'allocated_disk_gb',
        'os_distro',
        'fungsi_layanan',
        'vlan',
    ];

    public function serverFisik()
    {
        return $this->belongsTo(ServerFisik::class, 'server_fisik_id');
    }
}
