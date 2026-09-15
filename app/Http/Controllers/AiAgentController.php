<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
}
