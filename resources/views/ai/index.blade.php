<x-layouts.app>
    <div class="dashboard ai-fullpage-container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ph-fill ph-robot" style="color: #a855f7;"></i> SIMOX AI Agent
                </h1>
                <p>Asisten cerdas berbasis OpenClaw untuk manajemen, audit, dan otomasi Proxmox VE.</p>
            </div>
            <div class="header-actions" style="display: flex; align-items: center; gap: 0.75rem;">
                <div class="api-status checking" id="fullpageAgentStatusBadge"
                    style="padding: 0.4rem 0.75rem; border-radius: 9999px; border: 1px solid var(--border); background: var(--surface);">
                    <span class="status-dot"></span>
                    <span id="fullpageAgentStatusText" class="text-xs font-semibold">Memeriksa Gateway...</span>
                </div>
                <button class="btn btn-outline" id="fullpageResetBtn" title="Reset riwayat percakapan sesi ini">
                    <i class="ph ph-arrow-counter-clockwise"></i> Reset Chat
                </button>
            </div>
        </div>

        <!-- Fullscreen Chat Card -->
        <div class="ai-fullpage-card">
            <!-- Chat Card Header -->
            <div class="ai-fullpage-card-header">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div class="ai-avatar-badge">
                        <i class="ph-fill ph-sparkle"></i>
                    </div>
                    <div>
                        <div
                            style="font-weight: 600; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;">
                            OpenClaw AI Assistant
                        </div>
                        <small class="text-muted" style="font-size: 0.75rem;" id="fullpageEndpointInfo">
                            Terhubung
                        </small>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span class="badge" id="fullpageLatencyText"
                        style="background: var(--background); border: 1px solid var(--border); color: var(--text-muted); font-size: 0.75rem;">
                        <i class="ph ph-timer"></i> -- ms
                    </span>
                </div>
            </div>

            <!-- Chat Messages Body -->
            <div class="ai-fullpage-body" id="fullpageChatBody">
                <!-- Bot Welcome Message -->
                <div class="chat-message bot">
                    <div class="chat-avatar bot-avatar">
                        <i class="ph-fill ph-robot"></i>
                    </div>
                    <div class="msg-content">
                        <p style="margin-bottom: 0.5rem;">Halo! Saya <strong>SIMOX AI Agent</strong> ditenagai oleh
                            <strong>OpenClaw</strong>. Saya siap membantu Anda dalam:
                        </p>
                        <ul style="margin-left: 1.25rem; margin-bottom: 0.75rem; font-size: 0.85rem; line-height: 1.5;">
                            <li>Analisis inventaris Virtual Machine & Kontainer LXC</li>
                            <li>Informasi beban CPU, RAM, & Storage server node</li>
                            <li>Rekomendasi optimasi dan troubleshooting infrastruktur Proxmox VE</li>
                        </ul>
                        <p style="margin-bottom: 0; font-size: 0.85rem;">Pilih pertanyaan cepat di bawah atau ketik
                            langsung pertanyaan Anda:</p>
                    </div>
                </div>

                <!-- Prompt Suggestion Chips -->
                <div class="prompt-chips-container" id="promptChipsContainer">
                    <span class="prompt-chip" data-prompt="Berapa total server node dan VPS yang terdata saat ini?">
                        <i class="ph ph-chart-pie-slice"></i> Ringkasan Node & VPS
                    </span>
                    <span class="prompt-chip"
                        data-prompt="Tampilkan daftar VM yang saat ini sedang nonaktif atau butuh perhatian.">
                        <i class="ph ph-warning-circle"></i> Cek VM Bermasalah
                    </span>
                    <span class="prompt-chip"
                        data-prompt="Bagaimana rekomendasi alokasi RAM dan CPU agar efisien di Proxmox?">
                        <i class="ph ph-lightbulb"></i> Tips Efisiensi Resource
                    </span>
                </div>
            </div>

            <!-- Chat Input Area -->
            <div class="ai-fullpage-input-area">
                <div class="ai-input-wrapper">
                    <textarea id="fullpageChatInput" rows="1" placeholder="Tanyakan sesuatu ke AI Agent..." autocomplete="off"></textarea>
                    <button class="btn btn-primary" id="fullpageSendBtn">
                        <span>Kirim</span>
                        <i class="ph-fill ph-paper-plane-right"></i>
                    </button>
                </div>
                <div class="ai-input-hint">
                    <span><i class="ph ph-keyboard"></i> Tekan <strong>Enter</strong> untuk mengirim, <strong>Shift +
                            Enter</strong> untuk baris baru.</span>
                    <span><i class="ph ph-shield-check"></i> Sesi terisolasi</span>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
