<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setel Ulang Password - SIMOX AI</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;700&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    @if (config('services.turnstile.enabled', env('TURNSTILE_ENABLED', true)))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --secondary: #0ea5e9;
            --accent: #8b5cf6;
            --dark: #0f172a;
            --darker: #020617;
            --light: #f8fafc;
            --glass-bg: rgba(15, 23, 42, 0.4);
            --glass-border: rgba(255, 255, 255, 0.1);
            --glass-highlight: rgba(255, 255, 255, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--darker);
            color: var(--light);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            overflow-y: auto;
            position: relative;
            padding: 1.5rem;
        }

        .bg-grid {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-image:
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: 0;
            perspective: 1000px;
            transform: rotateX(60deg) translateY(-100px) scale(2);
            transform-origin: top center;
            animation: gridMove 20s linear infinite;
            mask-image: linear-gradient(to bottom, transparent, black 40%, transparent);
            -webkit-mask-image: linear-gradient(to bottom, transparent, black 40%, transparent);
            pointer-events: none;
        }

        @keyframes gridMove {
            0% {
                background-position: 0 0;
            }

            100% {
                background-position: 0 40px;
            }
        }

        .glow-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            animation: pulseOrb 8s alternate infinite ease-in-out;
            pointer-events: none;
        }

        .orb-1 {
            width: 40vw;
            height: 40vw;
            background: rgba(79, 70, 229, 0.2);
            top: -10vw;
            left: -10vw;
        }

        .orb-2 {
            width: 35vw;
            height: 35vw;
            background: rgba(14, 165, 233, 0.15);
            bottom: -5vw;
            right: -5vw;
            animation-delay: -3s;
        }

        @keyframes pulseOrb {
            0% {
                transform: scale(1) translateY(0);
                opacity: 0.6;
            }

            100% {
                transform: scale(1.1) translateY(20px);
                opacity: 0.8;
            }
        }

        .card-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 460px;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            padding: 2.5rem;
            animation: cardFadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes cardFadeIn {
            0% {
                opacity: 0;
                transform: translateY(20px) scale(0.98);
            }

            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.15rem;
            font-weight: 800;
            color: white;
            letter-spacing: 0.5px;
            margin-bottom: 1.5rem;
        }

        .brand-logo i {
            color: var(--secondary);
            font-size: 1.4rem;
        }

        .header-text h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.4rem;
            letter-spacing: -0.5px;
        }

        .header-text p {
            color: #94a3b8;
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1.75rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 0.5rem;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 1rem;
            color: #64748b;
            font-size: 1.15rem;
            transition: color 0.3s;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.8rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: white;
            font-size: 0.9rem;
            font-family: inherit;
            outline: none;
            transition: all 0.3s;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.07);
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.25);
        }

        .form-control:focus+i,
        .input-wrapper:focus-within i {
            color: var(--secondary);
        }

        .turnstile-wrapper {
            margin-bottom: 1.25rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .btn-submit {
            width: 100%;
            padding: 0.85rem;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);
            position: relative;
            overflow: hidden;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: skewX(-25deg);
            transition: 0.5s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(79, 70, 229, 0.5);
        }

        .btn-submit:hover::before {
            left: 150%;
        }

        .btn-submit:active {
            transform: scale(0.97) translateY(0);
            box-shadow: 0 5px 15px -3px rgba(79, 70, 229, 0.4);
        }

        .btn-submit.loading {
            opacity: 0.9;
            pointer-events: none;
            background: linear-gradient(135deg, #4338ca, #7c3aed);
            box-shadow: 0 0 25px rgba(99, 102, 241, 0.65);
            animation: pulseSubmit 1.2s infinite alternate;
        }

        @keyframes pulseSubmit {
            0% {
                box-shadow: 0 0 10px rgba(99, 102, 241, 0.4);
            }

            100% {
                box-shadow: 0 0 25px rgba(99, 102, 241, 0.85);
            }
        }

        .ripple-effect {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.45);
            transform: scale(0);
            animation: rippleAnim 0.6s linear;
            pointer-events: none;
        }

        @keyframes rippleAnim {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        .alert-box {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 0.8rem;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: #94a3b8;
            font-size: 0.85rem;
            text-decoration: none;
            transition: color 0.2s;
            margin-top: 1.75rem;
        }

        .back-link:hover {
            color: var(--secondary);
        }
    </style>
</head>

<body>

    <div class="bg-grid"></div>
    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>

    <div class="card-wrapper">
        <div class="brand-logo">
            <i class="ph-fill ph-cpu"></i> SIMOX AI
        </div>

        <div class="header-text">
            <h1>Reset Password</h1>
            <p>Masukkan kata sandi baru untuk memulihkan akses ke SIMOX AI Dashboard.</p>
        </div>

        @if ($errors->any())
            <div class="alert-box">
                <i class="ph-fill ph-warning-circle" style="font-size: 1.2rem; flex-shrink: 0;"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="email">Admin Email</label>
                <div class="input-wrapper">
                    <i class="ph ph-envelope-simple"></i>
                    <input type="email" id="email" name="email" class="form-control"
                        value="{{ old('email', $email) }}" required autofocus autocomplete="email">
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password Baru</label>
                <div class="input-wrapper">
                    <i class="ph ph-lock-key"></i>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="Minimal 8 karakter" required autocomplete="new-password">
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <div class="input-wrapper">
                    <i class="ph ph-lock-key-open"></i>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="form-control" placeholder="Ketik ulang password baru" required
                        autocomplete="new-password">
                </div>
            </div>

            @if (config('services.turnstile.enabled', env('TURNSTILE_ENABLED', true)))
                <div class="turnstile-wrapper">
                    <div class="cf-turnstile"
                        data-sitekey="{{ config('services.turnstile.site_key', env('TURNSTILE_SITE_KEY', '1x00000000000000000000AA')) }}"
                        data-theme="dark"></div>
                    @error('turnstile')
                        <span style="color: #ef4444; font-size: 0.8rem; margin-top: 0.5rem; text-align: center;">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
            @endif

            <button type="submit" class="btn-submit">
                <span>Simpan Password Baru</span> <i class="ph-bold ph-check"></i>
            </button>

            <div style="text-align: center;">
                <a href="{{ route('login') }}" class="back-link">
                    <i class="ph ph-arrow-left"></i> Batal & Kembali ke Login
                </a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form');
            const submitBtn = document.querySelector('.btn-submit');

            if (submitBtn) {
                submitBtn.addEventListener('click', function(e) {
                    const rect = submitBtn.getBoundingClientRect();
                    const ripple = document.createElement('span');
                    ripple.className = 'ripple-effect';
                    const size = Math.max(rect.width, rect.height);
                    ripple.style.width = ripple.style.height = `${size}px`;
                    ripple.style.left = `${e.clientX - rect.left - size / 2}px`;
                    ripple.style.top = `${e.clientY - rect.top - size / 2}px`;
                    submitBtn.appendChild(ripple);
                    setTimeout(() => ripple.remove(), 600);
                });
            }

            if (form && submitBtn) {
                form.addEventListener('submit', function() {
                    submitBtn.classList.add('loading');
                    submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin" style="font-size: 1.15rem;"></i> <span>Menyimpan Password...</span>';
                });
            }
        });
    </script>
</body>

</html>
