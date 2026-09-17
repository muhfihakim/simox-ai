<?php

namespace App\Http\Controllers;

use App\Models\ServerFisik;
use App\Models\VirtualMachine;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Realtime search for VMs, Dinas (Fungsi Layanan), and Nodes.
     */
    public function liveSearch(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (empty($q) || strlen($q) < 1) {
            return response()->json([
                'query' => '',
                'vms' => [],
                'nodes' => [],
                'total' => 0,
            ]);
        }

        // 1. Search Virtual Machine / VPS / LXC
        $vms = VirtualMachine::with('serverFisik')
            ->where(function ($query) use ($q) {
                $query->where('hostname', 'like', "%{$q}%")
                    ->orWhere('ip_public_private', 'like', "%{$q}%")
                    ->orWhere('fungsi_layanan', 'like', "%{$q}%")
                    ->orWhere('os_distro', 'like', "%{$q}%")
                    ->orWhere('tipe', 'like', "%{$q}%");
            })
            ->limit(8)
            ->get()
            ->map(function ($vm) {
                return [
                    'id' => $vm->id,
                    'hostname' => $vm->hostname,
                    'ip' => $vm->ip_public_private,
                    'tipe' => strtoupper($vm->tipe),
                    'status' => $vm->status,
                    'dinas' => $vm->fungsi_layanan ?: '-',
                    'node' => $vm->serverFisik ? $vm->serverFisik->nama_server : '-',
                    'os' => $vm->os_distro ?: 'Linux',
                    'url' => route('vps.index') . '?search=' . urlencode($vm->hostname),
                ];
            });

        // 2. Search Server Fisik / Proxmox Nodes
        $nodes = ServerFisik::withCount('virtualMachines')
            ->where(function ($query) use ($q) {
                $query->where('nama_server', 'like', "%{$q}%")
                    ->orWhere('alamat_ip', 'like', "%{$q}%")
                    ->orWhere('lokasi_rak', 'like', "%{$q}%")
                    ->orWhere('versi_proxmox', 'like', "%{$q}%");
            })
            ->limit(6)
            ->get()
            ->map(function ($node) {
                return [
                    'id' => $node->id,
                    'nama' => $node->nama_server,
                    'ip' => $node->alamat_ip ?: '-',
                    'status' => $node->status,
                    'versi' => $node->versi_proxmox ?: '-',
                    'vm_count' => $node->virtual_machines_count,
                    'url' => route('nodes.index') . '?search=' . urlencode($node->nama_server),
                ];
            });

        return response()->json([
            'query' => $q,
            'vms' => $vms,
            'nodes' => $nodes,
            'total' => $vms->count() + $nodes->count(),
        ]);
    }
}
