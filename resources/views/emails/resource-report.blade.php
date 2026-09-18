<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pemakaian Resource Proxmox VE</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 24px 12px;
            -webkit-font-smoothing: antialiased;
        }
        .email-container {
            max-width: 620px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }
        .email-header {
            background: linear-gradient(135deg, #4f46e5, #7e22ce);
            padding: 28px 24px;
            color: #ffffff;
            text-align: left;
        }
        .brand-title {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .brand-subtitle {
            font-size: 13px;
            opacity: 0.9;
            margin-top: 4px;
        }
        .email-body {
            padding: 24px;
        }
        .greeting {
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 18px;
        }
        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 22px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }
        .stats-table td, .stats-table th {
            padding: 10px 14px;
            font-size: 13px;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
        }
        .stats-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
        }
        .stats-table tr:last-child td {
            border-bottom: none;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-info { background-color: #e0e7ff; color: #3730a3; }
        
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            margin: 20px 0 10px 0;
            display: flex;
            align-items: center;
            gap: 6px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 6px;
        }
        .ai-box {
            background-color: #fdf4ff;
            border: 1px solid #f5d0fe;
            border-left: 4px solid #a855f7;
            padding: 14px 16px;
            border-radius: 6px;
            font-size: 13px;
            line-height: 1.6;
            color: #3b0764;
            margin-bottom: 20px;
        }
        .ai-box table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 12px;
        }
        .ai-box th, .ai-box td {
            border: 1px solid #e9d5ff;
            padding: 6px 10px;
            text-align: left;
        }
        .ai-box th {
            background-color: #fae8ff;
            color: #581c87;
        }
        .attachment-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 13px;
            color: #166534;
            margin-bottom: 20px;
        }
        .email-footer {
            background-color: #f8fafc;
            padding: 16px 24px;
            font-size: 12px;
            color: #64748b;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="brand-title">SIMOX AI &bull; Infrastructure Monitor</div>
            <div class="brand-subtitle">Laporan Potret Telemetri Proxmox VE Server</div>
        </div>

        <!-- Body -->
        <div class="email-body">
            <p class="greeting">
                Halo <strong>{{ $userName }}</strong>,<br>
                Berikut adalah ringkasan potret pemakaian resource server Proxmox VE terkini yang Anda minta melalui <strong>SIMOX AI Agent</strong> pada <strong>{{ now()->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB</strong>.
            </p>

            <!-- Ringkasan Eksekutif (jika ada data cluster stats) -->
            @if(!empty($stats) && isset($stats['totalNodes']))
                <div class="section-title">📊 Ringkasan Kapasitas Kluster</div>
                <table class="stats-table">
                    <tr>
                        <th width="40%">Komponen</th>
                        <th width="35%">Nilai Real-Time</th>
                        <th width="25%">Status</th>
                    </tr>
                    <tr>
                        <td><strong>Server Nodes</strong></td>
                        <td>{{ $stats['onlineNodes'] }} Online / {{ $stats['totalNodes'] }} Total</td>
                        <td>
                            @if($stats['offlineNodes'] > 0)
                                <span class="badge badge-warning">{{ $stats['offlineNodes'] }} Offline</span>
                            @else
                                <span class="badge badge-success">Semua Online</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Virtual Machines (VM)</strong></td>
                        <td>{{ $stats['activeVms'] }} Running / {{ $stats['totalVms'] }} Unit</td>
                        <td><span class="badge badge-info">{{ $stats['activeVms'] }} Aktif</span></td>
                    </tr>
                    <tr>
                        <td><strong>Kontainer (LXC)</strong></td>
                        <td>{{ $stats['activeLxc'] }} Running / {{ $stats['totalLxc'] }} Unit</td>
                        <td><span class="badge badge-info">{{ $stats['activeLxc'] }} Aktif</span></td>
                    </tr>
                    <tr>
                        <td><strong>Alokasi Memori (RAM)</strong></td>
                        <td>{{ $stats['ramUsed'] }} GB / {{ $stats['ramTotal'] }} GB</td>
                        <td>
                            @if($stats['ramPct'] >= 85)
                                <span class="badge badge-danger">{{ $stats['ramPct'] }}% (Kritis)</span>
                            @elseif($stats['ramPct'] >= 65)
                                <span class="badge badge-warning">{{ $stats['ramPct'] }}% (Tinggi)</span>
                            @else
                                <span class="badge badge-success">{{ $stats['ramPct'] }}% (Stabil)</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Alokasi Storage Fisik</strong></td>
                        <td>{{ round($stats['diskUsed'] / 1000, 2) }} TB / {{ round($stats['diskTotal'] / 1000, 2) }} TB</td>
                        <td>
                            @if($stats['diskPct'] >= 85)
                                <span class="badge badge-danger">{{ $stats['diskPct'] }}%</span>
                            @else
                                <span class="badge badge-success">{{ $stats['diskPct'] }}% Terpakai</span>
                            @endif
                        </td>
                    </tr>
                </table>
            @endif

            <!-- Node Highlights jika ada yang berat -->
            @if(!empty($stats['heavyNodes']))
                <div class="section-title">⚠️ Node Perlu Perhatian (Utilisasi RAM &ge; 75%)</div>
                <table class="stats-table">
                    <tr>
                        <th>Node</th>
                        <th>IP Address</th>
                        <th>RAM Terpakai</th>
                        <th>Persentase</th>
                    </tr>
                    @foreach($stats['heavyNodes'] as $hNode)
                        <tr>
                            <td><strong>{{ $hNode['nama_server'] }}</strong></td>
                            <td>{{ $hNode['alamat_ip'] }}</td>
                            <td>{{ $hNode['ram_used'] }} / {{ $hNode['kapasitas_ram'] }} GB</td>
                            <td><span class="badge badge-danger">{{ $hNode['ram_pct'] }}%</span></td>
                        </tr>
                    @endforeach
                </table>
            @endif

            <!-- AI Insights -->
            <div class="section-title">✨ Hasil Analisis & Laporan AI Agent</div>
            <div class="ai-box">
                {!! \Illuminate\Support\Str::markdown($aiAnalysis) !!}
            </div>

            <!-- PDF Attachment Notice -->
            @if(!empty($pdfBinary))
                <div class="attachment-box">
                    📎 <strong>Lampiran PDF Tersedia:</strong> Dokumen laporan lengkap formal <code>{{ $pdfFilename }}</code> telah dilampirkan pada email ini.
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="email-footer">
            Email ini dikirim secara otomatis oleh <strong>SIMOX AI Infrastructure Assistant</strong>.<br>
            Jika Anda tidak meminta laporan ini, silakan hubungi tim administrator sistem Diskominfo.
        </div>
    </div>
</body>
</html>
