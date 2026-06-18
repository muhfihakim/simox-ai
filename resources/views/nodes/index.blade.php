<x-layouts.app>
    <!-- Dashboard Content -->
    <div class="dashboard">
        <div class="page-header">
            <div>
                <h1>Data Node Server</h1>
                <p>Inventaris server fisik yang menyusun kluster Diskominfo Subang.</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline" onclick="showToast('Menyinkronkan data node...', 'info')"><i
                        class="ph ph-arrows-clockwise"></i> Sinkronisasi</button>
                <button class="btn btn-primary" id="openModalBtn"><i class="ph ph-plus"></i> Catat Node Baru</button>
            </div>
        </div>

        <!-- Node Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Total Node</span>
                        <h3 class="stat-value">3</h3>
                    </div>
                    <div class="stat-icon bg-blue"><i class="ph ph-hard-drives"></i></div>
                </div>
                <div class="stat-footer">
                    <span class="text-success">2 Online</span> &bull; <span class="text-danger">1 Offline</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Status Quorum</span>
                        <h3 class="stat-value text-success">OK</h3>
                    </div>
                    <div class="stat-icon bg-green"><i class="ph ph-check-circle"></i></div>
                </div>
                <div class="stat-footer">
                    <span class="text-muted">Corosync berjalan stabil</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Total CPU</span>
                        <h3 class="stat-value">80 Cores</h3>
                    </div>
                    <div class="stat-icon bg-purple"><i class="ph ph-cpu"></i></div>
                </div>
                <div class="stat-footer">
                    <div class="progress-bar-container">
                        <div class="progress-bar bg-purple" style="width: 45%;"></div>
                    </div>
                    <span class="text-muted mt-1 d-block">Rata-rata alokasi 45%</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Total RAM</span>
                        <h3 class="stat-value">320 GB</h3>
                    </div>
                    <div class="stat-icon bg-orange"><i class="ph ph-memory"></i></div>
                </div>
                <div class="stat-footer">
                    <div class="progress-bar-container">
                        <div class="progress-bar bg-orange" style="width: 61%;"></div>
                    </div>
                    <span class="text-muted mt-1 d-block">Teralokasi 195.2 GB (61%)</span>
                </div>
            </div>
        </div>

        <!-- Node Cards (Grid) -->
        <div class="content-grid"
            style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); margin-bottom: 1.5rem;">

            <!-- Node 1 -->
            <div class="card">
                <div class="card-header flex-between">
                    <div class="flex-align-center gap-2">
                        <i class="ph-fill ph-hard-drive text-primary" style="font-size: 1.4rem;"></i>
                        <div>
                            <h3 class="card-title">pve-01 <span class="badge bg-success"
                                    style="font-size:0.6rem; margin-left:4px;">Master</span></h3>
                        </div>
                    </div>
                    <span class="status-badge success"><span class="dot"></span>Online</span>
                </div>
                <div class="card-body">
                    <div class="text-xs text-muted mb-4">Uptime Tercatat: 45 hari, 12 jam, 30 mnt</div>

                    <div class="resource-bar mb-2">
                        <div class="flex-between text-xs mb-1">
                            <span>Kapasitas CPU (32 Cores)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-blue" style="width: 100%;"></div>
                        </div>
                    </div>

                    <div class="resource-bar mb-2">
                        <div class="flex-between text-xs mb-1">
                            <span>Kapasitas RAM (128 GB)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-purple" style="width: 100%;"></div>
                        </div>
                    </div>

                    <div class="resource-bar">
                        <div class="flex-between text-xs mb-1">
                            <span>Storage Fisik (1.5 TB)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-green" style="width: 100%;"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer flex-between gap-2">
                    <button class="btn btn-sm btn-outline text-primary flex-grow-1" style="justify-content: center;"><i
                            class="ph ph-info"></i> Detail Spesifikasi</button>
                    <button class="icon-btn-sm text-warning" title="Edit Data"><i
                            class="ph ph-pencil-simple"></i></button>
                    <button class="icon-btn-sm text-danger" title="Hapus Data"><i class="ph ph-trash"></i></button>
                </div>
            </div>

            <!-- Node 2 -->
            <div class="card">
                <div class="card-header flex-between">
                    <div class="flex-align-center gap-2">
                        <i class="ph-fill ph-hard-drive text-primary" style="font-size: 1.4rem;"></i>
                        <div>
                            <h3 class="card-title">pve-02</h3>
                        </div>
                    </div>
                    <span class="status-badge success"><span class="dot"></span>Online</span>
                </div>
                <div class="card-body">
                    <div class="text-xs text-muted mb-4">Uptime Tercatat: 20 hari, 5 jam, 15 mnt</div>

                    <div class="resource-bar mb-2">
                        <div class="flex-between text-xs mb-1">
                            <span>Kapasitas CPU (32 Cores)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-blue" style="width: 100%;"></div>
                        </div>
                    </div>

                    <div class="resource-bar mb-2">
                        <div class="flex-between text-xs mb-1">
                            <span>Kapasitas RAM (128 GB)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-purple" style="width: 100%;"></div>
                        </div>
                    </div>

                    <div class="resource-bar">
                        <div class="flex-between text-xs mb-1">
                            <span>Storage Fisik (1.5 TB)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-green" style="width: 100%;"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer flex-between gap-2">
                    <button class="btn btn-sm btn-outline text-primary flex-grow-1"
                        style="justify-content: center;"><i class="ph ph-info"></i> Detail Spesifikasi</button>
                    <button class="icon-btn-sm text-warning" title="Edit Data"><i
                            class="ph ph-pencil-simple"></i></button>
                    <button class="icon-btn-sm text-danger" title="Hapus Data"><i class="ph ph-trash"></i></button>
                </div>
            </div>

            <!-- Node 3 -->
            <div class="card">
                <div class="card-header flex-between">
                    <div class="flex-align-center gap-2">
                        <i class="ph-fill ph-hard-drive text-muted" style="font-size: 1.4rem;"></i>
                        <div>
                            <h3 class="card-title text-muted">pve-03 <span class="badge bg-danger text-white"
                                    style="font-size:0.6rem; margin-left:4px;">Maintenance</span></h3>
                        </div>
                    </div>
                    <span class="status-badge danger"><span class="dot"></span>Offline</span>
                </div>
                <div class="card-body">
                    <div class="text-xs text-muted mb-4">Offline tercatat sejak: Kemarin, 22:15</div>

                    <div class="resource-bar mb-2">
                        <div class="flex-between text-xs mb-1 text-muted">
                            <span>Kapasitas CPU (16 Cores)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: 100%; background: #cbd5e1;"></div>
                        </div>
                    </div>

                    <div class="resource-bar mb-2">
                        <div class="flex-between text-xs mb-1 text-muted">
                            <span>Kapasitas RAM (64 GB)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: 100%; background: #cbd5e1;"></div>
                        </div>
                    </div>

                    <div class="resource-bar">
                        <div class="flex-between text-xs mb-1 text-muted">
                            <span>Storage Fisik (1 TB)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: 100%; background: #cbd5e1;"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer flex-between gap-2">
                    <button class="btn btn-sm btn-outline text-primary flex-grow-1"
                        style="justify-content: center;"><i class="ph ph-info"></i> Detail Spesifikasi</button>
                    <button class="icon-btn-sm text-warning" title="Edit Data"><i
                            class="ph ph-pencil-simple"></i></button>
                    <button class="icon-btn-sm text-danger" title="Hapus Data"><i class="ph ph-trash"></i></button>
                </div>
            </div>
        </div>

        <!-- Cluster Data Table -->
        <div class="card">
            <div class="card-header flex-between flex-wrap gap-2">
                <h3 class="card-title">Buku Detail Node (Corosync)</h3>
                <div class="table-controls">
                    <div class="filter-group">
                        <select class="select-sm">
                            <option value="">Status Corosync</option>
                            <option value="quorum">Quorum (OK)</option>
                            <option value="offline">Offline</option>
                        </select>
                        <select class="select-sm">
                            <option value="">Versi PVE</option>
                            <option value="8.1.3">8.1.3</option>
                            <option value="8.0.4">8.0.4</option>
                        </select>
                    </div>
                    <div class="search-box-sm">
                        <i class="ph ph-magnifying-glass"></i>
                        <input type="text" placeholder="Cari Nama Node / IP...">
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table dense-table">
                        <thead>
                            <tr>
                                <th>Nama Server</th>
                                <th>Versi OS</th>
                                <th>Alamat IP</th>
                                <th>Lokasi Rak</th>
                                <th>Tahun Pembelian</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>pve-01</strong> <span class="text-muted text-xs">(Master)</span></td>
                                <td>Proxmox 8.1.3</td>
                                <td>192.168.1.10</td>
                                <td>Rak A2, Ruang Server 1</td>
                                <td>2022</td>
                                <td class="text-right">
                                    <button class="icon-btn-sm text-primary" title="Detail Data"><i
                                            class="ph ph-info"></i></button>
                                    <button class="icon-btn-sm text-warning" title="Edit Data"><i
                                            class="ph ph-pencil-simple"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>pve-02</strong></td>
                                <td>Proxmox 8.1.3</td>
                                <td>192.168.1.11</td>
                                <td>Rak A2, Ruang Server 1</td>
                                <td>2022</td>
                                <td class="text-right">
                                    <button class="icon-btn-sm text-primary" title="Detail Data"><i
                                            class="ph ph-info"></i></button>
                                    <button class="icon-btn-sm text-warning" title="Edit Data"><i
                                            class="ph ph-pencil-simple"></i></button>
                                </td>
                            </tr>
                            <tr class="row-disabled">
                                <td><strong>pve-03</strong></td>
                                <td>Proxmox 8.0.4</td>
                                <td>192.168.1.12</td>
                                <td>Rak B1, Ruang Server 2</td>
                                <td>2021</td>
                                <td class="text-right">
                                    <button class="icon-btn-sm text-primary" title="Detail Data"><i
                                            class="ph ph-info"></i></button>
                                    <button class="icon-btn-sm text-warning" title="Edit Data"><i
                                            class="ph ph-pencil-simple"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="pagination-info">Menampilkan 1-3 dari 3 data server</div>
                <div class="pagination">
                    <button class="page-btn disabled"><i class="ph ph-caret-left"></i></button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn disabled"><i class="ph ph-caret-right"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Template for Add Node -->
    <div class="modal-overlay" id="createVmModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Catat Data Server Node Baru</h3>
                <button class="icon-btn close-modal" id="closeModalBtn"><i class="ph ph-x"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Server (Hostname)</label>
                    <input type="text" class="input-form" placeholder="Contoh: pve-04">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Alamat IP Manajeman</label>
                        <input type="text" class="input-form" placeholder="192.168.1.13">
                    </div>
                    <div class="form-group">
                        <label>Versi Proxmox</label>
                        <input type="text" class="input-form" value="8.1.3">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Total CPU Cores</label>
                        <input type="number" class="input-form" value="32">
                    </div>
                    <div class="form-group">
                        <label>Total RAM (GB)</label>
                        <input type="number" class="input-form" value="128">
                    </div>
                </div>
                <div class="form-group">
                    <label>Lokasi Rak FIsik</label>
                    <input type="text" class="input-form" placeholder="Contoh: Rak C1, Data Center lt.2">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelModalBtn">Batal</button>
                <button class="btn btn-primary" id="saveModalBtn">Simpan Data</button>
            </div>
        </div>
    </div>


</x-layouts.app>
