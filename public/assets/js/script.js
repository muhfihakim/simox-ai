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
    function formatAiReply(text) {
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

        // Add action bar for reports/analyses
        let actionsHtml = "";
        const isReport =
            pdfMatch ||
            sourcePath ||
            text.includes("|") ||
            text.length > 140 ||
            text.includes("###");
        if (isReport) {
            actionsHtml = `
                <div class="msg-actions-bar">
                    <button type="button" class="btn-msg-action btn-action-pdf" data-filename="${escapeHtml(
                        filename,
                    )}" data-source="${escapeHtml(
                        sourcePath,
                    )}" title="Unduh Analisis sebagai PDF">
                        <i class="ph-bold ph-file-pdf"></i> Unduh PDF
                    </button>
                    <button type="button" class="btn-msg-action btn-action-copy" title="Salin Jawaban">
                        <i class="ph ph-copy"></i> Salin
                    </button>
                </div>
            `;
        }

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

    // --- AI Chat Widget Toggle ---
    const aiChatWidget = document.getElementById("aiChatWidget");
    const openAiChat = document.getElementById("openAiChat");
    const closeAiChat = document.getElementById("closeAiChat");

    if (openAiChat && aiChatWidget) {
        openAiChat.addEventListener("click", () =>
            aiChatWidget.classList.add("active"),
        );
    }
    if (closeAiChat && aiChatWidget) {
        closeAiChat.addEventListener("click", () =>
            aiChatWidget.classList.remove("active"),
        );
    }

    // --- AI Chat Widget Logic ---
    const aiChatBody = document.getElementById("aiChatBody");
    const aiChatInput = document.getElementById("aiChatInput");
    const aiChatSendBtn = document.getElementById("aiChatSendBtn");

    if (aiChatSendBtn && aiChatInput && aiChatBody) {
        const sendMessage = async () => {
            const message = aiChatInput.value.trim();
            if (!message) return;

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

            // Add loading indicator
            const botMsgDiv = document.createElement("div");
            botMsgDiv.className = "chat-message bot";
            botMsgDiv.innerHTML = `
                <div class="msg-content">
                    <div class="typing-indicator">
                        <div class="typing-dots"><span></span><span></span><span></span></div>
                        <span class="typing-text">Sedang berpikir...</span>
                    </div>
                </div>
            `;
            aiChatBody.appendChild(botMsgDiv);
            aiChatBody.scrollTo({ top: aiChatBody.scrollHeight, behavior: "smooth" });

            try {
                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content");
                const response = await fetch("/api/chat", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({ message }),
                });

                const data = await response.json();
                const rawReply = data.reply || "Tidak ada respons";
                botMsgDiv.dataset.rawText = rawReply;
                const contentEl = botMsgDiv.querySelector(".msg-content");
                contentEl.classList.remove("typing-indicator-content");
                contentEl.classList.add("msg-reply-animated");
                contentEl.innerHTML = formatAiReply(rawReply);
            } catch (error) {
                const contentEl = botMsgDiv.querySelector(".msg-content");
                contentEl.classList.remove("typing-indicator-content");
                contentEl.classList.add("msg-reply-animated");
                contentEl.innerHTML = `<span style="color: var(--danger);">Maaf, terjadi kesalahan komunikasi dengan server.</span>`;
            }
            aiChatBody.scrollTo({ top: aiChatBody.scrollHeight, behavior: "smooth" });
        };

        aiChatSendBtn.addEventListener("click", sendMessage);
        aiChatInput.addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
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
        const sendFullpageMessage = async (customMessage = null) => {
            const message = (customMessage || fullpageChatInput.value).trim();
            if (!message) return;

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

            // Bot message thinking bubble
            const botDiv = document.createElement("div");
            botDiv.className = "chat-message bot";
            botDiv.innerHTML = `
                <div class="chat-avatar bot-avatar"><i class="ph-fill ph-robot"></i></div>
                <div class="msg-content">
                    <div class="typing-indicator">
                        <div class="typing-dots"><span></span><span></span><span></span></div>
                        <span class="typing-text">Sedang berpikir...</span>
                    </div>
                </div>
            `;
            fullpageChatBody.appendChild(botDiv);
            fullpageChatBody.scrollTo({ top: fullpageChatBody.scrollHeight, behavior: "smooth" });

            try {
                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content");
                const response = await fetch("/api/chat", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({ message }),
                });

                const data = await response.json();
                const rawReply = data.reply || "Tidak ada respons dari agen.";
                botDiv.dataset.rawText = rawReply;
                const contentEl = botDiv.querySelector(".msg-content");
                contentEl.classList.remove("typing-indicator-content");
                contentEl.classList.add("msg-reply-animated");
                contentEl.innerHTML = formatAiReply(rawReply);
            } catch (error) {
                const contentEl = botDiv.querySelector(".msg-content");
                contentEl.classList.remove("typing-indicator-content");
                contentEl.classList.add("msg-reply-animated");
                contentEl.innerHTML = `<span style="color: var(--danger);"><i class="ph ph-warning-circle"></i> Gagal berkomunikasi dengan gateway AI.</span>`;
            }

            fullpageChatBody.scrollTo({ top: fullpageChatBody.scrollHeight, behavior: "smooth" });
        };

        fullpageSendBtn.addEventListener("click", () => sendFullpageMessage());

        fullpageChatInput.addEventListener("keydown", (e) => {
            if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault();
                sendFullpageMessage();
            }
        });

        // Prompt Chips Click
        promptChips.forEach((chip) => {
            chip.addEventListener("click", () => {
                const prompt = chip.getAttribute("data-prompt");
                if (prompt) {
                    sendFullpageMessage(prompt);
                }
            });
        });

        // Reset Chat Session
        if (fullpageResetBtn) {
            fullpageResetBtn.addEventListener("click", async () => {
                if (!confirm("Reset riwayat percakapan sesi ini?")) return;
                try {
                    const csrfToken = document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content");
                    await fetch("/api/agent/reset", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                        },
                    });
                    showToast("Sesi percakapan berhasil direset.", "success");
                    // Remove message bubbles except the first bot welcome
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
                                        <i class="ph ${icon}"></i> ${insight.badge || level}
                                    </span>
                                </td>
                                <td>
                                    <strong>${insight.title}</strong><br>
                                    <span class="text-muted text-xs">${insight.description}</span>
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
