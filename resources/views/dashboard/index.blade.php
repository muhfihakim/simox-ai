<x-layouts.app>

    <div class="dashboard">
        <div class="page-header">
            <div>
                <h1>Ringkasan Inventaris</h1>
                <p>Pendataan dan pengelolaan infrastruktur virtualisasi Diskominfo Subang.</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline" onclick="showToast('Menyinkronkan data dari server...', 'info')"><i
                        class="ph ph-arrows-clockwise"></i> Sinkronisasi</button>
                <button class="btn btn-primary" id="openModalBtn"><i class="ph ph-plus"></i> Catat
                    VM/LXC</button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Total Server (Node)</span>
                        <h3 class="stat-value">3</h3>
                    </div>
                    <div class="stat-icon bg-blue"><i class="ph ph-hard-drive"></i></div>
                </div>
                <div class="stat-footer">
                    <span class="text-success"><i class="ph ph-check-circle"></i> Data Sinkron</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Inventaris VM</span>
                        <h3 class="stat-value">24</h3>
                    </div>
                    <div class="stat-icon bg-purple"><i class="ph ph-desktop"></i></div>
                </div>
                <div class="stat-footer">
                    <span class="text-success">18 Aktif</span> &bull; <span class="text-danger">6
                        Nonaktif</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Inventaris Kontainer</span>
                        <h3 class="stat-value">45</h3>
                    </div>
                    <div class="stat-icon bg-orange"><i class="ph ph-box-arrow-down"></i></div>
                </div>
                <div class="stat-footer">
                    <span class="text-success">42 Aktif</span> &bull; <span class="text-danger">3
                        Nonaktif</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Kapasitas Tercatat</span>
                        <h3 class="stat-value">4.2 TB</h3>
                    </div>
                    <div class="stat-icon bg-green"><i class="ph ph-database"></i></div>
                </div>
                <div class="stat-footer">
                    <div class="progress-bar-container">
                        <div class="progress-bar bg-green" style="width: 65%;"></div>
                    </div>
                    <span class="text-muted mt-1 d-block">Teralokasi 2.7 TB (65%)</span>
                </div>
            </div>
        </div>

        <!-- Charts & Table -->
        <div class="content-grid">
            <!-- Resource Usage Chart -->
            <div class="card col-span-1">
                <div class="card-header flex-between">
                    <h3 class="card-title">Tren Utilisasi</h3>
                    <select class="select-sm">
                        <option>Hari Ini</option>
                        <option>Pekan Ini</option>
                    </select>
                </div>
                <div class="card-body">
                    <canvas id="resourceChart" height="200"></canvas>
                </div>
            </div>

            <!-- AI Insights -->
            <div class="card ai-card col-span-2">
                <div class="card-header ai-header">
                    <h3 class="card-title"><i class="ph-fill ph-sparkle"></i> Analisis AI Agent</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table dense-table">
                            <thead>
                                <tr>
                                    <th>Tingkat</th>
                                    <th>Insight Inventaris</th>
                                    <th class="text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-warning-light text-warning"><i class="ph ph-warning"></i>
                                            Rekomendasi</span></td>
                                    <td><strong>Alokasi Node "pve-02" Penuh</strong><br><span
                                            class="text-muted text-xs">Kapasitas tercatat >90%. Pertimbangkan
                                            pemerataan data ke pve-03.</span></td>
                                    <td class="text-right"><button class="btn btn-sm btn-outline-warning"
                                            onclick="showToast('Membuat draf laporan...', 'warning')">Buat
                                            Laporan</button></td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info-light text-info"><i class="ph ph-info"></i>
                                            Info</span></td>
                                    <td><strong>Audit VM Tidak Aktif</strong><br><span class="text-muted text-xs">3 VM
                                            milik
                                            Bidang E-Gov tercatat tidak
                                            aktif >30 hari.</span></td>
                                    <td class="text-right"><button class="btn btn-sm btn-outline"
                                            onclick="showToast('Menampilkan detail VM.', 'info')">Detail
                                            Data</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="card col-span-3">
                <div class="card-header flex-between flex-wrap gap-2">
                    <h3 class="card-title">Buku Inventaris Mesin Virtual & Kontainer</h3>
                    <div class="table-controls">
                        <div class="filter-group">
                            <select class="select-sm">
                                <option value="">Semua Instansi</option>
                                <option value="e-gov">Bidang E-Gov</option>
                                <option value="statistik">Bidang Statistik</option>
                            </select>
                            <select class="select-sm">
                                <option value="">Tipe</option>
                                <option value="vm">VM</option>
                                <option value="lxc">LXC</option>
                            </select>
                            <select class="select-sm">
                                <option value="">Status</option>
                                <option value="running">Aktif</option>
                                <option value="stopped">Nonaktif</option>
                            </select>
                        </div>
                        <div class="search-box-sm">
                            <i class="ph ph-magnifying-glass"></i>
                            <input type="text" placeholder="Cari ID / Instansi...">
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
                                    <th>Nama Aset</th>
                                    <th>Instansi / Bidang</th>
                                    <th>Status</th>
                                    <th>Node</th>
                                    <th>Alokasi CPU</th>
                                    <th>Alokasi RAM</th>
                                    <th>IP Address</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="checkbox" class="checkbox"></td>
                                    <td><strong>101</strong></td>
                                    <td>Web Server Utama</td>
                                    <td><span class="badge bg-purple-light text-purple">E-Gov</span></td>
                                    <td><span class="status-badge success"><span class="dot"></span>Aktif</span>
                                    </td>
                                    <td>pve-01</td>
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
                                    <td><strong>105</strong></td>
                                    <td>Database Statistik</td>
                                    <td><span class="badge bg-orange-light text-orange">Statistik</span></td>
                                    <td><span class="status-badge success"><span class="dot"></span>Aktif</span>
                                    </td>
                                    <td>pve-02</td>
                                    <td>8 Cores</td>
                                    <td>14.5 GB</td>
                                    <td>192.168.1.105</td>
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
                                    <td>Server Aplikasi SIKD</td>
                                    <td><span class="badge bg-purple-light text-purple">E-Gov</span></td>
                                    <td><span class="status-badge success"><span class="dot"></span>Aktif</span>
                                    </td>
                                    <td>pve-03</td>
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
                                <tr class="row-disabled">
                                    <td><input type="checkbox" class="checkbox"></td>
                                    <td><strong>201</strong></td>
                                    <td>Backup Server BKD</td>
                                    <td><span class="badge bg-orange-light text-orange">Kepegawaian</span></td>
                                    <td><span class="status-badge danger"><span class="dot"></span>Nonaktif</span>
                                    </td>
                                    <td>pve-01</td>
                                    <td>2 Cores</td>
                                    <td>4.0 GB</td>
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
                    <div class="pagination-info">Menampilkan 1-4 dari 69 data aset</div>
                    <div class="pagination">
                        <button class="page-btn disabled"><i class="ph ph-caret-left"></i></button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn">2</button>
                        <button class="page-btn">3</button>
                        <span class="page-dots">...</span>
                        <button class="page-btn">8</button>
                        <button class="page-btn"><i class="ph ph-caret-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Template -->
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
                        <label>VM ID</label>
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
                        <label>Node Host</label>
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
