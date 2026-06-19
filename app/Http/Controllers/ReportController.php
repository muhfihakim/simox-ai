<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServerFisik;
use App\Models\VirtualMachine;

class ReportController extends Controller
{
    public function index()
    {
        $totalCpu = ServerFisik::sum('kapasitas_cpu');
        $allocatedCpu = VirtualMachine::sum('allocated_cpu');

        $totalRam = ServerFisik::sum('kapasitas_ram');
        $allocatedRam = VirtualMachine::sum('allocated_ram_gb');

        $totalStorage = ServerFisik::sum('storage_fisik');
        $allocatedStorage = VirtualMachine::sum('allocated_disk_gb');

        $totalVm = VirtualMachine::count();

        // Node stats: Total VM, Terpakai, Sisa
        $nodesData = ServerFisik::withCount('virtualMachines')
            ->withSum('virtualMachines as allocated_cpu', 'allocated_cpu')
            ->withSum('virtualMachines as allocated_ram', 'allocated_ram_gb')
            ->withSum('virtualMachines as allocated_disk', 'allocated_disk_gb')
            ->get();

        $cpuPerNodeLabels = $nodesData->pluck('nama_server')->toArray();
        $cpuPerNodeData = $nodesData->pluck('allocated_cpu')->map(fn($v) => $v ?? 0)->toArray();
        $cpuPerNodeSisa = $nodesData->map(fn($n) => max(0, $n->kapasitas_cpu - ($n->allocated_cpu ?? 0)))->toArray();

        $ramPerNodeData = $nodesData->pluck('allocated_ram')->map(fn($v) => $v ?? 0)->toArray();
        $ramPerNodeSisa = $nodesData->map(fn($n) => max(0, $n->kapasitas_ram - ($n->allocated_ram ?? 0)))->toArray();

        $storagePerNodeData = $nodesData->pluck('allocated_disk')->map(fn($v) => $v ?? 0)->toArray();
        $storagePerNodeSisa = $nodesData->map(fn($n) => max(0, $n->storage_fisik - ($n->allocated_disk ?? 0)))->toArray();

        // RAM per Bidang
        $ramPerBidang = VirtualMachine::selectRaw('fungsi_layanan, sum(allocated_ram_gb) as total_ram')
            ->groupBy('fungsi_layanan')
            ->get();
        $ramLabels = $ramPerBidang->pluck('fungsi_layanan')->map(fn($v) => $v ?? 'Unknown')->toArray();
        $ramData = $ramPerBidang->pluck('total_ram')->toArray();

        // Storage
        $storageTerpakai = $allocatedStorage;
        $storageSisa = max(0, $totalStorage - $allocatedStorage);
        $storageData = [$storageTerpakai, $storageSisa];

        // Tipe Aset (OS Distro)
        $osTypes = VirtualMachine::selectRaw('os_distro, count(*) as total')
            ->groupBy('os_distro')
            ->get();
        $osLabels = $osTypes->pluck('os_distro')->map(fn($v) => $v ?? 'Unknown')->toArray();
        $osData = $osTypes->pluck('total')->toArray();

        // Tabulasi
        $tabulasi = VirtualMachine::selectRaw('fungsi_layanan, count(*) as total_aset, sum(allocated_cpu) as total_cpu, sum(allocated_ram_gb) as total_ram')
            ->groupBy('fungsi_layanan')
            ->orderBy('total_ram', 'desc')
            ->get();

        return view('reports.index', compact(
            'totalCpu', 'allocatedCpu',
            'totalRam', 'allocatedRam',
            'totalStorage', 'allocatedStorage',
            'totalVm',
            'nodesData',
            'cpuPerNodeLabels', 'cpuPerNodeData', 'cpuPerNodeSisa',
            'ramPerNodeData', 'ramPerNodeSisa',
            'storagePerNodeData', 'storagePerNodeSisa',
            'ramLabels', 'ramData',
            'storageData',
            'osLabels', 'osData',
            'tabulasi'
        ));
    }

    public function exportPdf(\Illuminate\Http\Request $request)
    {
        $totalCpu = ServerFisik::sum('kapasitas_cpu');
        $allocatedCpu = VirtualMachine::sum('allocated_cpu');

        $totalRam = ServerFisik::sum('kapasitas_ram');
        $allocatedRam = VirtualMachine::sum('allocated_ram_gb');

        $totalStorage = ServerFisik::sum('storage_fisik');
        $allocatedStorage = VirtualMachine::sum('allocated_disk_gb');

        $totalVm = VirtualMachine::count();

        $nodesData = ServerFisik::withCount('virtualMachines')
            ->withSum('virtualMachines as allocated_cpu', 'allocated_cpu')
            ->withSum('virtualMachines as allocated_ram', 'allocated_ram_gb')
            ->withSum('virtualMachines as allocated_disk', 'allocated_disk_gb')
            ->get();

        $tabulasi = VirtualMachine::selectRaw('fungsi_layanan, count(*) as total_aset, sum(allocated_cpu) as total_cpu, sum(allocated_ram_gb) as total_ram')
            ->groupBy('fungsi_layanan')
            ->orderBy('total_ram', 'desc')
            ->get();

        $charts = $request->input('charts', []);

        $data = compact(
            'totalCpu', 'allocatedCpu',
            'totalRam', 'allocatedRam',
            'totalStorage', 'allocatedStorage',
            'totalVm', 'nodesData', 'tabulasi', 'charts'
        );

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf', $data);
        return $pdf->download('Laporan_Resource_SIMOX.pdf');
    }
}
