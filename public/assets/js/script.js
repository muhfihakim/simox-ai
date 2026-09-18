document.addEventListener("DOMContentLoaded", () => {
    // --- Sidebar Toggle Logic ---
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggleSidebar"); // Topbar toggle
    const closeSidebarMobile = document.getElementById("closeSidebarMobile"); // Mobile close
    const sidebarOverlay = document.getElementById("sidebarOverlay");

    // Toggle logic for both Desktop and Mobile
    if (toggleBtn) {
        toggleBtn.addEventListener("click", () => {
            if (window.innerWidth <= 768) {
                sidebar.classList.add("mobile-open");
                sidebarOverlay.classList.add("active");
            } else {
                sidebar.classList.toggle("collapsed");
            }
        });
    }

    // Close on mobile
    const closeMobileSidebar = () => {
        sidebar.classList.remove("mobile-open");
        sidebarOverlay.classList.remove("active");
    };

    if (closeSidebarMobile)
        closeSidebarMobile.addEventListener("click", closeMobileSidebar);
    if (sidebarOverlay)
        sidebarOverlay.addEventListener("click", closeMobileSidebar);

    // --- Escape HTML Helper ---
    function escapeHtml(str) {
        if (!str) return "";
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // --- Global PDF Download Function ---
    window.downloadAiReportPdf = async function (
        content,
        filename = "laporan-analisis.pdf",
        title = "",
        source = "",
    ) {
        try {
            showToast("Menyiapkan dokumen PDF...", "info");
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");
            const response = await fetch("/api/ai/export-pdf", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/pdf",
                },
                body: JSON.stringify({ content, filename, title, source }),
            });

            if (!response.ok) {
                throw new Error("Gagal generate PDF dari server.");
            }

            // Dapatkan nama file dari header jika ada
            let targetFilename = filename;
            const disposition = response.headers.get("Content-Disposition");
            if (disposition && disposition.indexOf("filename=") !== -1) {
                const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(
                    disposition,
                );
                if (matches != null && matches[1]) {
                    targetFilename = matches[1].replace(/['"]/g, "").trim();
                }
            }

            const blob = await response.blob();
            const downloadUrl = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = downloadUrl;
            a.download = targetFilename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(downloadUrl);
            a.remove();
            showToast("Dokumen PDF berhasil diunduh!", "success");
        } catch (e) {
            console.error("PDF Download error:", e);
            showToast("Gagal mengunduh dokumen PDF.", "error");
        }
    };

    // --- Markdown / Formatter Helper ---
    function formatAiReply(text, duration = null) {
        if (!text) return "Tidak ada respons";

        let renderedHtml = "";
        if (
            typeof marked !== "undefined" &&
            typeof marked.parse === "function"
        ) {
            try {
                marked.setOptions({
                    gfm: true,
                    breaks: true,
                });
                const rawHtml = marked.parse(text);
                if (
                    typeof DOMPurify !== "undefined" &&
                    typeof DOMPurify.sanitize === "function"
                ) {
                    renderedHtml = DOMPurify.sanitize(rawHtml);
                } else {
                    renderedHtml = rawHtml;
                }
            } catch (e) {
                console.error("Markdown parse error:", e);
            }
        }

        if (!renderedHtml) {
            let div = document.createElement("div");
            div.textContent = text;
            let safeText = div.innerHTML;

            // Code blocks ```code```
            safeText = safeText.replace(
                /```([\s\S]*?)```/g,
                "<pre><code>$1</code></pre>",
            );
            // Inline code `code`
            safeText = safeText.replace(/`([^`]+)`/g, "<code>$1</code>");
            // Headings
            safeText = safeText.replace(/^### (.*$)/gm, "<h4>$1</h4>");
            safeText = safeText.replace(/^## (.*$)/gm, "<h3>$1</h3>");
            safeText = safeText.replace(/^# (.*$)/gm, "<h2>$1</h2>");
            // Bold **text**
            safeText = safeText.replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>");
            // Italic *text*
            safeText = safeText.replace(/\*([^\*]+)\*/g, "<em>$1</em>");
            // Bullet points * or -
            safeText = safeText.replace(/^[\*\-]\s+(.*)$/gm, "&bull; $1");
            // Line breaks
            safeText = safeText.replace(/\n/g, "<br>");
            renderedHtml = safeText;
        }

        // 1. Detect assistant-media URL or OpenClaw workspace path
        const urlMatch =
            text.match(
                /https?:\/\/[^\s\)\'\"\]]+__openclaw__\/assistant-media\?[^\s\)\'\"\]]+/i,
            ) || text.match(/\/__openclaw__\/assistant-media\?[^\s\)\'\"\]]+/i);
        let sourcePath = "";
        if (urlMatch) {
            sourcePath = urlMatch[0];
        } else {
            const workspaceMatch =
                text.match(/(\/root\/\.openclaw\/workspace\/[^\s\)\'\"\]]+\.pdf)/i) ||
                text.match(/Lokasi:\s*([^\s\n\r]+\.pdf)/i);
            if (workspaceMatch) {
                sourcePath = workspaceMatch[1];
            }
        }

        // 2. Detect filename
        const pdfMatch =
            text.match(
                /(?:Nama\s*file|File|Berkas|Dokumen|Lokasi|Output)[\s\S]{0,60}?([a-zA-Z0-9_\-\.]+\.pdf)/i,
            ) || text.match(/([a-zA-Z0-9_\-\.]+\.pdf)/i);
        let filename = pdfMatch
            ? pdfMatch[1]
            : sourcePath
            ? sourcePath.split("/").pop()
            : "laporan-analisis.pdf";

        if (filename.includes("?") || filename.includes("=")) {
            filename = "laporan-analisis.pdf";
        }

        // Rewrite any raw OpenClaw assistant-media URLs in HTML to SIMOX proxy
        renderedHtml = renderedHtml.replace(
            /(?:https?:\/\/[^\s"'<>]*)?\/__openclaw__\/assistant-media\?source=([^"'<>&]+)(?:[^"'<>]*)/gi,
            (match, p1) =>
                `/api/ai/download-file?source=${encodeURIComponent(
                    decodeURIComponent(p1),
                )}`,
        );

        let cardHtml = "";
        if (pdfMatch || sourcePath) {
            cardHtml = `
                <div class="ai-pdf-download-card">
                    <div class="pdf-card-icon"><i class="ph-fill ph-file-pdf"></i></div>
                    <div class="pdf-card-details">
                        <div class="pdf-card-name">${escapeHtml(filename)}</div>
                        <div class="pdf-card-desc">${
                            sourcePath
                                ? "Dokumen Resmi OpenClaw • Siap Diunduh"
                                : "Dokumen Analisis Proxmox VE • Siap Diunduh"
                        }</div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm btn-download-pdf-card" data-filename="${escapeHtml(
                        filename,
                    )}" data-source="${escapeHtml(sourcePath)}">
                        <i class="ph-bold ph-download-simple"></i> Unduh PDF
                    </button>
                </div>
            `;
        }

        // Add action bar (duration badge + copy)
        let actionsHtml = `
            <div class="msg-actions-bar">
                ${
                    duration
                        ? `<span class="msg-duration-badge" title="Waktu proses respons"><i class="ph-bold ph-timer"></i> ${escapeHtml(
                              duration,
                          )}s</span>`
                        : ""
                }
                <button type="button" class="btn-msg-action btn-action-copy" title="Salin Jawaban">
                    <i class="ph ph-copy"></i> Salin
                </button>
            </div>
        `;

        return renderedHtml + cardHtml + actionsHtml;
    }

    // --- OpenClaw Agent Status Checking ---
    const sidebarAgentStatus = document.getElementById("sidebarAgentStatus");
    const sidebarAgentText = document.getElementById("sidebarAgentText");
    const fullpageAgentStatusBadge = document.getElementById(
        "fullpageAgentStatusBadge",
    );
    const fullpageAgentStatusText = document.getElementById(
        "fullpageAgentStatusText",
    );
    const fullpageLatencyText = document.getElementById("fullpageLatencyText");

    async function checkAgentStatus() {
        try {
            const res = await fetch("/api/agent/status");
            const data = await res.json();

            if (res.ok && data.online) {
                if (sidebarAgentStatus) {
                    sidebarAgentStatus.className = "api-status connected";
                }
                if (sidebarAgentText) {
                    sidebarAgentText.textContent = "OpenClaw: Aktif";
                }
                if (fullpageAgentStatusBadge) {
                    fullpageAgentStatusBadge.className = "api-status connected";
                }
                if (fullpageAgentStatusText) {
                    fullpageAgentStatusText.textContent = "OpenClaw Terhubung";
                }
                if (fullpageLatencyText) {
                    fullpageLatencyText.innerHTML = `<i class="ph ph-timer"></i> ${data.latency_ms} ms`;
                }
            } else {
                setDisconnected();
            }
        } catch (e) {
            setDisconnected();
        }
    }

    function setDisconnected() {
        if (sidebarAgentStatus) {
            sidebarAgentStatus.className = "api-status disconnected";
        }
        if (sidebarAgentText) {
            sidebarAgentText.textContent = "OpenClaw: Terputus";
        }
        if (fullpageAgentStatusBadge) {
            fullpageAgentStatusBadge.className = "api-status disconnected";
        }
        if (fullpageAgentStatusText) {
            fullpageAgentStatusText.textContent = "OpenClaw Terputus";
        }
        if (fullpageLatencyText) {
            fullpageLatencyText.innerHTML = `<i class="ph ph-timer"></i> Offline`;
        }
    }

    if (sidebarAgentStatus || fullpageAgentStatusBadge) {
        checkAgentStatus();
        setInterval(checkAgentStatus, 30000);
    }

    // --- AI Thinking State & Navigation Lock ---
    window.isAiThinking = false;
    function setAiThinking(thinking) {
        window.isAiThinking = !!thinking;
        if (window.isAiThinking) {
            document.body.classList.add("ai-processing");
        } else {
            document.body.classList.remove("ai-processing");
        }
    }

    // Prevent closing tab, reloading, or leaving page while AI is thinking
    window.addEventListener("beforeunload", (e) => {
        if (window.isAiThinking) {
            e.preventDefault();
            e.returnValue = "AI sedang berpikir & memproses data. Apakah Anda yakin ingin keluar?";
            return e.returnValue;
        }
    });

    // Intercept in-app navigation (links, menu items, close/reset buttons) while AI is thinking
    document.addEventListener(
        "click",
        (e) => {
            if (!window.isAiThinking) return;

            // 1. Check close / reset buttons
            const closeOrReset = e.target.closest(
                "#closeAiChat, #fullpageCloseBtn, #fullpageResetBtn",
            );
            if (closeOrReset) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                if (typeof showToast === "function") {
                    showToast(
                        "AI sedang berpikir & memproses data. Harap tunggu hingga proses selesai.",
                        "warning",
                    );
                }
                return false;
            }

            // 2. Check navigation links (sidebar, topbar, cards, etc.)
            const link = e.target.closest("a[href]");
            if (link) {
                if (
                    link.classList.contains("btn-download-pdf-card") ||
                    link.classList.contains("btn-action-pdf")
                ) {
                    return;
                }

                const href = link.getAttribute("href");
                if (
                    href &&
                    !href.startsWith("#") &&
                    !href.startsWith("javascript:")
                ) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    if (typeof showToast === "function") {
                        showToast(
                            "AI sedang berpikir & memproses data. Harap tunggu hingga selesai sebelum berpindah menu/halaman.",
                            "warning",
                        );
                    }
                    return false;
                }
            }
        },
        true, // capture phase to intercept before native handlers
    );

    // Intercept form submissions while AI is thinking
    document.addEventListener(
        "submit",
        (e) => {
            if (window.isAiThinking) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                if (typeof showToast === "function") {
                    showToast(
                        "AI sedang berpikir & memproses data. Harap tunggu hingga selesai.",
                        "warning",
                    );
                }
                return false;
            }
        },
        true,
    );

    // --- AI Chat History & Storage Management (Global Widget & Fullpage) ---
    const CHAT_STORAGE_KEY = "simox_ai_chat_history";
    const WIDGET_OPEN_KEY = "simox_ai_widget_open";

    function getStoredChatHistory() {
        try {
            const data = localStorage.getItem(CHAT_STORAGE_KEY);
            return data ? JSON.parse(data) : [];
        } catch (e) {
            return [];
        }
    }

    function saveChatMessage(sender, text, rawReply = null, duration = null) {
        try {
            const history = getStoredChatHistory();
            history.push({
                sender,
                text: text || "",
                rawReply: rawReply || text || "",
                duration: duration || null,
                time: Date.now(),
            });
            // Batasi riwayat maksimal 50 pesan terakhir agar penyimpanan efisien
            if (history.length > 50) {
                history.splice(0, history.length - 50);
            }
            localStorage.setItem(CHAT_STORAGE_KEY, JSON.stringify(history));
        } catch (e) {
            console.error("Gagal menyimpan riwayat chat:", e);
        }
    }

    function clearChatHistory() {
        try {
            localStorage.removeItem(CHAT_STORAGE_KEY);
        } catch (e) {}
    }

    function restoreWidgetMessages() {
        const body = document.getElementById("aiChatBody");
        if (!body) return;

        body.innerHTML = `
            <div class="chat-message bot">
                <div class="msg-content">Halo! Saya Agen OpenClaw yang siap membantu Anda dengan pengelolaan Proxmox VE. Apa yang bisa saya bantu hari ini?</div>
            </div>
        `;

        const history = getStoredChatHistory();
        if (!history || history.length === 0) return;

        history.forEach((msg) => {
            if (msg.sender === "user") {
                const userMsgDiv = document.createElement("div");
                userMsgDiv.className = "chat-message user";
                userMsgDiv.innerHTML = `<div class="msg-content"></div>`;
                userMsgDiv.querySelector(".msg-content").textContent = msg.text;
                body.appendChild(userMsgDiv);
            } else {
                const botMsgDiv = document.createElement("div");
                botMsgDiv.className = "chat-message bot";
                botMsgDiv.dataset.rawText = msg.rawReply;
                botMsgDiv.innerHTML = `<div class="msg-content">${formatAiReply(msg.rawReply, msg.duration)}</div>`;
                body.appendChild(botMsgDiv);
            }
        });

        body.scrollTop = body.scrollHeight;
    }

    // --- AI Chat Widget Toggle ---
    const aiChatWidget = document.getElementById("aiChatWidget");
    const openAiChat = document.getElementById("openAiChat");
    const closeAiChat = document.getElementById("closeAiChat");

    if (aiChatWidget) {
        // Cek jika widget sebelumnya dalam posisi terbuka saat berpindah halaman
        const isWidgetOpen = localStorage.getItem(WIDGET_OPEN_KEY) === "true";
        if (isWidgetOpen) {
            aiChatWidget.classList.add("active");
        }
        // Pulihkan riwayat percakapan widget global
        restoreWidgetMessages();
    }

    if (openAiChat && aiChatWidget) {
        openAiChat.addEventListener("click", () => {
            aiChatWidget.classList.add("active");
            localStorage.setItem(WIDGET_OPEN_KEY, "true");
            restoreWidgetMessages();
        });
    }

    if (closeAiChat && aiChatWidget) {
        closeAiChat.addEventListener("click", async () => {
            if (window.isAiThinking) {
                if (typeof showToast === "function") {
                    showToast("AI sedang berpikir & memproses data. Mohon tunggu hingga selesai.", "warning");
                }
                return;
            }

            aiChatWidget.classList.remove("active");
            localStorage.setItem(WIDGET_OPEN_KEY, "false");

            clearChatHistory();
            restoreWidgetMessages();

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
                await fetch("/api/agent/reset", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
                    body: JSON.stringify({ session_id: "simox-web-widget-global" })
                });
            } catch (e) {}
        });
    }

    // --- AI Chat Widget Logic ---
    const aiChatBody = document.getElementById("aiChatBody");
    const aiChatInput = document.getElementById("aiChatInput");
    const aiChatSendBtn = document.getElementById("aiChatSendBtn");

    if (aiChatSendBtn && aiChatInput && aiChatBody) {
        let isWidgetThinking = false;

        const sendMessage = async (customMessage = null) => {
            if (isWidgetThinking || window.isAiThinking) return;

            const message = (customMessage || aiChatInput.value).trim();
            if (!message) return;

            isWidgetThinking = true;
            setAiThinking(true);
            aiChatSendBtn.disabled = true;
            aiChatInput.disabled = true;
            const widgetIcon = aiChatSendBtn.querySelector("i");
            if (widgetIcon) {
                widgetIcon.className = "ph ph-spinner ph-spin";
            }

            // Trigger button animation & input flash
            aiChatSendBtn.classList.add("btn-sending");
            aiChatInput.classList.add("input-sent-flash");
            setTimeout(() => {
                aiChatSendBtn.classList.remove("btn-sending");
                aiChatInput.classList.remove("input-sent-flash");
            }, 600);

            // Add user message to UI
            const userMsgDiv = document.createElement("div");
            userMsgDiv.className = "chat-message user";
            userMsgDiv.innerHTML = `<div class="msg-content"></div>`;
            userMsgDiv.querySelector(".msg-content").textContent = message;
            aiChatBody.appendChild(userMsgDiv);
            aiChatInput.value = "";
            aiChatBody.scrollTo({ top: aiChatBody.scrollHeight, behavior: "smooth" });

            // Simpan pesan pengguna ke history widget global & pastikan state widget open tersimpan
            saveChatMessage("user", message, null, null);
            localStorage.setItem(WIDGET_OPEN_KEY, "true");

            // Add loading indicator with live timer
            const botMsgDiv = document.createElement("div");
            botMsgDiv.className = "chat-message bot";
            botMsgDiv.innerHTML = `
                <div class="msg-content">
                    <div class="typing-indicator">
                        <div class="typing-dots"><span></span><span></span><span></span></div>
                        <span class="typing-text">Sedang berpikir...</span>
                        <span class="typing-timer"><i class="ph ph-timer"></i> <span class="timer-sec">0.0</span>s</span>
                    </div>
                </div>
            `;
            aiChatBody.appendChild(botMsgDiv);
            aiChatBody.scrollTo({ top: aiChatBody.scrollHeight, behavior: "smooth" });

            const startTime = Date.now();
            const timerSecEl = botMsgDiv.querySelector(".timer-sec");
            const timerInterval = setInterval(() => {
                if (timerSecEl) {
                    timerSecEl.textContent = ((Date.now() - startTime) / 1000).toFixed(1);
                }
            }, 100);

            try {
                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content");
                const response = await fetch("/api/chat", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({
                        message,
                        session_id: "simox-web-widget-global",
                    }),
                });

                const durationSec = ((Date.now() - startTime) / 1000).toFixed(1);
                const data = await response.json();
                const rawReply = data.reply || "Tidak ada respons";
                botMsgDiv.dataset.rawText = rawReply;
                const contentEl = botMsgDiv.querySelector(".msg-content");
                contentEl.classList.remove("typing-indicator-content");
                contentEl.classList.add("msg-reply-animated");
                contentEl.innerHTML = formatAiReply(rawReply, durationSec);

                // Simpan respons bot ke history widget global
                saveChatMessage("bot", null, rawReply, durationSec);
            } catch (error) {
                const contentEl = botMsgDiv.querySelector(".msg-content");
                contentEl.classList.remove("typing-indicator-content");
                contentEl.classList.add("msg-reply-animated");
                contentEl.innerHTML = `<span style="color: var(--danger);">Maaf, terjadi kesalahan komunikasi dengan server.</span>`;
            } finally {
                clearInterval(timerInterval);
                isWidgetThinking = false;
                setAiThinking(false);
                aiChatSendBtn.disabled = false;
                if (widgetIcon) {
                    widgetIcon.className = "ph-fill ph-paper-plane-right";
                }
                aiChatInput.disabled = false;
                aiChatInput.focus();
            }
            aiChatBody.scrollTo({ top: aiChatBody.scrollHeight, behavior: "smooth" });
        };

        window.sendAiWidgetPrompt = (promptText) => {
            if (!isWidgetThinking && !window.isAiThinking) {
                sendMessage(promptText);
            }
        };

        aiChatSendBtn.addEventListener("click", () => sendMessage());
        aiChatInput.addEventListener("keypress", (e) => {
            if (e.key === "Enter" && !isWidgetThinking && !window.isAiThinking) {
                sendMessage();
            }
        });
    }

    // --- Fullpage AI Chat Logic ---
    const fullpageChatBody = document.getElementById("fullpageChatBody");
    const fullpageChatInput = document.getElementById("fullpageChatInput");
    const fullpageSendBtn = document.getElementById("fullpageSendBtn");
    const fullpageResetBtn = document.getElementById("fullpageResetBtn");
    const promptChips = document.querySelectorAll(".prompt-chip");

    if (fullpageChatBody && fullpageChatInput && fullpageSendBtn) {
        let isFullpageThinking = false;

        function restoreFullpageMessages() {
            const body = document.getElementById("fullpageChatBody");
            if (!body) return;
            const history = getStoredChatHistory();
            if (!history || history.length === 0) return;

            // Hapus pesan dinamis sebelumnya setelah pesan bot awal
            const messages = body.querySelectorAll(".chat-message");
            messages.forEach((msg, idx) => {
                if (idx > 0) msg.remove();
            });

            history.forEach((msg) => {
                if (msg.sender === "user") {
                    const userDiv = document.createElement("div");
                    userDiv.className = "chat-message user";
                    userDiv.innerHTML = `
                        <div class="chat-avatar user-avatar"><i class="ph-fill ph-user"></i></div>
                        <div class="msg-content"></div>
                    `;
                    userDiv.querySelector(".msg-content").textContent = msg.text;
                    body.appendChild(userDiv);
                } else {
                    const botDiv = document.createElement("div");
                    botDiv.className = "chat-message bot";
                    botDiv.dataset.rawText = msg.rawReply;
                    botDiv.innerHTML = `
                        <div class="chat-avatar bot-avatar"><i class="ph-fill ph-robot"></i></div>
                        <div class="msg-content">${formatAiReply(msg.rawReply, msg.duration)}</div>
                    `;
                    body.appendChild(botDiv);
                }
            });

            body.scrollTop = body.scrollHeight;
        }

        // Pulihkan percakapan di halaman penuh saat dimuat
        restoreFullpageMessages();

        const sendFullpageMessage = async (customMessage = null) => {
            if (isFullpageThinking || window.isAiThinking) return;

            const message = (customMessage || fullpageChatInput.value).trim();
            if (!message) return;

            isFullpageThinking = true;
            setAiThinking(true);
            fullpageSendBtn.disabled = true;
            fullpageChatInput.disabled = true;
            promptChips.forEach((chip) => chip.classList.add("disabled"));

            const sendBtnSpan = fullpageSendBtn.querySelector("span");
            const sendBtnIcon = fullpageSendBtn.querySelector("i");
            if (sendBtnSpan) sendBtnSpan.textContent = "Berpikir...";
            if (sendBtnIcon) sendBtnIcon.className = "ph ph-spinner ph-spin";

            // Trigger button animation & input wrapper flash
            const inputWrapper = document.querySelector(".ai-input-wrapper");
            fullpageSendBtn.classList.add("btn-sending");
            if (inputWrapper) inputWrapper.classList.add("input-sent-flash");
            setTimeout(() => {
                fullpageSendBtn.classList.remove("btn-sending");
                if (inputWrapper) inputWrapper.classList.remove("input-sent-flash");
            }, 600);

            // User message bubble
            const userDiv = document.createElement("div");
            userDiv.className = "chat-message user";
            userDiv.innerHTML = `
                <div class="chat-avatar user-avatar"><i class="ph-fill ph-user"></i></div>
                <div class="msg-content"></div>
            `;
            userDiv.querySelector(".msg-content").textContent = message;
            fullpageChatBody.appendChild(userDiv);

            fullpageChatInput.value = "";
            fullpageChatBody.scrollTo({ top: fullpageChatBody.scrollHeight, behavior: "smooth" });

            // Simpan pesan user ke history global
            saveChatMessage("user", message);
            localStorage.setItem(WIDGET_OPEN_KEY, "true");

            // Bot message thinking bubble with live timer
            const botDiv = document.createElement("div");
            botDiv.className = "chat-message bot";
            botDiv.innerHTML = `
                <div class="chat-avatar bot-avatar"><i class="ph-fill ph-robot"></i></div>
                <div class="msg-content">
                    <div class="typing-indicator">
                        <div class="typing-dots"><span></span><span></span><span></span></div>
                        <span class="typing-text">Sedang berpikir...</span>
                        <span class="typing-timer"><i class="ph ph-timer"></i> <span class="timer-sec">0.0</span>s</span>
                    </div>
                </div>
            `;
            fullpageChatBody.appendChild(botDiv);
            fullpageChatBody.scrollTo({ top: fullpageChatBody.scrollHeight, behavior: "smooth" });

            const startTime = Date.now();
            const timerSecEl = botDiv.querySelector(".timer-sec");
            const timerInterval = setInterval(() => {
                if (timerSecEl) {
                    timerSecEl.textContent = ((Date.now() - startTime) / 1000).toFixed(1);
                }
            }, 100);

            try {
                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content");
                const response = await fetch("/api/chat", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({
                        message,
                        session_id: "simox-web-widget-global",
                    }),
                });

                const durationSec = ((Date.now() - startTime) / 1000).toFixed(1);
                const data = await response.json();
                const rawReply = data.reply || "Tidak ada respons dari agen.";
                botDiv.dataset.rawText = rawReply;
                const contentEl = botDiv.querySelector(".msg-content");
                contentEl.classList.remove("typing-indicator-content");
                contentEl.classList.add("msg-reply-animated");
                contentEl.innerHTML = formatAiReply(rawReply, durationSec);

                // Simpan respons bot ke history global
                saveChatMessage("bot", null, rawReply, durationSec);
            } catch (error) {
                const contentEl = botDiv.querySelector(".msg-content");
                contentEl.classList.remove("typing-indicator-content");
                contentEl.classList.add("msg-reply-animated");
                contentEl.innerHTML = `<span style="color: var(--danger);"><i class="ph ph-warning-circle"></i> Gagal berkomunikasi dengan gateway AI.</span>`;
            } finally {
                clearInterval(timerInterval);
                isFullpageThinking = false;
                setAiThinking(false);
                fullpageSendBtn.disabled = false;
                if (sendBtnSpan) sendBtnSpan.textContent = "Kirim";
                if (sendBtnIcon) sendBtnIcon.className = "ph-fill ph-paper-plane-right";
                promptChips.forEach((chip) => chip.classList.remove("disabled"));
                fullpageChatInput.disabled = false;
                fullpageChatInput.focus();
            }

            fullpageChatBody.scrollTo({ top: fullpageChatBody.scrollHeight, behavior: "smooth" });
        };

        fullpageSendBtn.addEventListener("click", () => {
            if (!isFullpageThinking && !window.isAiThinking) {
                sendFullpageMessage();
            }
        });

        fullpageChatInput.addEventListener("keydown", (e) => {
            if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault();
                if (!isFullpageThinking && !window.isAiThinking) {
                    sendFullpageMessage();
                }
            }
        });

        // Prompt Chips Click
        promptChips.forEach((chip) => {
            chip.addEventListener("click", () => {
                if (!isFullpageThinking && !window.isAiThinking) {
                    const prompt = chip.getAttribute("data-prompt");
                    if (prompt) {
                        sendFullpageMessage(prompt);
                    }
                }
            });
        });

        // Reset Chat Session
        if (fullpageResetBtn) {
            fullpageResetBtn.addEventListener("click", async () => {
                if (window.isAiThinking) {
                    if (typeof showToast === "function") {
                        showToast("AI sedang berpikir & memproses data. Harap tunggu hingga selesai.", "warning");
                    }
                    return;
                }
                if (!confirm("Reset riwayat percakapan sesi ini?")) return;
                try {
                    const csrfToken = document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content");
                    await fetch("/api/agent/reset", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                        },
                        body: JSON.stringify({ session_id: "simox-web-widget-global" }),
                    });
                    clearChatHistory();
                    localStorage.setItem(WIDGET_OPEN_KEY, "false");
                    showToast("Sesi percakapan berhasil direset.", "success");
                    
                    // Hapus pesan dinamis kecuali pesan bot awal
                    const messages =
                        fullpageChatBody.querySelectorAll(".chat-message");
                    messages.forEach((msg, idx) => {
                        if (idx > 0) msg.remove();
                    });
                } catch (e) {
                    showToast("Gagal mereset sesi percakapan.", "error");
                }
            });
        }

        // Tutup Agent Button
        const fullpageCloseBtn = document.getElementById("fullpageCloseBtn");
        if (fullpageCloseBtn) {
            fullpageCloseBtn.addEventListener("click", async () => {
                if (window.isAiThinking) {
                    if (typeof showToast === "function") {
                        showToast("AI sedang berpikir & memproses data. Harap tunggu hingga selesai.", "warning");
                    }
                    return;
                }
                if (!confirm("Tutup sesi AI Agent dan bersihkan riwayat percakapan?")) return;
                try {
                    const csrfToken = document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content");
                    await fetch("/api/agent/reset", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                        },
                        body: JSON.stringify({ session_id: "simox-web-widget-global" }),
                    });
                } catch (e) {}
                clearChatHistory();
                localStorage.setItem(WIDGET_OPEN_KEY, "false");
                window.location.href = "/dashboard";
            });
        }

        // Auto-send prompt from URL query string (?prompt=...)
        const urlParams = new URLSearchParams(window.location.search);
        const queryPrompt = urlParams.get("prompt");
        if (queryPrompt) {
            sendFullpageMessage(queryPrompt);
        }
    }

    // Global event listener for AI PDF Download and Copy actions (widget + fullpage)
    document.addEventListener("click", async (e) => {
        const pdfBtn = e.target.closest(
            ".btn-download-pdf-card, .btn-action-pdf",
        );
        if (pdfBtn) {
            e.preventDefault();
            const chatMsg = pdfBtn.closest(".chat-message");
            const filename =
                pdfBtn.getAttribute("data-filename") ||
                "laporan-analisis.pdf";
            const source = pdfBtn.getAttribute("data-source") || "";
            let content = chatMsg?.dataset?.rawText;
            if (!content) {
                const contentEl = chatMsg?.querySelector(".msg-content");
                if (contentEl) {
                    const clone = contentEl.cloneNode(true);
                    clone
                        .querySelectorAll(
                            ".ai-pdf-download-card, .msg-actions-bar",
                        )
                        .forEach((el) => el.remove());
                    content = clone.innerText;
                }
            }
            if (content || source) {
                await window.downloadAiReportPdf(content, filename, "", source);
            } else {
                showToast("Konten laporan tidak ditemukan.", "warning");
            }
            return;
        }

        const copyBtn = e.target.closest(".btn-action-copy");
        if (copyBtn) {
            e.preventDefault();
            const chatMsg = copyBtn.closest(".chat-message");
            let content = chatMsg?.dataset?.rawText;
            if (!content) {
                const contentEl = chatMsg?.querySelector(".msg-content");
                if (contentEl) {
                    const clone = contentEl.cloneNode(true);
                    clone
                        .querySelectorAll(
                            ".ai-pdf-download-card, .msg-actions-bar",
                        )
                        .forEach((el) => el.remove());
                    content = clone.innerText;
                }
            }
            if (content) {
                navigator.clipboard
                    .writeText(content)
                    .then(() => {
                        showToast(
                            "Teks berhasil disalin ke clipboard!",
                            "success",
                        );
                    })
                    .catch(() => {
                        showToast("Gagal menyalin teks.", "error");
                    });
            }
            return;
        }
    });

    // --- Modal Logic ---
    const createVmModal = document.getElementById("createVmModal");
    const openModalBtn = document.getElementById("openModalBtn");
    const closeModalBtn = document.getElementById("closeModalBtn");
    const cancelModalBtn = document.getElementById("cancelModalBtn");
    const saveModalBtn = document.getElementById("saveModalBtn");

    const openModal = () => createVmModal.classList.add("active");
    const closeModal = () => createVmModal.classList.remove("active");

    if (openModalBtn) openModalBtn.addEventListener("click", openModal);
    if (closeModalBtn) closeModalBtn.addEventListener("click", closeModal);
    if (cancelModalBtn) cancelModalBtn.addEventListener("click", closeModal);

    // Close modal when clicking outside (global for all modals)
    document.addEventListener("click", (e) => {
        if (e.target.classList.contains("modal-overlay")) {
            e.target.classList.remove("active");
        }
    });

    if (saveModalBtn) {
        saveModalBtn.addEventListener("click", () => {
            closeModal();
            showToast("Mesin Virtual sedang dibuat di background.", "success");
        });
    }

    // --- Chart.js Configuration ---
    const chartCanvas = document.getElementById("resourceChart");
    if (chartCanvas) {
        const ctx = chartCanvas.getContext("2d");
        const gradientCpu = ctx.createLinearGradient(0, 0, 0, 200);
        gradientCpu.addColorStop(0, "rgba(79, 70, 229, 0.4)");
        gradientCpu.addColorStop(1, "rgba(79, 70, 229, 0.0)");

        const gradientRam = ctx.createLinearGradient(0, 0, 0, 200);
        gradientRam.addColorStop(0, "rgba(14, 165, 233, 0.4)");
        gradientRam.addColorStop(1, "rgba(14, 165, 233, 0.0)");

        new Chart(ctx, {
            type: "line",
            data: {
                labels: ["00:00", "04:00", "08:00", "12:00", "16:00", "20:00"],
                datasets: [
                    {
                        label: "CPU (%)",
                        data: [35, 25, 45, 75, 60, 40],
                        borderColor: "#4f46e5",
                        backgroundColor: gradientCpu,
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                    },
                    {
                        label: "RAM (%)",
                        data: [45, 40, 55, 65, 70, 60],
                        borderColor: "#0ea5e9",
                        backgroundColor: gradientRam,
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "top",
                        align: "end",
                        labels: { boxWidth: 8, font: { size: 10 } },
                    },
                    tooltip: {
                        mode: "index",
                        intersect: false,
                        padding: 8,
                        titleFont: { size: 11 },
                        bodyFont: { size: 11 },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } },
                    },
                    y: {
                        grid: { borderDash: [3, 3] },
                        ticks: {
                            font: { size: 10 },
                            stepSize: 25,
                            max: 100,
                            min: 0,
                        },
                    },
                },
                interaction: { mode: "nearest", axis: "x", intersect: false },
            },
        });
    }

    // --- Dashboard AI Insights Refresh Logic ---
    const refreshAiInsightsBtn = document.getElementById(
        "refreshAiInsightsBtn",
    );
    const refreshAiInsightsIcon = document.getElementById(
        "refreshAiInsightsIcon",
    );
    const aiAnalysisStatusText = document.getElementById(
        "aiAnalysisStatusText",
    );
    const aiInsightsTableBody = document.getElementById("aiInsightsTableBody");

    if (refreshAiInsightsBtn && aiInsightsTableBody) {
        refreshAiInsightsBtn.addEventListener("click", async () => {
            refreshAiInsightsBtn.disabled = true;
            if (refreshAiInsightsIcon)
                refreshAiInsightsIcon.classList.add("ph-spin");
            if (aiAnalysisStatusText)
                aiAnalysisStatusText.textContent = "Menganalisis...";

            try {
                const res = await fetch("/api/dashboard/insights?refresh=1");
                const data = await res.json();

                if (data.success && Array.isArray(data.insights)) {
                    aiInsightsTableBody.innerHTML = "";
                    if (data.insights.length === 0) {
                        aiInsightsTableBody.innerHTML = `
                            <tr>
                                <td colspan="2" class="text-center text-muted py-3">
                                    Belum ada insight aktif. Klik tombol <strong>Analisis AI Sekarang</strong> di atas.
                                </td>
                            </tr>
                        `;
                    } else {
                        data.insights.forEach((insight) => {
                            const level = insight.level || "info";
                            let badgeClass = "bg-info-light text-info";
                            let icon = "ph-info";

                            if (level === "danger") {
                                badgeClass = "bg-danger-light text-danger";
                                icon = "ph-warning-octagon";
                            } else if (level === "warning") {
                                badgeClass = "bg-warning-light text-warning";
                                icon = "ph-warning";
                            } else if (level === "success") {
                                badgeClass = "bg-success-light text-success";
                                icon = "ph-check-circle";
                            }

                            const tr = document.createElement("tr");
                            tr.innerHTML = `
                                <td>
                                    <span class="badge ${badgeClass}">
                                        <i class="ph ${icon}"></i> ${escapeHtml(insight.badge || level)}
                                    </span>
                                </td>
                                <td class="insight-content-cell">
                                    <strong>${escapeHtml(insight.title)}</strong><br>
                                    <span class="text-muted text-xs">${escapeHtml(insight.description)}</span>
                                </td>
                            `;
                            aiInsightsTableBody.appendChild(tr);
                        });
                    }
                    const sourceText =
                        data.source === "openclaw" ? "OpenClaw AI" : "Sistem";
                    showToast(
                        `Analisis ${sourceText} berhasil diperbarui (${data.updated_at}).`,
                        "success",
                    );
                }
            } catch (err) {
                showToast("Gagal memperbarui analisis AI.", "danger");
            } finally {
                refreshAiInsightsBtn.disabled = false;
                if (refreshAiInsightsIcon)
                    refreshAiInsightsIcon.classList.remove("ph-spin");
                if (aiAnalysisStatusText)
                    aiAnalysisStatusText.textContent = "Analisis AI Sekarang";
            }
        });
    }

    // --- Global Navbar Realtime Search ---
    const searchBar = document.getElementById("navbarSearchBar");
    const searchInput = document.getElementById("navbarSearchInput");
    const searchClear = document.getElementById("navbarSearchClear");
    const searchResults = document.getElementById("navbarSearchResults");
    const searchResultsBody = document.getElementById("navbarSearchResultsBody");
    const searchLoading = document.getElementById("navbarSearchLoading");
    const searchFooter = document.getElementById("navbarSearchFooter");

    let searchDebounceTimer = null;
    let selectedResultIndex = -1;

    if (searchInput && searchResults && searchResultsBody) {
        const doSearch = async (query) => {
            query = query.trim();
            if (!query) {
                searchResults.classList.remove("active");
                searchResultsBody.innerHTML = "";
                if (searchClear) searchClear.style.display = "none";
                if (searchFooter) searchFooter.style.display = "none";
                return;
            }

            if (searchClear) searchClear.style.display = "flex";
            if (searchLoading) searchLoading.style.display = "flex";
            searchResults.classList.add("active");
            selectedResultIndex = -1;

            try {
                const res = await fetch(`/api/search?q=${encodeURIComponent(query)}`);
                const data = await res.json();

                if (searchLoading) searchLoading.style.display = "none";

                const vms = data.vms || [];
                const nodes = data.nodes || [];
                const total = data.total || 0;

                if (total === 0) {
                    searchResultsBody.innerHTML = `
                        <div class="search-empty-state">
                            <i class="ph ph-magnifying-glass"></i>
                            <p>Tidak ada data ditemukan untuk "<strong>${escapeHtml(query)}</strong>"</p>
                        </div>
                    `;
                    if (searchFooter) searchFooter.style.display = "none";
                    return;
                }

                let html = "";

                // Virtual Machines / LXC
                if (vms.length > 0) {
                    html += `
                        <div class="search-section">
                            <div class="search-section-title">
                                <i class="ph-bold ph-hard-drives"></i> Virtual Machines & LXC (${vms.length})
                            </div>
                    `;
                    vms.forEach((vm) => {
                        const isRunning = (vm.status || "").toLowerCase() === "running";
                        const iconClass = vm.tipe === "LXC" ? "lxc" : "vm";
                        const iconPh = vm.tipe === "LXC" ? "ph-package" : "ph-desktop";
                        const badgeClass = isRunning ? "search-badge-running" : "search-badge-stopped";

                        html += `
                            <a href="${vm.url}" class="search-result-item" data-url="${vm.url}">
                                <div class="search-result-icon ${iconClass}">
                                    <i class="ph-fill ${iconPh}"></i>
                                </div>
                                <div class="search-result-info">
                                    <div class="search-result-title">
                                        <span>${escapeHtml(vm.hostname)}</span>
                                        <span class="search-result-badge ${badgeClass}">${escapeHtml(vm.status)}</span>
                                        <span class="badge" style="font-size: 0.65rem; padding: 1px 4px; background: rgba(255,255,255,0.06);">${escapeHtml(vm.tipe)}</span>
                                    </div>
                                    <div class="search-result-meta">
                                        <span><i class="ph ph-buildings"></i> ${escapeHtml(vm.dinas)}</span>
                                        <span>&bull;</span>
                                        <span><i class="ph ph-server"></i> ${escapeHtml(vm.node)}</span>
                                        <span>&bull;</span>
                                        <span><i class="ph ph-globe"></i> ${escapeHtml(vm.ip)}</span>
                                    </div>
                                </div>
                                <i class="ph ph-caret-right text-muted" style="font-size: 0.9rem;"></i>
                            </a>
                        `;
                    });
                    html += `</div>`;
                }

                // Server Fisik / Nodes
                if (nodes.length > 0) {
                    html += `
                        <div class="search-section">
                            <div class="search-section-title">
                                <i class="ph-bold ph-server"></i> Server Fisik / Nodes (${nodes.length})
                            </div>
                    `;
                    nodes.forEach((node) => {
                        html += `
                            <a href="${node.url}" class="search-result-item" data-url="${node.url}">
                                <div class="search-result-icon node">
                                    <i class="ph-fill ph-server"></i>
                                </div>
                                <div class="search-result-info">
                                    <div class="search-result-title">
                                        <span>${escapeHtml(node.nama)}</span>
                                        <span class="search-result-badge search-badge-node">${escapeHtml(node.status)}</span>
                                    </div>
                                    <div class="search-result-meta">
                                        <span><i class="ph ph-network"></i> IP: ${escapeHtml(node.ip)}</span>
                                        <span>&bull;</span>
                                        <span><i class="ph ph-hard-drives"></i> ${node.vm_count} VM/LXC</span>
                                        <span>&bull;</span>
                                        <span>PVE ${escapeHtml(node.versi)}</span>
                                    </div>
                                </div>
                                <i class="ph ph-caret-right text-muted" style="font-size: 0.9rem;"></i>
                            </a>
                        `;
                    });
                    html += `</div>`;
                }

                searchResultsBody.innerHTML = html;
                if (searchFooter) searchFooter.style.display = "flex";

            } catch (err) {
                console.error("Live search error:", err);
                if (searchLoading) searchLoading.style.display = "none";
                searchResultsBody.innerHTML = `
                    <div class="search-empty-state">
                        <i class="ph ph-warning-circle text-danger"></i>
                        <p>Gagal memuat hasil pencarian.</p>
                    </div>
                `;
            }
        };

        searchInput.addEventListener("input", (e) => {
            clearTimeout(searchDebounceTimer);
            const val = e.target.value;
            if (!val.trim()) {
                searchResults.classList.remove("active");
                if (searchClear) searchClear.style.display = "none";
                return;
            }
            if (searchClear) searchClear.style.display = "flex";
            searchDebounceTimer = setTimeout(() => {
                doSearch(val);
            }, 180);
        });

        searchInput.addEventListener("focus", () => {
            if (searchInput.value.trim().length > 0) {
                searchResults.classList.add("active");
            }
        });

        // Keyboard navigation (Arrow up/down, Enter, Escape)
        searchInput.addEventListener("keydown", (e) => {
            const items = searchResultsBody.querySelectorAll(".search-result-item");
            if (e.key === "ArrowDown") {
                e.preventDefault();
                if (items.length === 0) return;
                selectedResultIndex = (selectedResultIndex + 1) % items.length;
                updateSelectedResult(items);
            } else if (e.key === "ArrowUp") {
                e.preventDefault();
                if (items.length === 0) return;
                selectedResultIndex = (selectedResultIndex - 1 + items.length) % items.length;
                updateSelectedResult(items);
            } else if (e.key === "Enter") {
                e.preventDefault();
                if (window.isAiThinking) {
                    if (typeof showToast === "function") {
                        showToast("AI sedang berpikir & memproses data. Harap tunggu hingga selesai sebelum berpindah halaman.", "warning");
                    }
                    return;
                }
                if (selectedResultIndex >= 0 && items[selectedResultIndex]) {
                    window.location.href = items[selectedResultIndex].getAttribute("data-url");
                } else if (items.length > 0) {
                    window.location.href = items[0].getAttribute("data-url");
                } else if (searchInput.value.trim()) {
                    window.location.href = `/vps?search=${encodeURIComponent(searchInput.value.trim())}`;
                }
            } else if (e.key === "Escape") {
                searchResults.classList.remove("active");
                searchInput.blur();
            }
        });

        function updateSelectedResult(items) {
            items.forEach((item, idx) => {
                if (idx === selectedResultIndex) {
                    item.classList.add("selected");
                    item.scrollIntoView({ block: "nearest" });
                } else {
                    item.classList.remove("selected");
                }
            });
        }

        if (searchClear) {
            searchClear.addEventListener("click", () => {
                searchInput.value = "";
                searchClear.style.display = "none";
                searchResults.classList.remove("active");
                searchResultsBody.innerHTML = "";
                searchInput.focus();
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener("click", (e) => {
            if (searchBar && !searchBar.contains(e.target)) {
                searchResults.classList.remove("active");
            }
        });
    }

    // Auto-filter on page load if ?search= is in query string
    const globalUrlSearch = new URLSearchParams(window.location.search).get("search");
    if (globalUrlSearch) {
        const pageSearchInput = document.getElementById("searchInput");
        if (pageSearchInput && typeof filterItems === "function") {
            pageSearchInput.value = globalUrlSearch;
            filterItems();
        }
    }
});

// --- Toast Notification System ---
window.showToast = function (message, type = "info") {
    const container = document.getElementById("toastContainer");
    if (!container) return;

    const toast = document.createElement("div");
    toast.className = `toast ${type}`;

    let iconClass = "ph-info";
    if (type === "success") iconClass = "ph-check-circle";
    if (type === "danger") iconClass = "ph-x-circle";
    if (type === "warning") iconClass = "ph-warning";

    toast.innerHTML = `
        <i class="ph-fill ${iconClass} toast-icon"></i>
        <span class="toast-msg">${message}</span>
    `;

    container.appendChild(toast);

    // Remove toast after 3 seconds
    setTimeout(() => {
        toast.style.animation = "fadeOut 0.3s forwards";
        setTimeout(() => {
            if (container.contains(toast)) {
                container.removeChild(toast);
            }
        }, 300);
    }, 3000);
};

// Close user dropdown if clicked outside
window.addEventListener("click", function (e) {
    if (!e.target.closest(".user-profile")) {
        const dropdown = document.getElementById("userDropdown");
        if (dropdown && dropdown.classList.contains("show")) {
            dropdown.classList.remove("show");
        }
    }
});
