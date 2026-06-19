document.addEventListener('DOMContentLoaded', () => {
    // --- Sidebar Toggle Logic ---
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleSidebar'); // Topbar toggle
    const closeSidebarMobile = document.getElementById('closeSidebarMobile'); // Mobile close
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    // Toggle logic for both Desktop and Mobile
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                sidebar.classList.add('mobile-open');
                sidebarOverlay.classList.add('active');
            } else {
                sidebar.classList.toggle('collapsed');
            }
        });
    }

    // Close on mobile
    const closeMobileSidebar = () => {
        sidebar.classList.remove('mobile-open');
        sidebarOverlay.classList.remove('active');
    };

    if (closeSidebarMobile) closeSidebarMobile.addEventListener('click', closeMobileSidebar);
    if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeMobileSidebar);

    // --- AI Chat Widget Toggle ---
    const aiChatWidget = document.getElementById('aiChatWidget');
    const openAiChat = document.getElementById('openAiChat');
    const closeAiChat = document.getElementById('closeAiChat');

    if (openAiChat) openAiChat.addEventListener('click', () => aiChatWidget.classList.add('active'));
    if (closeAiChat) closeAiChat.addEventListener('click', () => aiChatWidget.classList.remove('active'));

    // --- AI Chat Logic ---
    const aiChatBody = document.getElementById('aiChatBody');
    const aiChatInput = document.getElementById('aiChatInput');
    const aiChatSendBtn = document.getElementById('aiChatSendBtn');

    if (aiChatSendBtn && aiChatInput && aiChatBody) {
        const sendMessage = async () => {
            const message = aiChatInput.value.trim();
            if (!message) return;

            // Add user message to UI
            const userMsgDiv = document.createElement('div');
            userMsgDiv.className = 'chat-message user';
            userMsgDiv.innerHTML = `<div class="msg-content"></div>`;
            userMsgDiv.querySelector('.msg-content').textContent = message;
            aiChatBody.appendChild(userMsgDiv);
            aiChatInput.value = '';
            aiChatBody.scrollTop = aiChatBody.scrollHeight;

            // Add loading indicator
            const botMsgDiv = document.createElement('div');
            botMsgDiv.className = 'chat-message bot';
            botMsgDiv.innerHTML = `<div class="msg-content"><i class="ph ph-spinner ph-spin"></i> Sedang berpikir...</div>`;
            aiChatBody.appendChild(botMsgDiv);
            aiChatBody.scrollTop = aiChatBody.scrollHeight;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch('/api/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message })
                });

                const data = await response.json();
                botMsgDiv.innerHTML = `<div class="msg-content"></div>`;
                
                // Format balasan: Bold (**) dan Line breaks (\n)
                let formattedReply = (data.reply || 'Tidak ada respons')
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\n/g, '<br>');
                
                botMsgDiv.querySelector('.msg-content').innerHTML = formattedReply;
            } catch (error) {
                botMsgDiv.innerHTML = `<div class="msg-content" style="color: red;">Maaf, terjadi kesalahan komunikasi dengan server.</div>`;
            }
            aiChatBody.scrollTop = aiChatBody.scrollHeight;
        };

        aiChatSendBtn.addEventListener('click', sendMessage);
        aiChatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }

    // --- Modal Logic ---
    const createVmModal = document.getElementById('createVmModal');
    const openModalBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const saveModalBtn = document.getElementById('saveModalBtn');

    const openModal = () => createVmModal.classList.add('active');
    const closeModal = () => createVmModal.classList.remove('active');

    if (openModalBtn) openModalBtn.addEventListener('click', openModal);
    if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
    if (cancelModalBtn) cancelModalBtn.addEventListener('click', closeModal);
    
    // Close modal when clicking outside (global for all modals)
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.classList.remove('active');
        }
    });

    if (saveModalBtn) {
        saveModalBtn.addEventListener('click', () => {
            closeModal();
            showToast('Mesin Virtual sedang dibuat di background.', 'success');
        });
    }

    // --- Chart.js Configuration ---
    const chartCanvas = document.getElementById('resourceChart');
    if(chartCanvas) {
        const ctx = chartCanvas.getContext('2d');
        const gradientCpu = ctx.createLinearGradient(0, 0, 0, 200);
        gradientCpu.addColorStop(0, 'rgba(79, 70, 229, 0.4)');
        gradientCpu.addColorStop(1, 'rgba(79, 70, 229, 0.0)');

        const gradientRam = ctx.createLinearGradient(0, 0, 0, 200);
        gradientRam.addColorStop(0, 'rgba(14, 165, 233, 0.4)');
        gradientRam.addColorStop(1, 'rgba(14, 165, 233, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
                datasets: [
                    {
                        label: 'CPU (%)',
                        data: [35, 25, 45, 75, 60, 40],
                        borderColor: '#4f46e5',
                        backgroundColor: gradientCpu,
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointRadius: 2,
                        pointHoverRadius: 5
                    },
                    {
                        label: 'RAM (%)',
                        data: [45, 40, 55, 65, 70, 60],
                        borderColor: '#0ea5e9',
                        backgroundColor: gradientRam,
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointRadius: 2,
                        pointHoverRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', align: 'end', labels: { boxWidth: 8, font: { size: 10 } } },
                    tooltip: { mode: 'index', intersect: false, padding: 8, titleFont: { size: 11 }, bodyFont: { size: 11 } }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { grid: { borderDash: [3, 3] }, ticks: { font: { size: 10 }, stepSize: 25, max: 100, min: 0 } }
                },
                interaction: { mode: 'nearest', axis: 'x', intersect: false }
            }
        });
    }
});



// --- Toast Notification System ---
window.showToast = function(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    let iconClass = 'ph-info';
    if (type === 'success') iconClass = 'ph-check-circle';
    if (type === 'danger') iconClass = 'ph-x-circle';
    if (type === 'warning') iconClass = 'ph-warning';

    toast.innerHTML = `
        <i class="ph-fill ${iconClass} toast-icon"></i>
        <span class="toast-msg">${message}</span>
    `;

    container.appendChild(toast);

    // Remove toast after 3 seconds
    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.3s forwards';
        setTimeout(() => {
            if (container.contains(toast)) {
                container.removeChild(toast);
            }
        }, 300);
    }, 3000);
};

// Close user dropdown if clicked outside
window.addEventListener('click', function(e) {
    if (!e.target.closest('.user-profile')) {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    }
});
