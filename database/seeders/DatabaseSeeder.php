<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin Diskominfo',
            'email' => 'admin@mail.id',
            'password' => bcrypt('password123'),
        ]);

        // Seed 8 Nodes
        $nodes = [];
        for ($i = 1; $i <= 8; $i++) {
            $nodes[] = \App\Models\ServerFisik::create([
                'nama_server' => 'pve-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'status' => $i === 8 ? 'Offline' : 'Online',
                'kapasitas_cpu' => $i % 2 == 0 ? 64 : 32,
                'kapasitas_ram' => $i % 2 == 0 ? 256 : 128,
                'storage_fisik' => 2000, // 2000 GB
                'uptime' => $i === 8 ? null : rand(10, 60) . ' days, ' . rand(1, 23) . ' hours',
                'is_master' => $i === 1,
                'alamat_ip' => '192.168.1.' . (10 + $i),
                'versi_proxmox' => '8.1.3',
                'lokasi_rak' => 'Rak A' . ceil($i / 2),
                'tahun_pembelian' => 2022,
            ]);
        }

        // Seed 16 VMs (2 VMs per Node)
        $osDistros = ['Ubuntu 22.04 LTS', 'Ubuntu 20.04 LTS', 'Windows Server 2022', 'Debian 12', 'AlmaLinux 9'];
        $fungsiList = ['Web Server E-Gov', 'Database Keuangan', 'API Gateway', 'Aplikasi Kepegawaian', 'File Sharing', 'Backup Server', 'Proxy Server', 'Monitoring'];

        $vmCount = 1;
        foreach ($nodes as $index => $node) {
            for ($j = 1; $j <= 2; $j++) {
                $os = $osDistros[array_rand($osDistros)];
                \App\Models\VirtualMachine::create([
                    'server_fisik_id' => $node->id,
                    'hostname' => 'vm-app-' . str_pad($vmCount, 2, '0', STR_PAD_LEFT),
                    'ip_public_private' => '10.10.10.' . (100 + $vmCount),
                    'status' => ($node->status === 'Offline' || rand(1, 10) > 8) ? 'Stopped' : 'Running',
                    'allocated_cpu' => rand(2, 8),
                    'allocated_ram_gb' => rand(4, 16),
                    'allocated_disk_gb' => rand(50, 250),
                    'os_distro' => $os,
                    'fungsi_layanan' => $fungsiList[array_rand($fungsiList)],
                    'vlan' => '10' . rand(1, 5),
                ]);
                $vmCount++;
            }
        }
    }
}
