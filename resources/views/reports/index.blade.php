<x-layouts.app>
    <!-- Dashboard Content -->
    <div class="dashboard">
        <div class="page-header">
            <div>
                <h1>Laporan Pemakaian Resource</h1>
                <p>Visualisasi pemakaian CPU, RAM, dan Storage dari VM/LXC di seluruh Node kluster.</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline" onclick="window.print()"><i class="ph ph-printer"></i> Cetak
                    Laporan</button>
                <button class="btn btn-primary" onclick="showToast('Mengekspor laporan ke PDF...', 'success')"><i
                        class="ph ph-file-pdf"></i> Ekspor PDF</button>
            </div>
        </div>

        <!-- Stats summary -->
        <div class="stats-grid mb-4">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Total vCPU Alokasi</span>
                        <h3 class="stat-value">60 <span class="text-xs text-muted">/ 80 Cores</span></h3>
                    </div>
                    <div class="stat-icon bg-blue"><i class="ph ph-cpu"></i></div>
                </div>
                <div class="stat-footer">
                    <div class="progress-bar-container">
                        <div class="progress-bar bg-blue" style="width: 75%;"></div>
                    </div>
                    <span class="text-muted text-xs mt-1 d-block">Teralokasi 75%</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Total RAM Alokasi</span>
                        <h3 class="stat-value">195 <span class="text-xs text-muted">/ 320 GB</span></h3>
                    </div>
                    <div class="stat-icon bg-orange"><i class="ph ph-memory"></i></div>
                </div>
                <div class="stat-footer">
                    <div class="progress-bar-container">
                        <div class="progress-bar bg-orange" style="width: 61%;"></div>
                    </div>
                    <span class="text-muted text-xs mt-1 d-block">Teralokasi 61%</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Total Storage Terpakai</span>
                        <h3 class="stat-value">2.7 <span class="text-xs text-muted">/ 4.2 TB</span></h3>
                    </div>
                    <div class="stat-icon bg-green"><i class="ph ph-hard-drive"></i></div>
                </div>
                <div class="stat-footer">
                    <div class="progress-bar-container">
                        <div class="progress-bar bg-green" style="width: 65%;"></div>
                    </div>
                    <span class="text-muted text-xs mt-1 d-block">Terpakai 65%</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Total Aset Tercatat</span>
                        <h3 class="stat-value">69</h3>
                    </div>
                    <div class="stat-icon bg-purple"><i class="ph ph-squares-four"></i></div>
                </div>
                <div class="stat-footer">
                    <span class="text-muted text-xs">24 VM &bull; 45 LXC</span>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="content-grid"
            style="grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); margin-bottom: 1.5rem;">
            <!-- CPU Bar Chart -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Alokasi vCPU per Node (Bar Chart)</h3>
                </div>
                <div class="card-body">
                    <canvas id="cpuBarChart" height="250"></canvas>
                </div>
            </div>

            <!-- RAM Pie Chart -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Distribusi RAM per Bidang (Pie Chart)</h3>
                </div>
                <div class="card-body" style="display: flex; justify-content: center; align-items: center;">
                    <div style="width: 100%; max-width: 350px;">
                        <canvas id="ramPieChart" height="250"></canvas>
                    </div>
                </div>
            </div>

            <!-- Storage Bar/Doughnut Chart -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Penggunaan Storage (Doughnut Chart)</h3>
                </div>
                <div class="card-body" style="display: flex; justify-content: center; align-items: center;">
                    <div style="width: 100%; max-width: 350px;">
                        <canvas id="hddDoughnutChart" height="250"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tipe Aset Pie Chart -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Komposisi Tipe Aset</h3>
                </div>
                <div class="card-body" style="display: flex; justify-content: center; align-items: center;">
                    <div style="width: 100%; max-width: 350px;">
                        <canvas id="typePieChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabulasi Data Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tabulasi Instansi dengan Alokasi Tertinggi</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table dense-table">
                        <thead>
                            <tr>
                                <th>Peringkat</th>
                                <th>Bidang / Instansi</th>
                                <th>Total Aset</th>
                                <th>Total vCPU</th>
                                <th>Total RAM</th>
                                <th>Status Evaluasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>#1</strong></td>
                                <td>Bidang E-Gov</td>
                                <td>18</td>
                                <td>32 Cores</td>
                                <td>84 GB</td>
                                <td><span class="badge bg-warning-light text-warning">Perlu Tinjauan</span></td>
                            </tr>
                            <tr>
                                <td><strong>#2</strong></td>
                                <td>Bidang Statistik</td>
                                <td>12</td>
                                <td>16 Cores</td>
                                <td>48 GB</td>
                                <td><span class="badge bg-info-light text-info">Aman</span></td>
                            </tr>
                            <tr>
                                <td><strong>#3</strong></td>
                                <td>Keuangan</td>
                                <td>8</td>
                                <td>8 Cores</td>
                                <td>32 GB</td>
                                <td><span class="badge bg-info-light text-info">Aman</span></td>
                            </tr>
                            <tr>
                                <td><strong>#4</strong></td>
                                <td>BKPSDM</td>
                                <td>5</td>
                                <td>4 Cores</td>
                                <td>16 GB</td>
                                <td><span class="badge bg-info-light text-info">Aman</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
