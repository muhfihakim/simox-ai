<!DOCTYPE html>
<html lang="id">

@include('components.layouts.head')

<body>
    <div class="app-container">

        @include('components.layouts.sidebar')

        <!-- Main Content -->
        <main class="main-content">

            @include('components.layouts.topbar')

            <!-- Dashboard Content -->
            {{ $slot }}
        </main>

        <!-- AI Assistant Chat Widget -->
        <div class="ai-chat-widget" id="aiChatWidget">
            <div class="ai-chat-header">
                <div>
                    <h4><i class="ph-fill ph-robot"></i> SIMOX AI Agent</h4>
                    <small>Asisten Inventaris Diskominfo</small>
                </div>
                <button class="icon-btn" id="closeAiChat"><i class="ph ph-x"></i></button>
            </div>
            <div class="ai-chat-body" id="aiChatBody">
                <div class="chat-message bot">
                    <div class="msg-content">Halo! Saya Agen OpenClaw yang siap membantu Anda dengan pengelolaan Proxmox VE. Apa yang bisa saya bantu hari ini?</div>
                </div>
            </div>
            <div class="ai-chat-input">
                <input type="text" id="aiChatInput" placeholder="Ketik pesan Anda di sini..." autocomplete="off">
                <button class="btn btn-primary btn-icon" id="aiChatSendBtn"><i
                        class="ph-fill ph-paper-plane-right"></i></button>
            </div>
        </div>
    </div>

    <!-- Toasts Container -->
    <div class="toast-container" id="toastContainer"></div>

    <script src="{{ asset('assets/js/script.js') }}"></script>
</body>

</html>
