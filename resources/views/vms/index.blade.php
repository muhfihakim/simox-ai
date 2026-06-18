<x-layouts.app>
    <!-- Dashboard Content -->
    <div class="dashboard">
        <div class="page-header">
            <div>
                <h1>Buku Inventaris Mesin Virtual</h1>
                <p>Manajemen dan pendataan alokasi Mesin Virtual kluster Diskominfo Subang.</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline" onclick="showToast('Menyinkronkan data VM...', 'info')"><i
                        class="ph ph-arrows-clockwise"></i> Sinkronisasi</button>
                <button class="btn btn-primary" id="openModalBtn"><i class="ph ph-plus"></i> Catat VM Baru</button>
            </div>
        </div>

        <!-- VM Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Total VM Tercatat</span>
                        <h3 class="stat-value">24</h3>
                    </div>
                    <div class="stat-icon bg-purple"><i class="ph ph-desktop"></i></div>
                </div>
                <div class="stat-footer">
                    <span class="text-success">18 Aktif</span> &bull; <span class="text-danger">6 Nonaktif</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">vCPU Dialokasikan</span>
                        <h3 class="stat-value">64 <span class="text-muted" style="font-size:0.9rem">Cores</span></h3>
                    </div>
                    <div class="stat-icon bg-blue"><i class="ph ph-cpu"></i></div>
                </div>
                <div class="stat-footer">
                    <span class="text-muted">Total dari seluruh instansi</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">RAM Dialokasikan</span>
                        <h3 class="stat-value">128 GB</h3>
                    </div>
                    <div class="stat-icon bg-orange"><i class="ph ph-memory"></i></div>
                </div>
                <div class="stat-footer">
                    <div class="progress-bar-container">
                        <div class="progress-bar bg-orange" style="width: 40%;"></div>
                    </div>
                    <span class="text-muted mt-1 d-block">40% dari total Kluster</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Laporan Backup Terakhir</span>
                        <h3 class="stat-value text-success">Aman</h3>
                    </div>
                    <div class="stat-icon bg-green"><i class="ph ph-file-text"></i></div>
                </div>
                <div class="stat-footer">
                    <span class="text-muted">Semua VM tercatat dibackup (H-1)</span>
                </div>
            </div>
        </div>

        <!-- Top Resource VMs (Grid) -->
        <div class="flex-between mb-2 mt-4">
            <h3 class="card-title" style="font-size: 1rem;">Sorotan VM Aktif</h3>
        </div>
        <div class="content-grid"
            style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); margin-bottom: 1.5rem;">

            <!-- VM 1 -->
            <div class="card">
                <div class="card-header flex-between">
                    <div class="flex-align-center gap-2">
                        <i class="ph-fill ph-linux-logo text-primary" style="font-size: 1.4rem;"></i>
                        <div>
                            <h3 class="card-title">Web Portal Subang <span class="text-muted text-xs">#101</span></h3>
                        </div>
                    </div>
                    <span class="status-badge success"><span class="dot"></span>Aktif</span>
                </div>
                <div class="card-body">
                    <div class="text-xs text-muted mb-4">Pengguna: <strong>Bidang E-Gov</strong> &bull; Node: pve-01
                    </div>

                    <div class="resource-bar mb-2">
                        <div class="flex-between text-xs mb-1">
                            <span>Alokasi CPU (4 Cores)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-blue" style="width: 100%;"></div>
                        </div>
                    </div>

                    <div class="resource-bar">
                        <div class="flex-between text-xs mb-1">
                            <span>Alokasi RAM (4 GB)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-purple" style="width: 100%;"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer flex-between gap-2">
                    <button class="btn btn-sm btn-outline text-primary flex-grow-1" style="justify-content: center;"><i
                            class="ph ph-info"></i> Detail Data</button>
                    <button class="icon-btn-sm text-warning" title="Edit Data"><i
                            class="ph ph-pencil-simple"></i></button>
                    <button class="icon-btn-sm text-danger" title="Hapus Data"><i class="ph ph-trash"></i></button>
                </div>
            </div>

            <!-- VM 2 -->
            <div class="card">
                <div class="card-header flex-between">
                    <div class="flex-align-center gap-2">
                        <i class="ph-fill ph-windows-logo text-info" style="font-size: 1.4rem;"></i>
                        <div>
                            <h3 class="card-title">Database SIKD <span class="text-muted text-xs">#120</span></h3>
                        </div>
                    </div>
                    <span class="status-badge success"><span class="dot"></span>Aktif</span>
                </div>
                <div class="card-body">
                    <div class="text-xs text-muted mb-4">Pengguna: <strong>Keuangan</strong> &bull; Node: pve-02</div>

                    <div class="resource-bar mb-2">
                        <div class="flex-between text-xs mb-1">
                            <span>Alokasi CPU (8 Cores)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-blue" style="width: 100%;"></div>
                        </div>
                    </div>

                    <div class="resource-bar">
                        <div class="flex-between text-xs mb-1">
                            <span>Alokasi RAM (16 GB)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-purple" style="width: 100%;"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer flex-between gap-2">
                    <button class="btn btn-sm btn-outline text-primary flex-grow-1"
                        style="justify-content: center;"><i class="ph ph-info"></i> Detail Data</button>
                    <button class="icon-btn-sm text-warning" title="Edit Data"><i
                            class="ph ph-pencil-simple"></i></button>
                    <button class="icon-btn-sm text-danger" title="Hapus Data"><i class="ph ph-trash"></i></button>
                </div>
            </div>

            <!-- VM 3 -->
            <div class="card">
                <div class="card-header flex-between">
                    <div class="flex-align-center gap-2">
                        <i class="ph-fill ph-linux-logo text-purple" style="font-size: 1.4rem;"></i>
                        <div>
                            <h3 class="card-title">Server Kepegawaian <span class="text-muted text-xs">#112</span>
                            </h3>
                        </div>
                    </div>
                    <span class="status-badge success"><span class="dot"></span>Aktif</span>
                </div>
                <div class="card-body">
                    <div class="text-xs text-muted mb-4">Pengguna: <strong>BKPSDM</strong> &bull; Node: pve-03</div>

                    <div class="resource-bar mb-2">
                        <div class="flex-between text-xs mb-1">
                            <span>Alokasi CPU (4 Cores)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-blue" style="width: 100%;"></div>
                        </div>
                    </div>

                    <div class="resource-bar">
                        <div class="flex-between text-xs mb-1">
                            <span>Alokasi RAM (8 GB)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-purple" style="width: 100%;"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer flex-between gap-2">
                    <button class="btn btn-sm btn-outline text-primary flex-grow-1"
                        style="justify-content: center;"><i class="ph ph-info"></i> Detail Data</button>
                    <button class="icon-btn-sm text-warning" title="Edit Data"><i
                            class="ph ph-pencil-simple"></i></button>
                    <button class="icon-btn-sm text-danger" title="Hapus Data"><i class="ph ph-trash"></i></button>
                </div>
            </div>
        </div>

        <!-- VM List Table -->
        <div class="card">
            <div class="card-header flex-between flex-wrap gap-2">
                <h3 class="card-title">Buku Registrasi Semua Mesin Virtual</h3>
                <div class="table-controls">
                    <div class="filter-group">
                        <select class="select-sm">
                            <option value="">Semua Instansi</option>
                            <option value="e-gov">Bidang E-Gov</option>
                            <option value="statistik">Bidang Statistik</option>
                        </select>
                        <select class="select-sm">
                            <option value="">Status</option>
                            <option value="running">Aktif</option>
                            <option value="stopped">Nonaktif</option>
                        </select>
                    </div>
                    <div class="search-box-sm">
                        <i class="ph ph-magnifying-glass"></i>
                        <input type="text" placeholder="Cari Aset VM / Dinas...">
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table dense-table">
                        <thead>
                            <tr>
                                <th width="40"><input type="checkbox" class="checkbox"></th>
                                <th>ID</th>
                                <th>Nama Aset / VM</th>
                                <th>Instansi Pengguna</th>
                                <th>Status</th>
                                <th>Alokasi CPU</th>
                                <th>Alokasi RAM</th>
                                <th>IP Address Tercatat</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="checkbox" class="checkbox"></td>
                                <td><strong>101</strong></td>
                                <td>
                                    <div class="flex-align-center gap-2"><i
                                            class="ph-fill ph-linux-logo text-muted"></i>
                                        Web Portal Subang</div>
                                </td>
                                <td><span class="badge bg-purple-light text-purple">E-Gov</span></td>
                                <td><span class="status-badge success"><span class="dot"></span>Aktif</span></td>
                                <td>4 Cores</td>
                                <td>4.2 GB</td>
                                <td>192.168.1.101</td>
                                <td class="text-right">
                                    <button class="icon-btn-sm text-primary" title="Detail Data"><i
                                            class="ph ph-info"></i></button>
                                    <button class="icon-btn-sm text-warning" title="Edit Data"><i
                                            class="ph ph-pencil-simple"></i></button>
                                    <button class="icon-btn-sm text-danger" title="Hapus Data"><i
                                            class="ph ph-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="checkbox"></td>
                                <td><strong>112</strong></td>
                                <td>
                                    <div class="flex-align-center gap-2"><i
                                            class="ph-fill ph-linux-logo text-muted"></i>
                                        Server Kepegawaian</div>
                                </td>
                                <td><span class="badge bg-purple-light text-purple">BKPSDM</span></td>
                                <td><span class="status-badge success"><span class="dot"></span>Aktif</span></td>
                                <td>4 Cores</td>
                                <td>8.0 GB</td>
                                <td>192.168.1.112</td>
                                <td class="text-right">
                                    <button class="icon-btn-sm text-primary" title="Detail Data"><i
                                            class="ph ph-info"></i></button>
                                    <button class="icon-btn-sm text-warning" title="Edit Data"><i
                                            class="ph ph-pencil-simple"></i></button>
                                    <button class="icon-btn-sm text-danger" title="Hapus Data"><i
                                            class="ph ph-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="checkbox"></td>
                                <td><strong>120</strong></td>
                                <td>
                                    <div class="flex-align-center gap-2"><i
                                            class="ph-fill ph-windows-logo text-info"></i>
                                        Database SIKD</div>
                                </td>
                                <td><span class="badge bg-orange-light text-orange">Keuangan</span></td>
                                <td><span class="status-badge success"><span class="dot"></span>Aktif</span></td>
                                <td>8 Cores</td>
                                <td>16.0 GB</td>
                                <td>192.168.1.120</td>
                                <td class="text-right">
                                    <button class="icon-btn-sm text-primary" title="Detail Data"><i
                                            class="ph ph-info"></i></button>
                                    <button class="icon-btn-sm text-warning" title="Edit Data"><i
                                            class="ph ph-pencil-simple"></i></button>
                                    <button class="icon-btn-sm text-danger" title="Hapus Data"><i
                                            class="ph ph-trash"></i></button>
                                </td>
                            </tr>
                            <tr class="row-disabled">
                                <td><input type="checkbox" class="checkbox"></td>
                                <td><strong>210</strong></td>
                                <td>
                                    <div class="flex-align-center gap-2"><i
                                            class="ph-fill ph-linux-logo text-muted"></i>
                                        Web Testing Dev</div>
                                </td>
                                <td><span class="badge bg-purple-light text-purple">E-Gov</span></td>
                                <td><span class="status-badge danger"><span class="dot"></span>Nonaktif</span></td>
                                <td>2 Cores</td>
                                <td>2.0 GB</td>
                                <td>-</td>
                                <td class="text-right">
                                    <button class="icon-btn-sm text-primary" title="Detail Data"><i
                                            class="ph ph-info"></i></button>
                                    <button class="icon-btn-sm text-warning" title="Edit Data"><i
                                            class="ph ph-pencil-simple"></i></button>
                                    <button class="icon-btn-sm text-danger" title="Hapus Data"><i
                                            class="ph ph-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="pagination-info">Menampilkan 1-4 dari 24 data aset</div>
                <div class="pagination">
                    <button class="page-btn disabled"><i class="ph ph-caret-left"></i></button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <span class="page-dots">...</span>
                    <button class="page-btn">6</button>
                    <button class="page-btn"><i class="ph ph-caret-right"></i></button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Template for Record VM -->
    <div class="modal-overlay" id="createVmModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Catat Data VM / LXC</h3>
                <button class="icon-btn close-modal" id="closeModalBtn"><i class="ph ph-x"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Aset / Aplikasi</label>
                    <input type="text" class="input-form" placeholder="Contoh: Web Portal Desa">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Dinas / Bidang Pengguna</label>
                        <select class="input-form">
                            <option>Bidang E-Gov</option>
                            <option>Bidang Statistik</option>
                            <option>BKPSDM</option>
                            <option>Lainnya...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>ID Tercatat</label>
                        <input type="number" class="input-form" value="113">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Alokasi Cores (CPU)</label>
                        <input type="number" class="input-form" value="2">
                    </div>
                    <div class="form-group">
                        <label>Alokasi Memory (MB)</label>
                        <input type="number" class="input-form" value="2048">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Node Penempatan</label>
                        <select class="input-form">
                            <option>pve-01</option>
                            <option>pve-02</option>
                            <option>pve-03</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>IP Address Tercatat</label>
                        <input type="text" class="input-form" placeholder="Contoh: 192.168.1.50">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelModalBtn">Batal</button>
                <button class="btn btn-primary" id="saveModalBtn">Simpan Data</button>
            </div>
        </div>
    </div>


</x-layouts.app>
