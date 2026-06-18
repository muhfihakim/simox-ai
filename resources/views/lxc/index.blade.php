<x-layouts.app>
    <!-- Dashboard Content -->
    <div class="dashboard">
        <div class="page-header">
            <div>
                <h1>Buku Inventaris LXC</h1>
                <p>Manajemen dan pendataan alokasi Linux Container kluster Diskominfo Subang.</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline" onclick="showToast('Menyinkronkan data LXC...', 'info')"><i
                        class="ph ph-arrows-clockwise"></i> Sinkronisasi</button>
                <button class="btn btn-primary" id="openModalBtn"><i class="ph ph-plus"></i> Catat LXC Baru</button>
            </div>
        </div>

        <!-- LXC Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Total LXC Tercatat</span>
                        <h3 class="stat-value">18</h3>
                    </div>
                    <div class="stat-icon bg-purple"><i class="ph ph-box-arrow-down"></i></div>
                </div>
                <div class="stat-footer">
                    <span class="text-success">15 Aktif</span> &bull; <span class="text-danger">3 Nonaktif</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">vCPU Dialokasikan</span>
                        <h3 class="stat-value">32 <span class="text-muted" style="font-size:0.9rem">Cores</span></h3>
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
                        <h3 class="stat-value">64 GB</h3>
                    </div>
                    <div class="stat-icon bg-orange"><i class="ph ph-memory"></i></div>
                </div>
                <div class="stat-footer">
                    <div class="progress-bar-container">
                        <div class="progress-bar bg-orange" style="width: 25%;"></div>
                    </div>
                    <span class="text-muted mt-1 d-block">25% dari total Kluster</span>
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
                    <span class="text-muted">Semua LXC tercatat dibackup (H-1)</span>
                </div>
            </div>
        </div>

        <!-- Top Resource LXCs (Grid) -->
        <div class="flex-between mb-2 mt-4">
            <h3 class="card-title" style="font-size: 1rem;">Sorotan LXC Aktif</h3>
        </div>
        <div class="content-grid"
            style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); margin-bottom: 1.5rem;">

            <!-- LXC 1 -->
            <div class="card">
                <div class="card-header flex-between">
                    <div class="flex-align-center gap-2">
                        <i class="ph-fill ph-cube text-primary" style="font-size: 1.4rem;"></i>
                        <div>
                            <h3 class="card-title">Layanan API <span class="text-muted text-xs">#201</span></h3>
                        </div>
                    </div>
                    <span class="status-badge success"><span class="dot"></span>Aktif</span>
                </div>
                <div class="card-body">
                    <div class="text-xs text-muted mb-4">Pengguna: <strong>Bidang E-Gov</strong> &bull; Node: pve-01
                    </div>

                    <div class="resource-bar mb-2">
                        <div class="flex-between text-xs mb-1">
                            <span>Alokasi CPU (2 Cores)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-blue" style="width: 80%;"></div>
                        </div>
                    </div>

                    <div class="resource-bar">
                        <div class="flex-between text-xs mb-1">
                            <span>Alokasi RAM (2 GB)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-purple" style="width: 60%;"></div>
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

            <!-- LXC 2 -->
            <div class="card">
                <div class="card-header flex-between">
                    <div class="flex-align-center gap-2">
                        <i class="ph-fill ph-cube text-info" style="font-size: 1.4rem;"></i>
                        <div>
                            <h3 class="card-title">DNS Resolver <span class="text-muted text-xs">#202</span></h3>
                        </div>
                    </div>
                    <span class="status-badge success"><span class="dot"></span>Aktif</span>
                </div>
                <div class="card-body">
                    <div class="text-xs text-muted mb-4">Pengguna: <strong>Infrastruktur</strong> &bull; Node: pve-02
                    </div>

                    <div class="resource-bar mb-2">
                        <div class="flex-between text-xs mb-1">
                            <span>Alokasi CPU (1 Core)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-blue" style="width: 40%;"></div>
                        </div>
                    </div>

                    <div class="resource-bar">
                        <div class="flex-between text-xs mb-1">
                            <span>Alokasi RAM (512 MB)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-purple" style="width: 30%;"></div>
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

            <!-- LXC 3 -->
            <div class="card">
                <div class="card-header flex-between">
                    <div class="flex-align-center gap-2">
                        <i class="ph-fill ph-cube text-purple" style="font-size: 1.4rem;"></i>
                        <div>
                            <h3 class="card-title">Proxy Nginx <span class="text-muted text-xs">#203</span></h3>
                        </div>
                    </div>
                    <span class="status-badge success"><span class="dot"></span>Aktif</span>
                </div>
                <div class="card-body">
                    <div class="text-xs text-muted mb-4">Pengguna: <strong>Infrastruktur</strong> &bull; Node: pve-01
                    </div>

                    <div class="resource-bar mb-2">
                        <div class="flex-between text-xs mb-1">
                            <span>Alokasi CPU (2 Cores)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-blue" style="width: 50%;"></div>
                        </div>
                    </div>

                    <div class="resource-bar">
                        <div class="flex-between text-xs mb-1">
                            <span>Alokasi RAM (1 GB)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-purple" style="width: 45%;"></div>
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

        <!-- LXC List Table -->
        <div class="card">
            <div class="card-header flex-between flex-wrap gap-2">
                <h3 class="card-title">Buku Registrasi Semua LXC</h3>
                <div class="table-controls">
                    <div class="filter-group">
                        <select class="select-sm">
                            <option value="">Semua Instansi</option>
                            <option value="e-gov">Bidang E-Gov</option>
                            <option value="infrastruktur">Infrastruktur</option>
                        </select>
                        <select class="select-sm">
                            <option value="">Status</option>
                            <option value="running">Aktif</option>
                            <option value="stopped">Nonaktif</option>
                        </select>
                    </div>
                    <div class="search-box-sm">
                        <i class="ph ph-magnifying-glass"></i>
                        <input type="text" placeholder="Cari Aset LXC / Dinas...">
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
                                <th>Nama Aset / LXC</th>
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
                                <td><strong>201</strong></td>
                                <td>
                                    <div class="flex-align-center gap-2"><i class="ph-fill ph-cube text-muted"></i>
                                        Layanan API</div>
                                </td>
                                <td><span class="badge bg-purple-light text-purple">E-Gov</span></td>
                                <td><span class="status-badge success"><span class="dot"></span>Aktif</span></td>
                                <td>2 Cores</td>
                                <td>2.0 GB</td>
                                <td>192.168.10.201</td>
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
                                <td><strong>202</strong></td>
                                <td>
                                    <div class="flex-align-center gap-2"><i class="ph-fill ph-cube text-muted"></i>
                                        DNS Resolver</div>
                                </td>
                                <td><span class="badge bg-blue-light text-blue">Infrastruktur</span></td>
                                <td><span class="status-badge success"><span class="dot"></span>Aktif</span></td>
                                <td>1 Core</td>
                                <td>512 MB</td>
                                <td>192.168.10.202</td>
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
                                <td><strong>203</strong></td>
                                <td>
                                    <div class="flex-align-center gap-2"><i class="ph-fill ph-cube text-muted"></i>
                                        Proxy Nginx</div>
                                </td>
                                <td><span class="badge bg-blue-light text-blue">Infrastruktur</span></td>
                                <td><span class="status-badge success"><span class="dot"></span>Aktif</span></td>
                                <td>2 Cores</td>
                                <td>1.0 GB</td>
                                <td>192.168.10.203</td>
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
                                <td><strong>204</strong></td>
                                <td>
                                    <div class="flex-align-center gap-2"><i class="ph-fill ph-cube text-muted"></i>
                                        Cache Redis</div>
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
                <div class="pagination-info">Menampilkan 1-4 dari 18 data aset</div>
                <div class="pagination">
                    <button class="page-btn disabled"><i class="ph ph-caret-left"></i></button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <span class="page-dots">...</span>
                    <button class="page-btn">5</button>
                    <button class="page-btn"><i class="ph ph-caret-right"></i></button>
                </div>
            </div>
        </div>
    </div>



    <!-- Modal Template for Record LXC -->
    <div class="modal-overlay" id="createVmModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Catat Data LXC Baru</h3>
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
                            <option>Infrastruktur</option>
                            <option>BKPSDM</option>
                            <option>Lainnya...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>ID Tercatat</label>
                        <input type="number" class="input-form" value="205">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Alokasi Cores (CPU)</label>
                        <input type="number" class="input-form" value="2">
                    </div>
                    <div class="form-group">
                        <label>Alokasi Memory (MB)</label>
                        <input type="number" class="input-form" value="1024">
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
