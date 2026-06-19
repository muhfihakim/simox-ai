<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Utilisasi Resource Kluster</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; font-weight: bold; }
        .header h2 { margin: 5px 0 0 0; font-size: 14px; font-weight: normal; }
        .header p { margin: 5px 0 0 0; font-size: 10px; }
        h3 { font-size: 14px; border-bottom: 1px solid #000; padding-bottom: 3px; margin-top: 20px; color: #000; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .footer { margin-top: 40px; text-align: right; }
        .text-success { color: #000; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Utilisasi Resource Kluster</h1>
        <h2>Sistem Informasi Manajemen Operasional (SIMOX)</h2>
        <p>Tanggal Cetak: {{ date('d F Y') }}</p>
    </div>

    <h3>A. Ringkasan Eksekutif Kapasitas</h3>
    <table>
        <tr>
            <th>Total vCPU Alokasi</th>
            <th>Total RAM Alokasi</th>
            <th>Total Storage Terpakai</th>
            <th>Total Aset (VM/LXC)</th>
        </tr>
        <tr>
            <td>{{ $allocatedCpu }} / {{ $totalCpu }} Cores</td>
            <td>{{ $allocatedRam }} / {{ $totalRam }} GB</td>
            <td>{{ $allocatedStorage }} / {{ $totalStorage }} GB</td>
            <td>{{ $totalVm }}</td>
        </tr>
    </table>

    <h3>B. Status Kapasitas per Node (Server)</h3>
    <table>
        <thead>
            <tr>
                <th>Node (Server)</th>
                <th>Total VM/LXC</th>
                <th>vCPU (Terpakai / Sisa)</th>
                <th>RAM (Terpakai / Sisa)</th>
                <th>Storage (Terpakai / Sisa)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($nodesData as $node)
            <tr>
                <td><strong>{{ $node->nama_server }}</strong></td>
                <td>{{ $node->virtual_machines_count }}</td>
                <td>{{ $node->allocated_cpu ?? 0 }} / <span class="text-success">{{ max(0, $node->kapasitas_cpu - ($node->allocated_cpu ?? 0)) }}</span> Cores</td>
                <td>{{ $node->allocated_ram ?? 0 }} / <span class="text-success">{{ max(0, $node->kapasitas_ram - ($node->allocated_ram ?? 0)) }}</span> GB</td>
                <td>{{ $node->allocated_disk ?? 0 }} / <span class="text-success">{{ max(0, $node->storage_fisik - ($node->allocated_disk ?? 0)) }}</span> GB</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>C. Rincian Alokasi per Bidang / Instansi</h3>
    <table>
        <thead>
            <tr>
                <th>Peringkat</th>
                <th>Bidang / Instansi</th>
                <th>Total Aset</th>
                <th>Total vCPU</th>
                <th>Total RAM</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tabulasi as $index => $row)
            <tr>
                <td><strong>#{{ $index + 1 }}</strong></td>
                <td>{{ $row->fungsi_layanan ?? 'Unknown' }}</td>
                <td>{{ $row->total_aset }}</td>
                <td>{{ $row->total_cpu ?? 0 }} Cores</td>
                <td>{{ $row->total_ram ?? 0 }} GB</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="page-break-before: always;"></div>
    <h3>D. Lampiran Visualisasi Data</h3>
    <table style="border: none;">
        <tr>
            <td style="border: none; text-align: center; width: 50%;">
                <h4>vCPU Terpakai vs Sisa per Node</h4>
                @if(isset($charts['cpuNodeBarChart']))
                    <img src="{{ $charts['cpuNodeBarChart'] }}" width="100%">
                @endif
            </td>
            <td style="border: none; text-align: center; width: 50%;">
                <h4>RAM Terpakai vs Sisa per Node</h4>
                @if(isset($charts['ramNodeBarChart']))
                    <img src="{{ $charts['ramNodeBarChart'] }}" width="100%">
                @endif
            </td>
        </tr>
        <tr>
            <td style="border: none; text-align: center; width: 50%;">
                <h4>Storage Terpakai vs Sisa per Node</h4>
                @if(isset($charts['storageNodeBarChart']))
                    <img src="{{ $charts['storageNodeBarChart'] }}" width="100%">
                @endif
            </td>
            <td style="border: none; text-align: center; width: 50%;">
                <h4>Distribusi RAM per Bidang</h4>
                @if(isset($charts['ramPieChart']))
                    <img src="{{ $charts['ramPieChart'] }}" width="100%">
                @endif
            </td>
        </tr>
        <tr>
            <td style="border: none; text-align: center; width: 50%;">
                <h4>Penggunaan Storage Global</h4>
                @if(isset($charts['hddDoughnutChart']))
                    <img src="{{ $charts['hddDoughnutChart'] }}" width="100%">
                @endif
            </td>
            <td style="border: none; text-align: center; width: 50%;">
                <h4>Komposisi Tipe Aset</h4>
                @if(isset($charts['typePieChart']))
                    <img src="{{ $charts['typePieChart'] }}" width="100%">
                @endif
            </td>
        </tr>
    </table>

    <div class="footer">
        <p>Dibuat oleh Sistem SIMOX</p>
        <br><br><br>
        <p>___________________________</p>
        <p>Administrator</p>
    </div>
</body>
</html>
