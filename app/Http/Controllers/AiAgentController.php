<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\ResourceReportMail;
use App\Models\ServerFisik;
use App\Models\VirtualMachine;

class AiAgentController extends Controller
{
    private function executeOpenClaw($sessionId, $message)
    {
        $baseUrl = rtrim(config('services.openclaw.base_url', env('OPENCLAW_BASE_URL', 'http://203.2.151.17:18789')), '/');
        $token = config('services.openclaw.token', env('OPENCLAW_TOKEN'));
        $model = config('services.openclaw.model', env('OPENCLAW_MODEL', 'openclaw'));
        $timeout = (int) config('services.openclaw.timeout', env('OPENCLAW_TIMEOUT', 300));

        $client = Http::timeout($timeout);

        if (!empty($token)) {
            $client = $client->withToken($token);
        }

        $url = $baseUrl . '/v1/chat/completions';

        $systemPrompt = "Anda adalah SIMOX AI Agent, asisten cerdas khusus manajemen infrastruktur server Proxmox VE (PVE) dan aplikasi inventaris SIMOX Diskominfo.\n\n"
            . "LINGKUP TUGAS UTAMA ANDA HANYA MENCAKUP:\n"
            . "1. Pengelolaan, pemantauan, analisis utilisasi beban (CPU, RAM, Disk, Jaringan), audit kesehatan, dan optimasi kluster Proxmox VE, node server fisik, Virtual Machine (VM), dan Kontainer Linux (LXC).\n"
            . "2. Data inventaris server, VM, LXC, dan status operasional pada aplikasi SIMOX.\n"
            . "3. Pembuatan laporan telemetri resmi dan berkas dokumen PDF inventaris/resource di workspace.\n\n"
            . "BATASAN & ATURAN MUTLAK:\n"
            . "- Anda DILARANG KERAS melayani, menjawab, atau mendiskusikan topik di luar Proxmox VE, virtualisasi server, infrastruktur jaringan Diskominfo, dan aplikasi SIMOX (seperti resep makanan, hiburan, dongeng, politik, tugas umum, atau obrolan santai di luar teknis infrastruktur).\n"
            . "- Jika ada prompt atau pertanyaan pengguna di luar lingkup Proxmox VE atau SIMOX, tolak dengan sopan, ramah, dan ringkas. Tegaskan bahwa Anda khusus bertugas sebagai asisten infrastruktur Proxmox VE & aplikasi SIMOX, lalu sarankan pengguna untuk mengajukan pertanyaan seputar server, node, atau VM Proxmox.";


        $messages = [];
        if ($message !== '/reset') {
            $messages[] = [
                'role' => 'system',
                'content' => $systemPrompt,
            ];
        }
        $messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'user' => $sessionId,
        ];

