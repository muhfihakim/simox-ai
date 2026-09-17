<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Verifikasi Cloudflare Turnstile jika aktif
        if (config('services.turnstile.enabled', env('TURNSTILE_ENABLED', true))) {
            $turnstileResponse = $request->input('cf-turnstile-response');

            if (empty($turnstileResponse)) {
                return back()->withErrors([
                    'turnstile' => 'Harap selesaikan verifikasi Cloudflare Turnstile.',
                ])->onlyInput('email');
            }

            $secretKey = config('services.turnstile.secret_key', env('TURNSTILE_SECRET_KEY', '1x0000000000000000000000000000000AA'));

            try {
                $verify = Http::asForm()->timeout(10)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secretKey,
                    'response' => $turnstileResponse,
                    'remoteip' => $request->ip(),
                ]);

                if (!$verify->successful() || !$verify->json('success')) {
                    Log::warning('Cloudflare Turnstile verification failed: ' . $verify->body());
                    return back()->withErrors([
                        'turnstile' => 'Verifikasi keamanan Turnstile gagal. Silakan coba lagi.',
                    ])->onlyInput('email');
                }
            } catch (\Exception $e) {
                Log::error('Cloudflare Turnstile Exception: ' . $e->getMessage());
                return back()->withErrors([
                    'turnstile' => 'Gagal menghubungi server verifikasi Turnstile.',
                ])->onlyInput('email');
            }
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
