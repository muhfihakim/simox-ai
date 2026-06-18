<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAgentController extends Controller
{
    private function executeOpenClaw($sessionId, $message) {
        $escapedMessage = escapeshellarg($message);
        $escapedSession = escapeshellarg($sessionId);
        
        // Tanpa 2>&1 agar stderr (log) tidak bercampur dengan stdout (JSON)
        $command = "openclaw agent --agent main --session-id {$escapedSession} --message {$escapedMessage} --json";
        $output = shell_exec($command);
        
        if ($output) {
            $jsonStart = strpos($output, '{');
            if ($jsonStart !== false) {
                $jsonStr = substr($output, $jsonStart);
                return json_decode($jsonStr, true);
            }
        }
        return null;
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

            // Eksekusi CLI OpenClaw
            $data = $this->executeOpenClaw($sessionId, $message);

            // Deteksi jika terjadi Context Overflow
            if ($data && isset($data['result']['error'])) {
                $errorMsg = $data['result']['error']['message'] ?? '';
                if (strpos($errorMsg, 'Context overflow') !== false) {
                    // Reset session secara otomatis
                    $this->executeOpenClaw($sessionId, '/reset');
                    // Coba kirim ulang pesan user setelah reset
                    $data = $this->executeOpenClaw($sessionId, $message);
                }
            }

            if ($data) {
                // Untuk balasan sukses, OpenClaw mengembalikan di payloads[0]['text']
                if (isset($data['result']['payloads'][0]['text'])) {
                    $reply = $data['result']['payloads'][0]['text'];
                    return response()->json(['reply' => $reply]);
                } 
                // Untuk balasan error sistem (jika bukan overflow atau gagal recovery)
                elseif (isset($data['result']['finalAssistantVisibleText'])) {
                    $reply = $data['result']['finalAssistantVisibleText'];
                    return response()->json(['reply' => $reply]);
                }
            }

            Log::error('OpenClaw CLI Error - Tidak ada output JSON valid.');
            return response()->json(['reply' => 'Maaf, terjadi kesalahan saat mengeksekusi agen AI di server. (CLI Error)'], 500);

        } catch (\Exception $e) {
            Log::error('OpenClaw CLI Exception: ' . $e->getMessage());
            return response()->json(['reply' => 'Maaf, agen AI sedang tidak aktif atau tidak dapat dijalankan.'], 500);
        }
    }
}
