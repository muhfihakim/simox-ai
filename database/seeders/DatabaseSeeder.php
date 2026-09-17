<?php

namespace Database\Seeders;

use App\Models\ServerFisik;
use App\Models\User;
use App\Models\VirtualMachine;
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
        User::firstOrCreate(
            ['email' => 'admin@mail.id'],
            [
                'name' => 'Admin Diskominfo',
                'password' => bcrypt('password123'),
            ]
        );

        // Seed 16 Nodes
        $nodes = [];
        for ($i = 1; $i <= 16; $i++) {
            $nodeName = 'pve-' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $rackName = $i <= 8 ? 'Rak A' . ceil($i / 2) : 'Rak B' . ceil(($i - 8) / 2);
            $isOffline = ($i === 8 || $i === 16);

            $nodes[] = ServerFisik::firstOrCreate(
                ['nama_server' => $nodeName],
                [
                    'status' => $isOffline ? 'Offline' : 'Online',
                    'kapasitas_cpu' => $i % 2 == 0 ? 64 : 32,
                    'kapasitas_ram' => $i % 2 == 0 ? 256 : 128,
                    'storage_fisik' => 2000, // 2000 GB
                    'uptime' => $isOffline ? null : rand(10, 60) . ' days, ' . rand(1, 23) . ' hours',
                    'is_master' => $i === 1,
                    'alamat_ip' => '192.168.1.' . (10 + $i),
                    'versi_proxmox' => '8.1.3',
                    'lokasi_rak' => $rackName,
                    'tahun_pembelian' => 2022 + ($i > 8 ? 1 : 0),
                ]
            );
        }

        // Seed 32 VMs (2 VMs per Node)
        $osDistros = [
            'Ubuntu 22.04 LTS', 'Ubuntu 20.04 LTS', 'Windows Server 2022',
            'Debian 12', 'AlmaLinux 9', 'CentOS Stream 9'
        ];
        $fungsiList = [
            'Web Server E-Gov', 'Database Keuangan', 'API Gateway', 'Aplikasi Kepegawaian',
            'File Sharing', 'Backup Server', 'Proxy Server', 'Monitoring Prometheus',
            'Portal Satu Data', 'Sistem Kependudukan', 'Mail Server', 'DNS Server Trust',
            'Aplikasi Smart City', 'GIS Pemetaan', 'SIASN Service', 'OpenClaw Gateway'
        ];
        $tipeList = ['vm', 'lxc'];

        $vmCount = 1;
        foreach ($nodes as $index => $node) {
            for ($j = 1; $j <= 2; $j++) {
                $hostname = 'vm-app-' . str_pad($vmCount, 2, '0', STR_PAD_LEFT);
                $os = $osDistros[array_rand($osDistros)];
                $tipe = $tipeList[array_rand($tipeList)];
                $fungsi = $fungsiList[($vmCount - 1) % count($fungsiList)];

                VirtualMachine::firstOrCreate(
                    ['hostname' => $hostname],
                    [
                        'server_fisik_id' => $node->id,
                        'ip_public_private' => '10.10.10.' . (100 + $vmCount),
                        'status' => ($node->status === 'Offline' || rand(1, 10) > 8) ? 'Stopped' : 'Running',
                        'allocated_cpu' => rand(2, 8),
                        'allocated_ram_gb' => rand(4, 16),
                        'allocated_disk_gb' => rand(50, 250),
                        'os_distro' => $os,
                        'fungsi_layanan' => $fungsi,
                        'vlan' => '10' . rand(1, 5),
                        'tipe' => $tipe,
                    ]
                );
                $vmCount++;
            }
        }
    }
}
