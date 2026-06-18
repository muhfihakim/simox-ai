<!-- Sidebar -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <i class="ph-fill ph-hard-drives logo-icon"></i>
        <h2>SIMOX</h2>
        <button class="toggle-btn mobile-close-btn" id="closeSidebarMobile"><i class="ph ph-x"></i></button>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-label">UTAMA</span>
        <a href="{{ route('dashboard.index') }}" class="nav-item {{ request()->routeIs('dashboard.*') ? 'active' : '' }}"><i class="ph ph-squares-four"></i>
            <span>Dasbor</span></a>
        <a href="{{ route('nodes.index') }}" class="nav-item {{ request()->routeIs('nodes.*') ? 'active' : '' }}"><i class="ph ph-hard-drive"></i> <span>Data
                Node</span></a>
        <a href="{{ route('vms.index') }}" class="nav-item {{ request()->routeIs('vms.*') ? 'active' : '' }}"><i class="ph ph-desktop"></i> <span>Inventaris VM</span></a>
        <a href="{{ route('lxc.index') }}" class="nav-item {{ request()->routeIs('lxc.*') ? 'active' : '' }}"><i class="ph ph-box-arrow-down"></i> <span>Inventaris
                LXC</span></a>
        <a href="#" class="nav-item"><i class="ph ph-database"></i> <span>Penyimpanan</span></a>
        <a href="#" class="nav-item"><i class="ph ph-network"></i> <span>Jaringan IP</span></a>
        <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="ph ph-chart-pie-slice"></i> <span>Laporan
                Pemakaian</span></a>

        <span class="nav-label mt-4">INTEGRASI</span>
        <a href="#" class="nav-item ai-item"><i class="ph ph-robot"></i> <span>AI Agent</span><span
                class="badge ai-badge">Pro</span></a>
        <a href="#" class="nav-item"><i class="ph ph-arrows-clockwise"></i> <span>Sinkronisasi
                PVE</span></a>

        <span class="nav-label mt-4">SISTEM</span>
        <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="ph ph-users"></i> <span>Pengguna Admin</span></a>
        <a href="#" class="nav-item"><i class="ph ph-gear"></i> <span>Pengaturan</span></a>
    </nav>

    <div class="sidebar-footer">
        <div class="api-status connected">
            <span class="status-dot"></span> Status Sinkronisasi: Aktif
        </div>
    </div>
</aside>
