<?php

namespace App\Http\Controllers;

use App\Models\ServerFisik;
use Illuminate\Http\Request;

class ServerFisikController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allNodes = ServerFisik::all();
        $nodes = ServerFisik::orderBy('nama_server', 'asc')->paginate(6);
        return view('nodes.index', compact('nodes', 'allNodes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_server' => 'required|string|max:255',
            'alamat_ip' => 'nullable|string|max:255',
            'versi_proxmox' => 'nullable|string|max:255',
            'kapasitas_cpu' => 'nullable|integer',
            'kapasitas_ram' => 'nullable|integer',
            'lokasi_rak' => 'nullable|string|max:255',
        ]);

        $validated['status'] = $request->input('status', 'Online');
        
        ServerFisik::create($validated);

        return redirect()->route('nodes.index')->with('success', 'Data Node berhasil ditambahkan!');
    }

    public function show(ServerFisik $serverFisik)
    {
        //
    }

    public function edit(ServerFisik $serverFisik)
    {
        //
    }

    public function update(Request $request, ServerFisik $serverFisik)
    {
        $validated = $request->validate([
            'nama_server' => 'required|string|max:255',
            'alamat_ip' => 'nullable|string|max:255',
            'versi_proxmox' => 'nullable|string|max:255',
            'kapasitas_cpu' => 'nullable|integer',
            'kapasitas_ram' => 'nullable|integer',
            'lokasi_rak' => 'nullable|string|max:255',
            'status' => 'required|string',
        ]);

        $serverFisik->update($validated);

        return redirect()->route('nodes.index')->with('success', 'Data Node berhasil diperbarui!');
    }

    public function destroy(ServerFisik $serverFisik)
    {
        $serverFisik->delete();
        return redirect()->route('nodes.index')->with('success', 'Data Node berhasil dihapus!');
    }
}
