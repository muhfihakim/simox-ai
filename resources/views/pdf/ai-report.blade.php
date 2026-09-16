<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title ?? 'Laporan Analisis SIMOX-AI' }}</title>
    <style>
        @page {
            margin: 18mm 16mm 20mm 16mm;
            @bottom-right {
                content: "Halaman " counter(page);
            }
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5pt;
            line-height: 1.5;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        
        /* Header section */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #7e22ce;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .brand-title {
            font-size: 18pt;
            font-weight: bold;
            color: #7e22ce;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .brand-subtitle {
            font-size: 9pt;
            color: #64748b;
            margin-top: 2px;
        }
        .meta-right {
            text-align: right;
            font-size: 8.5pt;
            color: #64748b;
            line-height: 1.4;
        }
        
        /* Doc info card */
        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #7e22ce;
            border-radius: 4px;
            padding: 10px 14px;
            margin-bottom: 18px;
        }
        .info-table {
            width: 100%;
            font-size: 9pt;
        }
        .info-table td {
            padding: 2px 0;
        }
        .info-label {
            color: #64748b;
            width: 25%;
            font-weight: 600;
        }
        .info-val {
            color: #0f172a;
            font-weight: 500;
        }
        
        /* Content formatting */
        h1, h2, h3, h4, h5 {
            color: #0f172a;
            font-weight: 700;
            margin-top: 16px;
            margin-bottom: 8px;
            page-break-after: avoid;
        }
        h1 { font-size: 15pt; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        h2 { font-size: 13pt; }
        h3 { font-size: 11.5pt; color: #4338ca; }
        h4 { font-size: 10.5pt; }
        
        p {
            margin: 6px 0 10px 0;
            text-align: justify;
        }
        
        ul, ol {
            margin: 6px 0 12px 20px;
            padding: 0;
        }
        li {
            margin-bottom: 4px;
        }
        
        /* Tables */
        table.content-table,
        .report-body table {
            width: 100%;
            border-collapse: collapse;
            margin: 14px 0 18px 0;
            font-size: 9.5pt;
            page-break-inside: avoid;
        }
        table.content-table th,
        table.content-table td,
        .report-body table th,
        .report-body table td {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            text-align: left;
        }
        table.content-table th,
        .report-body table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
        }
        table.content-table tr:nth-child(even),
        .report-body table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        /* Code & blockquotes */
        pre {
            background-color: #0f172a;
            color: #f8fafc;
            padding: 10px;
            border-radius: 4px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 8.5pt;
            white-space: pre-wrap;
            word-break: break-all;
            margin: 10px 0;
        }
        code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 9pt;
            background-color: #f1f5f9;
            color: #7e22ce;
            padding: 1px 4px;
            border-radius: 3px;
        }
        blockquote {
            border-left: 3px solid #9333ea;
            background-color: #faf5ff;
            margin: 10px 0;
            padding: 8px 12px;
            font-style: italic;
            color: #475569;
        }
        
        /* Footer */
        .footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            height: 8mm;
            border-top: 1px solid #e2e8f0;
            font-size: 8pt;
            color: #94a3b8;
            padding-top: 4px;
        }
        .footer-left {
            float: left;
        }
        .footer-right {
            float: right;
        }
    </style>
</head>
<body>
    <!-- Running Footer -->
    <div class="footer">
        <div class="footer-left">SIMOX-AI • Platform Manajemen & Audit Proxmox VE Cerdas</div>
        <div class="footer-right">Laporan Otomatis Agen AI</div>
    </div>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td>
                <div class="brand-title">SIMOX-AI</div>
                <div class="brand-subtitle">Proxmox VE Intelligent Management & Audit Platform</div>
            </td>
            <td class="meta-right">
                <strong>{{ $filename ?? 'laporan-analisis.pdf' }}</strong><br>
                Digenerate: {{ $generatedAt ?? date('d M Y, H:i') }}<br>
                Modul: OpenClaw AI Auditor
            </td>
        </tr>
    </table>

    <!-- Metadata Card -->
    <div class="info-card">
        <table class="info-table">
            <tr>
                <td class="info-label">Judul Laporan:</td>
                <td class="info-val">{{ $title ?? 'Hasil Analisis & Audit Sistem' }}</td>
                <td class="info-label">Status Analisis:</td>
                <td class="info-val"><span style="color: #16a34a; font-weight: bold;">✓ Selesai Terverifikasi</span></td>
            </tr>
            <tr>
                <td class="info-label">Waktu Eksekusi:</td>
                <td class="info-val">{{ $generatedAt ?? date('d M Y, H:i') }}</td>
                <td class="info-label">Akses Proxmox:</td>
                <td class="info-val">Read-Only (Aman & Terisolasi)</td>
            </tr>
        </table>
    </div>

    <!-- Report Body Content -->
    <div class="report-body">
        {!! $content !!}
    </div>
</body>
</html>
