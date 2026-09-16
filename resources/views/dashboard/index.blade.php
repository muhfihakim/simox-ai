<x-layouts.app>

    <div class="dashboard">
        <div class="page-header">
            <div>
                <h1>Ringkasan Inventaris</h1>
                <p>Pendataan dan pengelolaan infrastruktur virtualisasi Diskominfo Subang.</p>
            </div>
            <div class="header-actions">
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
                        <h3 class="stat-value">{{ $totalNodes ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon bg-blue"><i class="ph ph-hard-drive"></i></div>
                </div>
                <div class="stat-footer">
                    <span class="text-success"><i class="ph ph-check-circle"></i> {{ $onlineNodes ?? 0 }} Node Online</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Inventaris VM</span>
                        <h3 class="stat-value">{{ $totalVms ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon bg-purple"><i class="ph ph-desktop"></i></div>
                </div>
                <div class="stat-footer">
                    <span class="text-success">{{ $activeVms ?? 0 }} Aktif</span> &bull; <span class="text-danger">{{ $inactiveVms ?? 0 }} Nonaktif</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Inventaris Kontainer</span>
                        <h3 class="stat-value">{{ $totalLxc ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon bg-orange"><i class="ph ph-box-arrow-down"></i></div>
                </div>
                <div class="stat-footer">
                    <span class="text-success">{{ $activeLxc ?? 0 }} Aktif</span> &bull; <span class="text-danger">{{ $inactiveLxc ?? 0 }} Nonaktif</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Kapasitas Tercatat</span>
                        <h3 class="stat-value">{{ round(($totalCapacityGb ?? 0) / 1000, 1) }} TB</h3>
                    </div>
                    <div class="stat-icon bg-green"><i class="ph ph-database"></i></div>
                </div>
                <div class="stat-footer">
                    <div class="progress-bar-container">
                        <div class="progress-bar bg-green" style="width: {{ min($usagePercent ?? 0, 100) }}%;"></div>
                    </div>
                    <span class="text-muted mt-1 d-block">Teralokasi {{ round(($totalAllocatedDiskGb ?? 0) / 1000, 1) }} TB ({{ $usagePercent ?? 0 }}%)</span>
                </div>
            </div>
        </div>

        <!-- Charts & Table -->
        <div class="content-grid">
            <!-- Beban Alokasi Node -->
            <div class="card col-span-1">
                <div class="card-header flex-between">
                    <h3 class="card-title" style="display: flex; align-items: center; gap: 0.4rem;"><i class="ph ph-cpu"></i> Beban Alokasi Node</h3>
                    <span class="badge bg-purple-light text-purple" style="font-size: 0.72rem;">{{ $totalNodes ?? 0 }} Node</span>
                </div>
                <div class="card-body" style="padding: 0.9rem 1.1rem;">
                    <div class="node-load-list" style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 290px; overflow-y: auto; padding-right: 0.25rem;">
                        @forelse ($nodeLoads ?? [] as $node)
                            <div class="node-load-item">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem; font-size: 0.82rem;">
                                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                                        <span class="status-dot" style="width: 7px; height: 7px; border-radius: 50%; display: inline-block; background-color: {{ $node->status === 'Online' ? 'var(--success)' : 'var(--danger)' }};"></span>
                                        <a href="{{ route('nodes.show', $node->id) }}" style="font-weight: 600; color: var(--text-main); text-decoration: none;">{{ $node->nama_server }}</a>
                                        <span class="text-muted" style="font-size: 0.75rem;">({{ $node->vm_count }} VM)</span>
                                    </div>
                                    <span style="font-size: 0.78rem; font-weight: 500;" class="{{ $node->badge_class }}">
                                        {{ $node->ram_used }} / {{ $node->kapasitas_ram }} GB
                                        <span style="font-weight: 600;">({{ $node->ram_pct }}%)</span>
                                    </span>
                                </div>
                                <div class="progress-bar-container" style="height: 6px; background: rgba(0,0,0,0.06); border-radius: 999px; overflow: hidden;">
                                    <div class="progress-bar" style="width: {{ min($node->ram_pct, 100) }}%; height: 100%; {{ $node->bar_style }} border-radius: 999px;"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-xs text-center py-3">Belum ada data node terdaftar.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- AI Insights -->
            <div class="card ai-card col-span-2">
                <div class="card-header ai-header flex-between" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="card-title"><i class="ph-fill ph-sparkle"></i> Analisis AI Agent</h3>
                    <button class="btn btn-xs btn-primary" id="refreshAiInsightsBtn" title="Kirim snapshot database ke OpenClaw untuk dianalisis" style="font-size: 0.75rem; padding: 0.3rem 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                        <i class="ph ph-sparkle" id="refreshAiInsightsIcon"></i>
                        <span id="aiAnalysisStatusText">Analisis AI Sekarang</span>
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table dense-table">
                            <thead>
                                <tr>
                                    <th style="width: 140px;">Tingkat</th>
                                    <th>Insight & Rekomendasi Inventaris</th>
                                </tr>
                            </thead>
                            <tbody id="aiInsightsTableBody">
                                @forelse ($aiInsights ?? [] as $insight)
                                    @php
                                        $level = $insight['level'] ?? 'info';
                                        $badgeClass = match($level) {
                                            'danger' => 'bg-danger-light text-danger',
                                            'warning' => 'bg-warning-light text-warning',
                                            'success' => 'bg-success-light text-success',
                                            default => 'bg-info-light text-info',
                                        };
                                        $icon = match($level) {
                                            'danger' => 'ph-warning-octagon',
                                            'warning' => 'ph-warning',
                                            'success' => 'ph-check-circle',
                                            default => 'ph-info',
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge {{ $badgeClass }}">
                                                <i class="ph {{ $icon }}"></i> {{ $insight['badge'] ?? ucfirst($level) }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $insight['title'] }}</strong><br>
                                            <span class="text-muted text-xs">{{ $insight['description'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">
                                            Belum ada insight aktif. Klik tombol <strong>Analisis AI Sekarang</strong> di atas.
                                        </td>
                                    </tr>
                                @endforelse
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
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($vms ?? [] as $vm)
                                    <tr class="{{ $vm->status === 'Running' ? '' : 'row-disabled' }}">
                                        <td><input type="checkbox" class="checkbox"></td>
                                        <td><strong>{{ $vm->id }}</strong></td>
                                        <td>{{ $vm->hostname }}</td>
                                        <td><span class="badge bg-purple-light text-purple">{{ $vm->fungsi_layanan }}</span></td>
                                        <td>
                                            @if ($vm->status === 'Running')
                                                <span class="status-badge success"><span class="dot"></span>Aktif</span>
                                            @else
                                                <span class="status-badge danger"><span class="dot"></span>Nonaktif</span>
                                            @endif
                                        </td>
                                        <td>{{ $vm->serverFisik->nama_server ?? '-' }}</td>
                                        <td>{{ $vm->allocated_cpu }} Cores</td>
                                        <td>{{ $vm->allocated_ram_gb }} GB</td>
                                        <td>{{ $vm->ip_public_private ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-3">Belum ada data VM / Kontainer.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="pagination-info">Menampilkan {{ isset($vms) && $vms->count() ? $vms->firstItem() . '-' . $vms->lastItem() . ' dari ' . $vms->total() : '0' }} data aset</div>
                    <div class="pagination">
                        <a href="{{ route('vps.index') }}" class="btn btn-sm btn-outline">Lihat Semua di Buku Inventaris &rarr;</a>
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
