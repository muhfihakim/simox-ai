<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAgentController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $message = $request->input('message');

        try {
            // Gunakan 1 Global Session agar tidak menumpuk di OpenClaw
            $sessionId = 'simox-web-widget-global';

            // Baca token secara dinamis dari konfigurasi OpenClaw (agar tidak pernah mismatch)
            $openclawConfigPath = env('USERPROFILE', 'C:\\Users\\mlhak') . '\\.openclaw\\openclaw.json';
            $token = env('OPENCLAW_API_TOKEN', '2a6965e7fec5868ee5dacd4baf5c8269be57eb57f76c1d912020a94dcd3c0df6');
            if (file_exists($openclawConfigPath)) {
                $config = json_decode(file_get_contents($openclawConfigPath), true);
                if (isset($config['gateway']['auth']['token'])) {
                    $token = $config['gateway']['auth']['token'];
                }
            }

            // Send the request to OpenClaw agent
            $response = Http::withToken($token)
                ->withHeaders([
                    'x-openclaw-session-key' => $sessionId
                ])
                ->timeout(300)
                ->post('http://127.0.0.1:18789/v1/chat/completions', [
                    'model' => 'openclaw',
                    'messages' => [
                        ['role' => 'user', 'content' => $message]
                    ],
                    'stream' => false
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['choices'][0]['message']['content'] ?? 'Maaf, saya tidak bisa memproses balasan dari agen.';
                return response()->json(['reply' => $reply]);
            }

            Log::error('OpenClaw API Error: ' . $response->body());
            return response()->json(['reply' => 'Maaf, terjadi kesalahan saat menghubungi agen AI. Pastikan API Chat Completions OpenClaw sudah diaktifkan.'], 500);
        } catch (\Exception $e) {
            Log::error('OpenClaw Exception: ' . $e->getMessage());
            return response()->json(['reply' => 'Maaf, agen AI sedang tidak aktif atau tidak dapat dihubungi di http://127.0.0.1:18789.'], 500);
        }
    }
}
