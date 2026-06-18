<?php

namespace App\Http\Controllers;

use App\Models\VirtualMachine;
use Illuminate\Http\Request;

class VirtualMachineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vms = VirtualMachine::with('serverFisik')->orderBy('hostname', 'asc')->get();
        $nodes = \App\Models\ServerFisik::orderBy('nama_server', 'asc')->get();
        return view('vms.index', compact('vms', 'nodes'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'server_fisik_id' => 'required|exists:server_fisiks,id',
            'hostname' => 'required|string|max:255',
            'ip_public_private' => 'required|string|max:255',
            'status' => 'required|string',
            'allocated_cpu' => 'required|integer|min:1',
            'allocated_ram_gb' => 'required|integer|min:1',
            'allocated_disk_gb' => 'required|integer|min:1',
            'os_distro' => 'required|string|max:255',
            'fungsi_layanan' => 'required|string|max:255',
            'vlan' => 'nullable|string|max:255',
        ]);

        VirtualMachine::create($validated);

        return redirect()->route('vms.index')->with('success', 'Data Virtual Machine berhasil ditambahkan!');
    }

    public function show(VirtualMachine $virtualMachine)
    {
        //
    }

    public function edit(VirtualMachine $virtualMachine)
    {
        //
    }

    public function update(Request $request, VirtualMachine $virtualMachine)
    {
        $validated = $request->validate([
            'server_fisik_id' => 'required|exists:server_fisiks,id',
            'hostname' => 'required|string|max:255',
            'ip_public_private' => 'required|string|max:255',
            'status' => 'required|string',
            'allocated_cpu' => 'required|integer|min:1',
            'allocated_ram_gb' => 'required|integer|min:1',
            'allocated_disk_gb' => 'required|integer|min:1',
            'os_distro' => 'required|string|max:255',
            'fungsi_layanan' => 'required|string|max:255',
            'vlan' => 'nullable|string|max:255',
        ]);

        $virtualMachine->update($validated);

        return redirect()->route('vms.index')->with('success', 'Data Virtual Machine berhasil diperbarui!');
    }

    public function destroy(VirtualMachine $virtualMachine)
    {
        $virtualMachine->delete();
        return redirect()->route('vms.index')->with('success', 'Data Virtual Machine berhasil dihapus!');
    }
}
