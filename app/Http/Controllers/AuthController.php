<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

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

        $turnstileError = $this->verifyTurnstile($request);
        if ($turnstileError) {
            return back()->withErrors(['turnstile' => $turnstileError])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Kredensial yang dimasukkan tidak cocok dengan data kami.',
        ])->onlyInput('email');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $turnstileError = $this->verifyTurnstile($request);
        if ($turnstileError) {
            return back()->withErrors(['turnstile' => $turnstileError])->onlyInput('email');
        }

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Tautan pemulihan akses telah dikirimkan ke email Anda. Silakan periksa kotak masuk atau folder spam.');
        }

        return back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $turnstileError = $this->verifyTurnstile($request);
        if ($turnstileError) {
            return back()->withErrors(['turnstile' => $turnstileError])->onlyInput('email');
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Password berhasil direset! Silakan masuk dengan password baru.');
        }

        return back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    private function verifyTurnstile(Request $request): ?string
    {
        if (!config('services.turnstile.enabled', env('TURNSTILE_ENABLED', true))) {
            return null;
        }

        $turnstileResponse = $request->input('cf-turnstile-response');

        if (empty($turnstileResponse)) {
            return 'Harap selesaikan verifikasi Cloudflare Turnstile.';
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
                return 'Verifikasi keamanan Turnstile gagal. Silakan coba lagi.';
            }
        } catch (\Exception $e) {
            Log::error('Cloudflare Turnstile Exception: ' . $e->getMessage());
            return 'Gagal menghubungi server verifikasi Turnstile.';
        }

        return null;
    }
}