        try {
            $response = $client->post($url, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('OpenClaw API response not successful: ' . $response->status() . ' - ' . $response->body());
            return $response->json();
        } catch (\Exception $e) {
            Log::error('OpenClaw HTTP Exception: ' . $e->getMessage());
            return null;
        }
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'session_id' => 'nullable|string',
        ]);

        $message = $request->input('message');
        $sessionId = $request->input('session_id', 'simox-web-widget-global');

        // 1. Jika permintaan eksplisit mengarah ke DATABASE / aplikasi lokal (kata kunci: db, database, app ini, dll)
        if ($this->isDatabaseSpecificRequest($message)) {
            if ($this->isEmailReportRequest($message)) {
                $targetEmail = $this->extractTargetEmail($message, $request);
                return $this->handleDatabaseEmailReport($request, $message, $targetEmail);
            }
            return $this->handleDatabaseChatQuery($request, $message, $sessionId);
        }

        // 2. Default: Permintaan Proxmox VE Real-Time langsung via OpenClaw Gateway API
        return $this->handleOpenClawChat($request, $message, $sessionId);
    }

    public function index()
    {
        return view('ai.index');
    }

    public function status()
    {
        $baseUrl = rtrim(config('services.openclaw.base_url', env('OPENCLAW_BASE_URL', 'http://203.2.151.17:18789')), '/');
        $token = config('services.openclaw.token', env('OPENCLAW_TOKEN'));

        $startTime = microtime(true);

        try {
            $client = Http::timeout(5);
            if (!empty($token)) {
                $client = $client->withToken($token);
            }

            $response = $client->get($baseUrl . '/v1/models');
            $latency = round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $models = $response->json('data') ?? [];
                return response()->json([
                    'online' => true,
                    'status' => 'Aktif',
                    'latency_ms' => $latency,
                    'models' => $models,
                    'gateway' => $baseUrl,
                ]);
            }

            return response()->json([
                'online' => false,
                'status' => 'Error (' . $response->status() . ')',
                'latency_ms' => $latency,
                'message' => $response->body(),
            ], 502);
        } catch (\Exception $e) {
            return response()->json([
                'online' => false,
                'status' => 'Terputus',
                'message' => $e->getMessage(),
            ], 503);
        }
    }

    public function reset(Request $request)
    {
        $sessionId = $request->input('session_id', 'simox-web-widget-global');
        $this->executeOpenClaw($sessionId, '/reset');
        return response()->json(['message' => 'Percakapan berhasil direset.']);
    }

    /**
     * Fetch media file directly from OpenClaw Gateway workspace.
     */
    private function fetchOpenClawMedia(?string $source, ?string $filename = null): ?array
    {
        $baseUrl = rtrim(config('services.openclaw.base_url', env('OPENCLAW_BASE_URL', 'http://203.2.151.17:18789')), '/');
        $token = config('services.openclaw.token', env('OPENCLAW_TOKEN'));

        // Handle full OpenClaw media URL if passed as source
        if (!empty($source) && str_contains($source, 'source=')) {
            $parsed = parse_url($source, PHP_URL_QUERY);
            if ($parsed) {
                parse_str($parsed, $queryParams);
                if (!empty($queryParams['source'])) {
                    $source = $queryParams['source'];
                }
            }
        }

        $candidates = [];

        if (!empty($source)) {
            $source = trim($source);
            $candidates[] = $source;
            if (!str_starts_with($source, '/')) {
                $candidates[] = '/root/.openclaw/workspace/' . ltrim($source, '/');
            } else {
                $candidates[] = '/root/.openclaw/workspace/' . basename($source);
            }
        }

        if (!empty($filename)) {
            $cleanFilename = basename(trim($filename));
            $candidates[] = '/root/.openclaw/workspace/' . $cleanFilename;
            $candidates[] = $cleanFilename;
        }

        $candidates = array_unique(array_filter($candidates));

        $client = Http::timeout(20);
        if (!empty($token)) {
            $client = $client->withToken($token);
        }

        foreach ($candidates as $candidate) {
            try {
                $url = $baseUrl . '/__openclaw__/assistant-media?source=' . urlencode($candidate);
                $res = $client->get($url);

                if ($res->successful()) {
                    return [
                        'body' => $res->body(),
                        'content_type' => $res->header('Content-Type') ?: 'application/pdf',
                        'filename' => basename($candidate),
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("Gagal fetch media OpenClaw ({$candidate}): " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Endpoint untuk mengunduh langsung berkas asli yang dibuat oleh OpenClaw.
     */
    public function downloadFile(Request $request)
    {
        $source = $request->query('source') ?: $request->input('source');
        $filename = $request->query('filename') ?: $request->input('filename');

        if (empty($source) && empty($filename)) {
            abort(400, 'Parameter source atau filename diperlukan.');
        }

        // Cek apakah berkas ada di Cache (misal baru digenerate oleh AI Agent)
        if (!empty($filename) && Cache::has('ai_pdf_' . $filename)) {
            $rawCache = Cache::get('ai_pdf_' . $filename);
            $pdfData = base64_decode($rawCache, true) ?: $rawCache;
            return response($pdfData, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($pdfData),
                'Cache-Control' => 'no-cache, private',
            ]);
        }

        $openClawFile = $this->fetchOpenClawMedia($source, $filename);

        if ($openClawFile) {
            $downloadFilename = $filename ?: $openClawFile['filename'];
            if (!str_ends_with(strtolower($downloadFilename), '.pdf') && str_contains($openClawFile['content_type'], 'pdf')) {
                $downloadFilename .= '.pdf';
            }

            return response($openClawFile['body'], 200, [
                'Content-Type' => $openClawFile['content_type'],
                'Content-Disposition' => 'attachment; filename="' . $downloadFilename . '"',
                'Content-Length' => strlen($openClawFile['body']),
                'Cache-Control' => 'no-cache, private',
            ]);
        }

        abort(404, 'Berkas tidak ditemukan di server OpenClaw.');
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'content' => 'nullable|string',
            'filename' => 'nullable|string',
            'title' => 'nullable|string',
            'source' => 'nullable|string',
        ]);

        $source = $request->input('source');
        $filename = $request->input('filename') ?: 'laporan-analisis-simox.pdf';
        $rawContent = $request->input('content', '');

        // 0. Cek apakah berkas ada di Cache
        if (!empty($filename) && Cache::has('ai_pdf_' . $filename)) {
            $rawCache = Cache::get('ai_pdf_' . $filename);
            $pdfData = base64_decode($rawCache, true) ?: $rawCache;
            return response($pdfData, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($pdfData),
                'Cache-Control' => 'no-cache, private',
            ]);
        }

        // 1. Cek apakah ada source langsung atau nama berkas spesifik di OpenClaw
        if (!empty($source) || (!empty($filename) && !in_array($filename, ['laporan-analisis.pdf', 'laporan-analisis-simox.pdf']))) {
            $openClawFile = $this->fetchOpenClawMedia($source, $filename);
            if ($openClawFile) {
                $downloadFilename = (!empty($filename) && !in_array($filename, ['laporan-analisis.pdf', 'laporan-analisis-simox.pdf'])) 
                    ? $filename 
                    : $openClawFile['filename'];

                if (!str_ends_with(strtolower($downloadFilename), '.pdf') && str_contains($openClawFile['content_type'], 'pdf')) {
                    $downloadFilename .= '.pdf';
                }

                return response($openClawFile['body'], 200, [
                    'Content-Type' => $openClawFile['content_type'],
                    'Content-Disposition' => 'attachment; filename="' . $downloadFilename . '"',
                    'Content-Length' => strlen($openClawFile['body']),
                    'Cache-Control' => 'no-cache, private',
                ]);
            }
        }

        // 2. Cek apakah teks pesan merujuk berkas OpenClaw workspace
        if (!empty($rawContent)) {
            if (preg_match('/(\/root\/\.openclaw\/workspace\/[^\s\)\'\"\]]+\.pdf)/i', $rawContent, $m)) {
                $openClawFile = $this->fetchOpenClawMedia($m[1], basename($m[1]));
                if ($openClawFile) {
                    $downloadFilename = basename($m[1]);
                    return response($openClawFile['body'], 200, [
                        'Content-Type' => $openClawFile['content_type'],
                        'Content-Disposition' => 'attachment; filename="' . $downloadFilename . '"',
                        'Content-Length' => strlen($openClawFile['body']),
                        'Cache-Control' => 'no-cache, private',
                    ]);
                }
            }

            if (preg_match('/(?:Nama\s*file|Berkas|File|Dokumen):\s*([a-zA-Z0-9_\-\.]+\.pdf)/i', $rawContent, $m)) {
                $openClawFile = $this->fetchOpenClawMedia(null, $m[1]);
                if ($openClawFile) {
                    return response($openClawFile['body'], 200, [
                        'Content-Type' => $openClawFile['content_type'],
                        'Content-Disposition' => 'attachment; filename="' . $m[1] . '"',
                        'Content-Length' => strlen($openClawFile['body']),
                        'Cache-Control' => 'no-cache, private',
                    ]);
                }
            }
        }

        // 3. Fallback: Jika bukan berkas OpenClaw, buat PDF menggunakan DomPDF seperti biasa
        if (empty($rawContent)) {
            return response()->json(['error' => 'Konten atau berkas tidak ditemukan.'], 404);
        }

        if (!str_ends_with(strtolower($filename), '.pdf')) {
            $filename .= '.pdf';
        }

        // Ambil judul atau fallback
        $title = $request->input('title');
        if (empty($title)) {
            if (preg_match('/^#+\s+(.+)$/m', $rawContent, $m)) {
                $title = trim($m[1]);
            } else {
                $title = 'Laporan Analisis SIMOX-AI Proxmox VE';
            }
        }

        // Bersihkan teks download card / path jika ada di content agar tidak dobel di PDF
        $cleanContent = preg_replace('/Lokasi:\s*\/root\/\.openclaw\/workspace\/[^\s]+/i', '', $rawContent);

        $htmlContent = Str::markdown($cleanContent);

        $generatedAt = now()->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') . ' WIB';

        $html = view('pdf.ai-report', [
            'title' => $title,
            'filename' => $filename,
            'content' => $htmlContent,
            'generatedAt' => $generatedAt,
        ])->render();

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->download($filename);
    }

    /**
     * Deteksi apakah prompt pengguna meminta data dari database lokal/internal aplikasi.
     */
    private function isDatabaseSpecificRequest(string $message): bool
    {
        $msg = strtolower($message);
        $patterns = [
            '/\bdb\b/i',
            '/\bdatabase\b/i',
            '/\bapp ini\b/i',
            '/\baplikasi ini\b/i',
            '/\btabel\b/i',
            '/\bdata lokal\b/i',
            '/\bdatabase lokal\b/i',
            '/\bdb lokal\b/i',
            '/\bdi db\b/i',
            '/\bdi database\b/i',
            '/\bpada database\b/i',
            '/\bpada db\b/i',
            '/\bseeder\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $msg)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cek apakah pesan pengguna bermaksud meminta pengiriman laporan ke email.
     */
    private function isEmailReportRequest(string $message): bool
    {
        $msg = strtolower($message);

        // Harus mengandung kata kunci email atau format email
        $hasEmailKeyword = str_contains($msg, 'email') 
            || str_contains($msg, 'e-mail') 
            || str_contains($msg, 'surel') 
            || str_contains($msg, 'mail')
            || (bool) preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $msg);

        if (!$hasEmailKeyword) {
            return false;
        }

        // Dan ada kata aksi pengiriman atau kata terkait laporan/resource
        $actionKeywords = [
            'kirim', 'send', 'lapor', 'resource', 'rekap', 
            'ringkas', 'hasil', 'tolong', 'minta', 'forward', 'share', 'ke email'
        ];

        foreach ($actionKeywords as $keyword) {
            if (str_contains($msg, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Dapatkan alamat email tujuan dari teks atau dari user yang sedang login.
     */
    private function extractTargetEmail(string $message, Request $request): ?string
    {
        // 1. Cek jika pengguna menulis email eksplisit di prompt (misal: "kirim ke admin@diskominfo.go.id")
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $message, $matches)) {
            return $matches[0];
        }

        // 2. Cek akun yang sedang login di sesi
        if (Auth::check() && !empty(Auth::user()->email)) {
            return Auth::user()->email;
        }

        if ($request->user() && !empty($request->user()->email)) {
            return $request->user()->email;
        }

        // 3. Fallback jika sesi web menyimpan user_id
        if ($userId = $request->session()->get('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d') ?? session('user_id')) {
            $u = \App\Models\User::find($userId);
            if ($u && !empty($u->email)) {
                return $u->email;
            }
        }

        return null;
    }

    /**
     * Ekstrak path/nama file PDF dari respons OpenClaw dan simpan ke cache jika ada.
     */
    private function extractAndCacheOpenClawPdf(string $text): ?array
    {
        $pdfSource = null;
        $pdfFilename = null;

        // Cari path workspace /root/.openclaw/... atau /rot/... atau nama file PDF
        if (preg_match('/(\/(?:root|rot)\/\.openclaw\/workspace\/[^\s\)\'\"\]]+\.pdf)/i', $text, $m)) {
            $pdfSource = $m[1];
            $pdfFilename = basename($m[1]);
        } elseif (preg_match('/(?:Nama\s*file|File|Berkas|Dokumen|Lokasi|Output)[\s\S]{0,60}?([a-zA-Z0-9_\-\.]+\.pdf)/i', $text, $m)) {
            $pdfFilename = $m[1];
        } elseif (preg_match('/([a-zA-Z0-9_\-\.]+\.pdf)/i', $text, $m)) {
            $pdfFilename = $m[1];
        }

        if ($pdfSource || $pdfFilename) {
            $media = $this->fetchOpenClawMedia($pdfSource, $pdfFilename);
            if ($media && !empty($media['body'])) {
                $filename = $pdfFilename ?: $media['filename'];
                Cache::put('ai_pdf_' . $filename, base64_encode($media['body']), now()->addHours(2));
                return [
                    'source' => $pdfSource,
                    'filename' => $filename,
                    'body' => $media['body'],
                ];
            }
        }

        return null;
    }

    /**
     * Tangani chat interaksi langsung dengan Proxmox VE via OpenClaw Real API.
     */
    private function handleOpenClawChat(Request $request, string $message, string $sessionId = 'simox-web-widget-global')
    {
        $isEmailRequested = $this->isEmailReportRequest($message);
        $isPdfRequested = (bool) preg_match('/\bpdf\b/i', $message);
        $targetEmail = null;

        if ($isEmailRequested) {
            $targetEmail = $this->extractTargetEmail($message, $request);
            if (empty($targetEmail)) {
                return response()->json([
                    'reply' => "⚠️ **Tidak Dapat Menemukan Alamat Email**\n\nUntuk mengirimkan laporan Proxmox VE ke email, silakan login ke akun admin Anda terlebih dahulu atau cantumkan alamat email tujuan di pesan Anda (contoh: *'tolong kirim laporan container 9010 ke email admin@diskominfo.go.id'*)."
                ]);
            }
        }

        try {
            set_time_limit(300);

            // Jika ada permintaan kirim email, berikan catatan sistem agar OpenClaw fokus inspeksi PVE & buat PDF di workspace
            $openClawMessage = $message;
            if ($isEmailRequested) {
                $openClawMessage .= "\n\n(Catatan Sistem: Pengiriman email ke pengguna ditangani otomatis oleh aplikasi web SIMOX. "
                    . ($isPdfRequested 
                        ? "Pastikan dokumen laporan PDF dibuat dan disimpan di workspace /root/.openclaw/workspace/ agar SIMOX dapat melampirkannya ke email pengguna." 
                        : "Lakukan inspeksi dan analisis Proxmox VE secara langsung via API/CLI.") 
                    . ")";
            }

            $data = $this->executeOpenClaw($sessionId, $openClawMessage);

            if ($data && isset($data['error'])) {
                $errorMsg = $data['error']['message'] ?? '';
                if (stripos($errorMsg, 'Context overflow') !== false) {
                    $this->executeOpenClaw($sessionId, '/reset');
                    $data = $this->executeOpenClaw($sessionId, $openClawMessage);
                }
            }

            $replyText = null;
            if ($data) {
                $replyText = $data['choices'][0]['message']['content'] 
                    ?? $data['result']['payloads'][0]['text'] 
                    ?? $data['result']['finalAssistantVisibleText'] 
                    ?? null;
            }

            if (empty($replyText)) {
                return response()->json([
                    'reply' => 'Maaf, terjadi kesalahan saat menghubungi gateway AI di server Proxmox VE. Pastikan endpoint OpenClaw aktif.'
                ], 500);
            }

            // Cek apakah OpenClaw membuat berkas PDF di workspace
            $cachedPdf = $this->extractAndCacheOpenClawPdf($replyText);

            // Jika ada permintaan kirim email, kirim via Brevo SMTP
            if ($isEmailRequested) {
                $targetName = Auth::check() 
                    ? Auth::user()->name 
                    : ($request->user()?->name ?? 'Administrator SIMOX');

                $pdfBinary = $cachedPdf ? $cachedPdf['body'] : null;
                $pdfFilename = $cachedPdf ? $cachedPdf['filename'] : 'laporan-proxmox-ve.pdf';

                $emailSent = false;
                try {
                    Mail::to($targetEmail)->send(new ResourceReportMail(
                        $targetName,
                        [], // Data real API dinamis, render markdown di template
                        $replyText,
                        $pdfBinary,
                        $pdfFilename
                    ));
                    $emailSent = true;
                } catch (\Throwable $e) {
                    Log::error("Gagal mengirim email laporan Proxmox VE ke {$targetEmail}: " . $e->getMessage());
                }

                if ($emailSent) {
                    $replyText .= "\n\n---\n📧 **Laporan Proxmox VE telah berhasil dikirimkan ke email Anda:** `{$targetEmail}`";
                    if ($pdfBinary) {
                        $replyText .= "\n📎 Dokumen resmi PDF (`{$pdfFilename}`) telah dilampirkan langsung di email.";
                    }
                } else {
                    $replyText .= "\n\n---\n⚠️ *Catatan: Analisis Proxmox VE selesai, namun pengiriman email ke `{$targetEmail}` terkendala server SMTP.*";
                }
            }

            // Pastikan format Berkas: nama.pdf ada jika PDF berhasil digenerate agar kartu unduh di web widget muncul
            if ($cachedPdf && !preg_match('/(?:Nama\s*file|File|Berkas|Dokumen|Lokasi|Output)[\s\S]{0,60}?' . preg_quote($cachedPdf['filename'], '/') . '/i', $replyText)) {
                $replyText .= "\n\nBerkas: " . $cachedPdf['filename'];
            }

            return response()->json([
                'reply' => $replyText
            ]);

        } catch (\Exception $e) {
            Log::error('OpenClaw Controller Exception: ' . $e->getMessage());
            return response()->json(['reply' => 'Maaf, agen AI sedang tidak aktif atau tidak dapat dijalankan.'], 500);
        }
    }

    /**
     * Tangani query yang menanyakan database aplikasi internal SIMOX.
     */
    private function handleDatabaseChatQuery(Request $request, string $message, string $sessionId = 'simox-web-widget-global')
    {
        $totalNodes = ServerFisik::count();
        $onlineNodes = ServerFisik::where('status', 'Online')->count();
        $offlineNodes = max(0, $totalNodes - $onlineNodes);

        $totalVms = VirtualMachine::where('tipe', 'VM')->count();
        $activeVms = VirtualMachine::where('tipe', 'VM')->where('status', 'Running')->count();

        $totalLxc = VirtualMachine::where('tipe', 'LXC')->count();
        $activeLxc = VirtualMachine::where('tipe', 'LXC')->where('status', 'Running')->count();

        $ramTotal = (int) ServerFisik::sum('kapasitas_ram');
        $ramUsed = (int) VirtualMachine::sum('allocated_ram_gb');
        $ramPct = $ramTotal > 0 ? round(($ramUsed / $ramTotal) * 100) : 0;

        $diskTotal = (int) ServerFisik::sum('storage_fisik');
        $diskUsed = (int) VirtualMachine::sum('allocated_disk_gb');
        $diskPct = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100) : 0;

        $nodes = ServerFisik::withCount('virtualMachines')
            ->orderBy('nama_server', 'asc')
            ->get()
            ->map(fn($n) => "{$n->nama_server} ({$n->status}, IP: {$n->alamat_ip}, RAM: {$n->kapasitas_ram}GB, VM/LXC: {$n->virtual_machines_count})")
            ->implode('; ');

        $dbSummary = "Informasi Database Aplikasi SIMOX:\n"
            . "- Server Fisik (Node): {$totalNodes} total ({$onlineNodes} Online, {$offlineNodes} Offline)\n"
            . "- Virtual Machines (VM): {$totalVms} total ({$activeVms} Running)\n"
            . "- Kontainer Linux (LXC): {$totalLxc} total ({$activeLxc} Running)\n"
            . "- Alokasi RAM: {$ramUsed} GB / {$ramTotal} GB ({$ramPct}%)\n"
            . "- Alokasi Storage: " . round($diskUsed / 1000, 2) . " TB / " . round($diskTotal / 1000, 2) . " TB ({$diskPct}%)\n"
            . "- Daftar Node: {$nodes}\n";

        $prompt = "Berikut adalah data riil dari database internal aplikasi SIMOX:\n"
            . $dbSummary . "\n"
            . "Pertanyaan pengguna: {$message}\n\n"
            . "Jawab pertanyaan pengguna secara akurat berdasarkan data database aplikasi SIMOX di atas. Jelaskan secara ramah dan profesional.";

        try {
            set_time_limit(300);
            $data = $this->executeOpenClaw($sessionId, $prompt);
            if ($data && isset($data['choices'][0]['message']['content'])) {
                return response()->json(['reply' => $data['choices'][0]['message']['content']]);
            }
            if ($data && isset($data['result']['payloads'][0]['text'])) {
                return response()->json(['reply' => $data['result']['payloads'][0]['text']]);
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal menghubungi OpenClaw untuk query database: ' . $e->getMessage());
        }

        // Fallback jika OpenClaw tidak merespons
        $reply = "📊 **Data Telemetri Database Aplikasi SIMOX:**\n\n"
            . "- **Server Fisik (Node)**: {$totalNodes} Node ({$onlineNodes} Online, {$offlineNodes} Offline)\n"
            . "- **Virtual Machines**: {$totalVms} VM ({$activeVms} Running)\n"
            . "- **Kontainer LXC**: {$totalLxc} Unit ({$activeLxc} Running)\n"
            . "- **Alokasi RAM**: {$ramUsed} GB / {$ramTotal} GB ({$ramPct}%)\n"
            . "- **Alokasi Storage**: " . round($diskUsed / 1000, 2) . " TB / " . round($diskTotal / 1000, 2) . " TB ({$diskPct}%)\n\n"
            . "💡 *Data diambil langsung dari tabel database MySQL aplikasi SIMOX.*";

        return response()->json(['reply' => $reply]);
    }

    /**
     * Buat snapshot laporan resource dari database lokal dan kirimkan ke email penerima.
     */
    private function handleDatabaseEmailReport(Request $request, string $message, ?string $targetEmail)
    {
        if (empty($targetEmail)) {
            return response()->json([
                'reply' => "⚠️ **Tidak Dapat Menemukan Alamat Email**\n\nUntuk mengirimkan laporan resource database ke email, silakan login ke akun admin Anda terlebih dahulu atau cantumkan alamat email tujuan di pesan Anda (contoh: *'tolong kirim laporan resource database ke email admin@diskominfo.go.id'*)."
            ]);
        }

        $targetName = Auth::check() 
            ? Auth::user()->name 
            : ($request->user()?->name ?? 'Administrator SIMOX');

        // 1. Ambil potret telemetri dari tabel database MySQL
        $totalNodes = ServerFisik::count();
        $onlineNodes = ServerFisik::where('status', 'Online')->count();
        $offlineNodes = max(0, $totalNodes - $onlineNodes);

        $totalVms = VirtualMachine::where('tipe', 'VM')->count();
        $activeVms = VirtualMachine::where('tipe', 'VM')->where('status', 'Running')->count();
        $inactiveVms = max(0, $totalVms - $activeVms);

        $totalLxc = VirtualMachine::where('tipe', 'LXC')->count();
        $activeLxc = VirtualMachine::where('tipe', 'LXC')->where('status', 'Running')->count();
        $inactiveLxc = max(0, $totalLxc - $activeLxc);

        $totalUnits = $totalVms + $totalLxc;
        $activeUnits = $activeVms + $activeLxc;
        $inactiveUnits = max(0, $totalUnits - $activeUnits);

        $ramTotal = (int) ServerFisik::sum('kapasitas_ram');
        $ramUsed = (int) VirtualMachine::sum('allocated_ram_gb');
        $ramPct = $ramTotal > 0 ? round(($ramUsed / $ramTotal) * 100) : 0;

        $diskTotal = (int) ServerFisik::sum('storage_fisik');
        $diskUsed = (int) VirtualMachine::sum('allocated_disk_gb');
        $diskPct = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100) : 0;

        $nodes = ServerFisik::withCount('virtualMachines')
            ->withSum('virtualMachines as allocated_ram', 'allocated_ram_gb')
            ->withSum('virtualMachines as allocated_cpu', 'allocated_cpu')
            ->orderBy('nama_server', 'asc')
            ->get();

        $nodesList = [];
        $heavyNodes = [];
        foreach ($nodes as $node) {
            $nRamUsed = (int) ($node->allocated_ram ?? 0);
            $nRamCap = (int) $node->kapasitas_ram;
            $nPct = $nRamCap > 0 ? round(($nRamUsed / $nRamCap) * 100) : 0;
            $nodeItem = [
                'id' => $node->id,
                'nama_server' => $node->nama_server,
                'alamat_ip' => $node->alamat_ip ?? '-',
                'status' => $node->status,
                'kapasitas_ram' => $nRamCap,
                'ram_used' => $nRamUsed,
                'ram_pct' => $nPct,
                'kapasitas_cpu' => $node->kapasitas_cpu,
                'vm_count' => $node->virtual_machines_count ?? 0,
            ];
            $nodesList[] = $nodeItem;
            if ($nPct >= 75) {
                $heavyNodes[] = $nodeItem;
            }
        }

        $stats = [
            'totalNodes' => $totalNodes,
            'onlineNodes' => $onlineNodes,
            'offlineNodes' => $offlineNodes,
            'totalVms' => $totalVms,
            'activeVms' => $activeVms,
            'inactiveVms' => $inactiveVms,
            'totalLxc' => $totalLxc,
            'activeLxc' => $activeLxc,
            'inactiveLxc' => $inactiveLxc,
            'totalUnits' => $totalUnits,
            'activeUnits' => $activeUnits,
            'inactiveUnits' => $inactiveUnits,
            'ramTotal' => $ramTotal,
            'ramUsed' => $ramUsed,
            'ramPct' => $ramPct,
            'diskTotal' => $diskTotal,
            'diskUsed' => $diskUsed,
            'diskPct' => $diskPct,
            'nodes' => $nodesList,
            'heavyNodes' => $heavyNodes,
        ];

        $isPdfRequested = (bool) preg_match('/\bpdf\b/i', $message);

        $aiAnalysisText = null;
        $pdfBinary = null;
        $pdfFilename = null;

        if ($isPdfRequested) {
            // Minta OpenClaw buat PDF dari data database di workspace
            $openClawPdfPrompt = "Tolong buatkan dokumen resmi laporan inventaris database aplikasi SIMOX ke dalam file PDF di workspace (/root/.openclaw/workspace/).\n"
                . "Data telemetri database aplikasi saat ini:\n"
                . "- Server Nodes: {$onlineNodes}/{$totalNodes} Online\n"
                . "- Total VM & LXC: {$activeUnits} Running, {$inactiveUnits} Stopped (Total {$totalUnits})\n"
                . "- Alokasi RAM: {$ramUsed} GB dari {$ramTotal} GB ({$ramPct}%)\n"
                . "- Alokasi Storage: " . round($diskUsed / 1000, 2) . " TB dari " . round($diskTotal / 1000, 2) . " TB ({$diskPct}%)\n"
                . (!empty($heavyNodes) ? "- Node beban berat: " . implode(', ', array_map(fn($n) => "{$n['nama_server']} ({$n['ram_pct']}%)", $heavyNodes)) . "\n" : "- Semua node dalam kondisi aman.\n")
                . "Buat dokumen dengan layout rapi, profesional, dan simpan file PDF di workspace.";

            try {
                $aiData = $this->executeOpenClaw('simox-report-gen', $openClawPdfPrompt);
                if ($aiData) {
                    $rawAiText = $aiData['choices'][0]['message']['content'] 
                        ?? $aiData['result']['payloads'][0]['text'] 
                        ?? $aiData['result']['finalAssistantVisibleText'] 
                        ?? '';
                    
                    $cachedPdf = $this->extractAndCacheOpenClawPdf($rawAiText);
                    if ($cachedPdf) {
                        $pdfBinary = $cachedPdf['body'];
                        $pdfFilename = $cachedPdf['filename'];
                    }
                    $aiAnalysisText = $rawAiText;
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal meminta OpenClaw generate PDF database: ' . $e->getMessage());
            }
        } else {
            // Permintaan email biasa (tanpa PDF)
            $aiPrompt = "Buatkan analisis singkat & profesional (3 paragraf padat) mengenai potret kesehatan dan kapasitas resource database aplikasi SIMOX saat ini untuk dikirim ke email administrator:\n"
                . "- Server Node: {$onlineNodes}/{$totalNodes} Online\n"
                . "- Total VM & LXC: {$activeUnits} Aktif dari {$totalUnits} Unit ({$inactiveUnits} Stopped)\n"
                . "- Alokasi RAM: {$ramUsed} GB dari {$ramTotal} GB ({$ramPct}%)\n"
                . "- Alokasi Storage: " . round($diskUsed / 1000, 2) . " TB dari " . round($diskTotal / 1000, 2) . " TB ({$diskPct}%)\n"
                . (!empty($heavyNodes) ? "- Node beban berat: " . implode(', ', array_map(fn($n) => "{$n['nama_server']} ({$n['ram_pct']}%)", $heavyNodes)) . "\n" : "- Semua node dalam rentang beban aman.\n")
                . "Berikan ringkasan kondisi operasional, evaluasi beban, dan 2 rekomendasi langkah efisiensi/tindak lanjut.";

            try {
                $aiData = $this->executeOpenClaw('simox-report-gen', $aiPrompt);
                if ($aiData) {
                    $aiAnalysisText = $aiData['choices'][0]['message']['content'] 
                        ?? $aiData['result']['payloads'][0]['text'] 
                        ?? $aiData['result']['finalAssistantVisibleText'] 
                        ?? null;
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal mendapatkan ringkasan AI database untuk email: ' . $e->getMessage());
            }
        }

        if (empty($aiAnalysisText)) {
            $aiAnalysisText = "Berdasarkan potret database internal aplikasi SIMOX terkini, terdata {$totalNodes} node server fisik dengan alokasi RAM sebesar {$ramPct}% ({$ramUsed}/{$ramTotal} GB) dan alokasi storage sebesar {$diskPct}%. Dari total {$totalUnits} unit VM dan LXC di database, sebanyak {$activeUnits} unit tercatat berstatus aktif running.\n\n"
                . (!empty($heavyNodes) 
                    ? "Perhatian Khusus: Terdeteksi node server dengan alokasi memori tinggi (" . implode(', ', array_map(fn($n) => "{$n['nama_server']}: {$n['ram_pct']}%", $heavyNodes)) . ")."
                    : "Seluruh node server terdata stabil dengan alokasi beban yang merata.")
                . "\n\nRekomendasi:\n1. Pastikan sinkronisasi data database dengan kluster Proxmox VE aktual tetap terjaga.\n2. Lakukan audit rutin pada data VM/LXC yang berstatus Stopped.";
        }

        // Kirim Email via SMTP
        $emailSent = false;
        try {
            Mail::to($targetEmail)->send(new ResourceReportMail(
                $targetName,
                $stats,
                $aiAnalysisText,
                $pdfBinary,
                $pdfFilename ?: 'laporan-database-simox.pdf'
            ));
            $emailSent = true;
        } catch (\Throwable $e) {
            Log::error("Gagal mengirim email laporan database ke {$targetEmail}: " . $e->getMessage());
        }

        // Susun respons chat
        $timeStr = now()->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') . ' WIB';
        $diskUsedTb = round($stats['diskUsed'] / 1000, 2);
        $diskTotalTb = round($stats['diskTotal'] / 1000, 2);

        if ($isPdfRequested && $pdfBinary) {
            if ($emailSent) {
                $reply = "✅ **Laporan Database (PDF) Berhasil Dikirim ke Email!**\n\n"
                    . "Dokumen laporan resmi PDF dari data database SIMOX telah berhasil dikirimkan ke email akun Anda: **{$targetEmail}**\n\n"
                    . "📊 **Potret Telemetri Database Aplikasi ({$timeStr}):**\n"
                    . "- **Node Server**: {$onlineNodes}/{$totalNodes} Unit Online" . ($offlineNodes > 0 ? " ({$offlineNodes} Offline)" : "") . "\n"
                    . "- **Virtual Machines & LXC**: {$activeUnits} Aktif dari total {$totalUnits} Unit" . ($inactiveUnits > 0 ? " ({$inactiveUnits} Stopped)" : "") . "\n"
                    . "- **Alokasi RAM**: {$ramUsed} GB / {$ramTotal} GB (**{$ramPct}%**)\n"
                    . "- **Alokasi Storage**: {$diskUsedTb} TB / {$diskTotalTb} TB (**{$diskPct}%**)\n\n"
                    . "📄 **Lampiran Dokumen PDF:**\n"
                    . "Dokumen resmi asli (`{$pdfFilename}`) telah dilampirkan langsung pada email Anda.\n\n"
                    . "Anda juga dapat mengunduh berkasnya langsung di sini:\n"
                    . "Berkas: {$pdfFilename}";
            } else {
                $reply = "⚠️ **Laporan Database Berhasil Dibuat, Pengiriman Email Terkendala**\n\n"
                    . "Dokumen PDF (`{$pdfFilename}`) selesai digenerate, namun pengiriman via server SMTP terkendala.\n\n"
                    . "Berkas: {$pdfFilename}";
            }
        } else {
            if ($emailSent) {
                $reply = "✅ **Laporan Resource Database Berhasil Dikirim ke Email!**\n\n"
                    . "Ringkasan potret pemakaian resource dari database internal SIMOX telah berhasil dikirimkan ke alamat email: **{$targetEmail}**\n\n"
                    . "📊 **Potret Telemetri Database Aplikasi ({$timeStr}):**\n"
                    . "- **Node Server**: {$onlineNodes}/{$totalNodes} Unit Online" . ($offlineNodes > 0 ? " ({$offlineNodes} Offline)" : "") . "\n"
                    . "- **Virtual Machines & LXC**: {$activeUnits} Aktif dari total {$totalUnits} Unit" . ($inactiveUnits > 0 ? " ({$inactiveUnits} Stopped)" : "") . "\n"
                    . "- **Alokasi RAM**: {$ramUsed} GB / {$ramTotal} GB (**{$ramPct}%**)\n"
                    . "- **Alokasi Storage**: {$diskUsedTb} TB / {$diskTotalTb} TB (**{$diskPct}%**)\n\n"
                    . "💡 *Data bersumber dari database internal aplikasi SIMOX.*";
            } else {
                $reply = "⚠️ **Laporan Database Berhasil Disusun, Pengiriman Email Terkendala**\n\n"
                    . "Potret pemakaian resource database untuk **{$targetEmail}** telah berhasil dikompilasi, namun pengiriman email terkendala SMTP.";
            }
        }

        return response()->json([
            'reply' => $reply,
        ]);
    }
}
