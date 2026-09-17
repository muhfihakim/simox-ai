<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMOX AI</title>
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
        }

        /* Complex Animated Background */
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
            animation-delay: 0s;
        }

        .orb-2 {
            width: 35vw;
            height: 35vw;
            background: rgba(14, 165, 233, 0.15);
            bottom: -5vw;
            right: -5vw;
            animation-delay: -3s;
        }

        .orb-3 {
            width: 30vw;
            height: 30vw;
            background: rgba(139, 92, 246, 0.2);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -6s;
        }

        @keyframes pulseOrb {
            0% {
                transform: scale(1) translateY(0);
                opacity: 0.6;
            }

            100% {
                transform: scale(1.2) translateY(20px);
                opacity: 1;
            }
        }

        /* Main Container */
        .login-wrapper {
            position: relative;
            z-index: 10;
            display: flex;
            width: 1000px;
            max-width: 95vw;
            min-height: 620px;
            height: auto;
            max-height: 94vh;
            border-radius: 24px;
            background: var(--glass-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), inset 0 0 0 1px var(--glass-highlight);
            overflow: hidden;
            animation: containerEnter 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        @keyframes containerEnter {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(30px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Left Side: Form */
        .login-form-side {
            flex: 1;
            padding: 2.5rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, transparent 100%);
            border-right: 1px solid var(--glass-border);
            overflow-y: auto;
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(to right, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .brand-logo i {
            color: #60a5fa;
            -webkit-text-fill-color: initial;
        }

        .header-text h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }

        .header-text p {
            color: #94a3b8;
            font-size: 0.9rem;
            margin-bottom: 1.75rem;
        }

        .turnstile-wrapper {
            margin: 1rem 0 1.25rem 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 65px;
            width: 100%;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 1.2rem;
            transition: 0.3s;
        }

        .form-control {
            width: 100%;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0.9rem 1.2rem 0.9rem 3rem;
            border-radius: 12px;
            color: white;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(15, 23, 42, 0.8);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
        }

        .form-control:focus+i {
            color: var(--primary);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            margin-bottom: 2rem;
            font-size: 0.85rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            color: #cbd5e1;
        }

        .checkbox-container input {
            appearance: none;
            width: 18px;
            height: 18px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            background: rgba(15, 23, 42, 0.6);
            cursor: pointer;
            position: relative;
            transition: 0.2s;
        }

        .checkbox-container input:checked {
            background: var(--primary);
            border-color: var(--primary);
        }

        .checkbox-container input:checked::after {
            content: "✓";
            position: absolute;
            color: white;
            font-size: 12px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .forgot-pass {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }

        .forgot-pass:hover {
            text-decoration: underline;
            color: #38bdf8;
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
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

        /* Right Side: Visual Showcase */
        .login-visual-side {
            flex: 1.2;
            padding: 2.5rem;
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: radial-gradient(circle at top right, rgba(79, 70, 229, 0.1), transparent 70%);
        }

        .visual-header {
            margin-bottom: auto;
            z-index: 2;
        }

        .visual-header h2 {
            font-size: 1.25rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .badge-live {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            border: 1px solid rgba(16, 185, 129, 0.3);
            display: flex;
            align-items: center;
            gap: 4px;
            letter-spacing: 0.5px;
        }

        .badge-live i {
            font-size: 8px;
            animation: blink 1.5s infinite;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0;
            }
        }

        .widgets-grid {
            position: relative;
            height: 380px;
            margin-top: 2rem;
            z-index: 2;
            perspective: 1000px;
            transform-style: preserve-3d;
        }

        .glass-widget {
            position: absolute;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: floatWidget 6s ease-in-out infinite alternate;
        }

        @keyframes floatWidget {
            from {
                transform: translateY(0) translateZ(0);
            }

            to {
                transform: translateY(-10px) translateZ(20px);
            }
        }

        /* Widget 1: Server Load */
        .widget-1 {
            top: 0px;
            right: 10px;
            width: 195px;
            animation-delay: 0s;
            z-index: 2;
        }

        .widget-title {
            color: #94a3b8;
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: space-between;
        }

        .w-stat {
            font-size: 1.8rem;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: baseline;
            gap: 4px;
        }

        .w-stat span {
            font-size: 0.9rem;
            color: #34d399;
        }

        .w-graph {
            height: 40px;
            margin-top: 10px;
            display: flex;
            align-items: flex-end;
            gap: 4px;
        }

        .w-bar {
            width: 10px;
            background: rgba(79, 70, 229, 0.5);
            border-radius: 2px 2px 0 0;
            animation: barAnimate 2s infinite alternate;
        }

        .w-bar:nth-child(1) {
            height: 30%;
            animation-delay: 0.1s;
        }

        .w-bar:nth-child(2) {
            height: 50%;
            animation-delay: 0.3s;
        }

        .w-bar:nth-child(3) {
            height: 80%;
            background: var(--primary);
            animation-delay: 0.5s;
        }

        .w-bar:nth-child(4) {
            height: 40%;
            animation-delay: 0.7s;
        }

        .w-bar:nth-child(5) {
            height: 60%;
            animation-delay: 0.9s;
        }

        .w-bar:nth-child(6) {
            height: 90%;
            background: var(--secondary);
            animation-delay: 1.1s;
        }

        @keyframes barAnimate {
            0% {
                opacity: 0.6;
            }

            100% {
                opacity: 1;
                transform: scaleY(1.1);
                transform-origin: bottom;
            }
        }

        /* Widget 3: AI Insights (Moved to Top Left) */
        .widget-3 {
            top: 30px;
            left: -10px;
            width: 230px;
            animation-delay: -1.5s;
            z-index: 1;
        }

        .ai-text {
            font-size: 0.8rem;
            color: #cbd5e1;
            line-height: 1.5;
            border-left: 2px solid var(--accent);
            padding-left: 0.5rem;
            margin-top: 0.5rem;
        }

        .code-lines {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-top: 10px;
            opacity: 0.4;
        }

        .cl {
            height: 4px;
            border-radius: 2px;
            background: white;
        }

        .cl:nth-child(1) {
            width: 80%;
            background: #34d399;
        }

        .cl:nth-child(2) {
            width: 60%;
        }

        .cl:nth-child(3) {
            width: 90%;
            background: #60a5fa;
        }

        /* Widget 2: Node Status (Moved to Bottom) */
        .widget-2 {
            bottom: 0px;
            left: 30px;
            width: 280px;
            animation-delay: -3s;
            z-index: 3;
        }

        .node-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .node-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.85rem;
        }

        .n-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #e2e8f0;
            font-family: 'JetBrains Mono', monospace;
        }

        .n-info i {
            color: #8b5cf6;
        }

        .n-status {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 10px #10b981;
        }

        .n-status.warn {
            background: #f59e0b;
            box-shadow: 0 0 10px #f59e0b;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .login-visual-side {
                display: none;
            }

            .login-wrapper {
                width: 100%;
                max-width: 450px;
                margin: 0 1rem;
                border-right: none;
            }

            .login-form-side {
                padding: 2rem;
                border-right: none;
            }

            /* Fix Background for Mobile */
            .bg-grid {
                transform: rotateX(60deg) translateY(-20px) scale(2.5);
                background-size: 30px 30px;
            }

            .orb-1 {
                width: 300px;
                height: 300px;
                top: -50px;
                left: -50px;
            }

            .orb-2 {
                width: 250px;
                height: 250px;
                bottom: -50px;
                right: -50px;
            }

            .orb-3 {
                width: 200px;
                height: 200px;
            }
        }

        @media (max-width: 480px) {
            .login-form-side {
                padding: 1.5rem;
            }

            .header-text h1 {
                font-size: 1.7rem;
            }

            .login-wrapper {
                max-height: 95vh;
                height: auto;
                overflow-y: auto;
                overflow-x: hidden;
            }
        }
    </style>
</head>

<body>

    <div class="bg-grid"></div>
    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>
    <div class="glow-orb orb-3"></div>

    <div class="login-wrapper">
        <!-- Form Side -->
        <div class="login-form-side">
            <div class="brand-logo">
                <i class="ph-fill ph-cpu"></i> SIMOX AI
            </div>

            <div class="header-text">
                <h1>Welcome Back</h1>
                <p>Securely access the Proxmox Infrastructure Dashboard</p>
            </div>

            @if ($errors->any())
                <div class="alert-box">
                    <i class="ph-fill ph-warning-circle"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="email">Admin Email</label>
                    <div class="input-wrapper">
                        <i class="ph ph-user"></i>
                        <input type="email" id="email" name="email" class="form-control"
                            placeholder="sysadmin@diskominfo.go.id" value="{{ old('email') }}" required autofocus
                            autocomplete="email">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Access Token / Password</label>
                    <div class="input-wrapper">
                        <i class="ph ph-lock-key"></i>
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="••••••••••••" required autocomplete="current-password">
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember" id="remember">
                        Keep session active
                    </label>
                    <a href="#" class="forgot-pass">Recover access</a>
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
                    Authenticate Session <i class="ph-bold ph-arrow-right"></i>
                </button>
            </form>
        </div>

        <!-- Visual Side -->
        <div class="login-visual-side">
            <div class="widgets-grid">
                <!-- Widget 1: Usage -->
                <div class="glass-widget widget-1">
                    <div class="widget-title">
                        <span>Cluster CPU Load</span>
                        <i class="ph ph-chart-line-up"></i>
                    </div>
                    <div class="w-stat">68.4<span>%</span></div>
                    <div class="w-graph">
                        <div class="w-bar"></div>
                        <div class="w-bar"></div>
                        <div class="w-bar"></div>
                        <div class="w-bar"></div>
                        <div class="w-bar"></div>
                        <div class="w-bar"></div>
                    </div>
                </div>

                <!-- Widget 2: AI Agent Insight -->
                <div class="glass-widget widget-3">
                    <div class="widget-title">
                        <span>AI Agent Insight</span>
                        <i class="ph-fill ph-sparkle text-accent"></i>
                    </div>
                    <div class="ai-text">
                        "Node pve-03 exhibits high IO wait. Recommend rebalancing LXC containers."
                    </div>
                    <div class="code-lines">
                        <div class="cl"></div>
                        <div class="cl"></div>
                        <div class="cl"></div>
                    </div>
                </div>

                <!-- Widget 3: Nodes -->
                <div class="glass-widget widget-2">
                    <div class="widget-title">
                        <span>Active Nodes Status</span>
                        <i class="ph ph-server"></i>
                    </div>
                    <div class="node-list">
                        <div class="node-item">
                            <div class="n-info"><i class="ph-fill ph-hard-drive"></i> pve-01.local</div>
                            <div class="n-status"></div>
                        </div>
                        <div class="node-item">
                            <div class="n-info"><i class="ph-fill ph-hard-drive"></i> pve-02.local</div>
                            <div class="n-status"></div>
                        </div>
                        <div class="node-item">
                            <div class="n-info"><i class="ph-fill ph-hard-drive"></i> pve-03.backup</div>
                            <div class="n-status warn"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
