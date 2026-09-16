<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

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

        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $message,
                ],
            ],
            'user' => $sessionId,
        ];

        try {
            $response = $client->post($url, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("OpenClaw HTTP Error [{$response->status()}]: " . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('OpenClaw HTTP Exception: ' . $e->getMessage());
            return null;
        }
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $message = $request->input('message');

        try {
            // Gunakan global session agar tidak menumpuk
            $sessionId = 'simox-web-widget-global';
            
            // Set timeout agar PHP tidak berhenti jika agen AI butuh waktu berpikir yang lama
            set_time_limit(300);

            // Eksekusi HTTP OpenClaw Gateway
            $data = $this->executeOpenClaw($sessionId, $message);

            // Deteksi jika terjadi Context Overflow
            if ($data && isset($data['error'])) {
                $errorMsg = $data['error']['message'] ?? '';
                if (stripos($errorMsg, 'Context overflow') !== false) {
                    $this->executeOpenClaw($sessionId, '/reset');
                    $data = $this->executeOpenClaw($sessionId, $message);
                }
            }

            if ($data) {
                // Format OpenAI standard
                if (isset($data['choices'][0]['message']['content'])) {
                    return response()->json(['reply' => $data['choices'][0]['message']['content']]);
                }
                // Format native payloads fallback
                if (isset($data['result']['payloads'][0]['text'])) {
                    return response()->json(['reply' => $data['result']['payloads'][0]['text']]);
                }
                if (isset($data['result']['finalAssistantVisibleText'])) {
                    return response()->json(['reply' => $data['result']['finalAssistantVisibleText']]);
                }
            }

            return response()->json([
                'reply' => 'Maaf, terjadi kesalahan saat menghubungi gateway AI di server. Pastikan HTTP endpoint OpenClaw aktif.'
            ], 500);

        } catch (\Exception $e) {
            Log::error('OpenClaw Controller Exception: ' . $e->getMessage());
            return response()->json(['reply' => 'Maaf, agen AI sedang tidak aktif atau tidak dapat dijalankan.'], 500);
        }
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

    public function reset()
    {
        $sessionId = 'simox-web-widget-global';
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
}
