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

    // --- Markdown / Formatter Helper ---
    function formatAiReply(text) {
        if (!text) return "Tidak ada respons";

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
                    return DOMPurify.sanitize(rawHtml);
                }
                return rawHtml;
            } catch (e) {
                console.error("Markdown parse error:", e);
            }
        }

        // Fallback Formatter if marked is not loaded
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
        return safeText;
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

            // Add user message to UI
            const userMsgDiv = document.createElement("div");
            userMsgDiv.className = "chat-message user";
            userMsgDiv.innerHTML = `<div class="msg-content"></div>`;
            userMsgDiv.querySelector(".msg-content").textContent = message;
            aiChatBody.appendChild(userMsgDiv);
            aiChatInput.value = "";
            aiChatBody.scrollTop = aiChatBody.scrollHeight;

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
            aiChatBody.scrollTop = aiChatBody.scrollHeight;

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
                const contentEl = botMsgDiv.querySelector(".msg-content");
                contentEl.classList.remove("typing-indicator-content");
                contentEl.innerHTML = formatAiReply(
                    data.reply || "Tidak ada respons",
                );
            } catch (error) {
                const contentEl = botMsgDiv.querySelector(".msg-content");
                contentEl.classList.remove("typing-indicator-content");
                contentEl.innerHTML = `<span style="color: var(--danger);">Maaf, terjadi kesalahan komunikasi dengan server.</span>`;
            }
            aiChatBody.scrollTop = aiChatBody.scrollHeight;
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
            fullpageChatBody.scrollTop = fullpageChatBody.scrollHeight;

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
            fullpageChatBody.scrollTop = fullpageChatBody.scrollHeight;

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
                const contentEl = botDiv.querySelector(".msg-content");
                contentEl.classList.remove("typing-indicator-content");
                contentEl.innerHTML = formatAiReply(
                    data.reply || "Tidak ada respons dari agen.",
                );
            } catch (error) {
                const contentEl = botDiv.querySelector(".msg-content");
                contentEl.classList.remove("typing-indicator-content");
                contentEl.innerHTML = `<span style="color: var(--danger);"><i class="ph ph-warning-circle"></i> Gagal berkomunikasi dengan gateway AI.</span>`;
            }

            fullpageChatBody.scrollTop = fullpageChatBody.scrollHeight;
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
