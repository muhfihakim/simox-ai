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
        $allVms = VirtualMachine::all();
        $vms = VirtualMachine::with('serverFisik')->orderBy('hostname', 'asc')->paginate(6);
        $nodes = \App\Models\ServerFisik::orderBy('nama_server', 'asc')->get();
        return view('vps.index', compact('vms', 'allVms', 'nodes'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'server_fisik_id' => 'required|exists:server_fisiks,id',
            'tipe' => 'required|in:VM,LXC',
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

        return redirect()->route('vps.index')->with('success', 'Data VPS berhasil ditambahkan!');
    }

    public function show(VirtualMachine $vm)
    {
        //
    }

    public function edit(VirtualMachine $vm)
    {
        //
    }

    public function update(Request $request, VirtualMachine $v)
    {
        // The parameter is $v instead of $vp or $vm to avoid issues if route binding is 'vp'
        $id = $request->route('vp');
        if(!is_object($id)) {
            $vmModel = VirtualMachine::findOrFail($id);
        } else {
            $vmModel = $id;
        }

        $validated = $request->validate([
            'server_fisik_id' => 'required|exists:server_fisiks,id',
            'tipe' => 'required|in:VM,LXC',
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

        $vmModel->update($validated);

        return redirect()->route('vps.index')->with('success', 'Data VPS berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $vm = VirtualMachine::findOrFail($id);
        $vm->delete();
        return redirect()->route('vps.index')->with('success', 'Data VPS berhasil dihapus!');
    }
}
