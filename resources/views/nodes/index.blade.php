<x-layouts.app>
    <!-- Dashboard Content -->
    <div class="dashboard">
        <div class="page-header">
            <div>
                <h1>Data Node Server</h1>
                <p>Inventaris server fisik yang menyusun kluster Diskominfo Subang.</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline" onclick="window.location.reload()"><i
                        class="ph ph-arrows-clockwise"></i> Sinkronisasi</button>
                <button class="btn btn-primary" id="openModalBtn"><i class="ph ph-plus"></i> Catat Node Baru</button>
            </div>
        </div>

        @if(session('success'))
            <div style="background: #10b981; color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background: #ef4444; color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Node Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Total Node</span>
                        <h3 class="stat-value">{{ $nodes->count() }}</h3>
                    </div>
                    <div class="stat-icon bg-blue"><i class="ph ph-hard-drives"></i></div>
                </div>
                <div class="stat-footer">
                    <span class="text-success">{{ $nodes->where('status', 'Online')->count() }} Online</span> &bull; 
                    <span class="text-danger">{{ $nodes->where('status', '!=', 'Online')->count() }} Offline</span>
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
                        <h3 class="stat-value">{{ $nodes->sum('kapasitas_cpu') }} Cores</h3>
                    </div>
                    <div class="stat-icon bg-purple"><i class="ph ph-cpu"></i></div>
                </div>
                <div class="stat-footer">
                    <div class="progress-bar-container">
                        <div class="progress-bar bg-purple" style="width: 100%;"></div>
                    </div>
                    <span class="text-muted mt-1 d-block">100% dialokasikan dari node</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Total RAM</span>
                        <h3 class="stat-value">{{ $nodes->sum('kapasitas_ram') }} GB</h3>
                    </div>
                    <div class="stat-icon bg-orange"><i class="ph ph-memory"></i></div>
                </div>
                <div class="stat-footer">
                    <div class="progress-bar-container">
                        <div class="progress-bar bg-orange" style="width: 100%;"></div>
                    </div>
                    <span class="text-muted mt-1 d-block">Teralokasi penuh</span>
                </div>
            </div>
        </div>

        <!-- Node Cards (Grid) -->
        <div class="content-grid"
            style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); margin-bottom: 1.5rem;">

            @foreach($nodes as $node)
            <div class="card">
                <div class="card-header flex-between">
                    <div class="flex-align-center gap-2">
                        <i class="ph-fill ph-hard-drive {{ $node->status == 'Online' ? 'text-primary' : 'text-muted' }}" style="font-size: 1.4rem;"></i>
                        <div>
                            <h3 class="card-title {{ $node->status != 'Online' ? 'text-muted' : '' }}">
                                {{ $node->nama_server }} 
                                @if($node->is_master)
                                <span class="badge bg-success" style="font-size:0.6rem; margin-left:4px;">Master</span>
                                @endif
                            </h3>
                        </div>
                    </div>
                    @if($node->status == 'Online')
                        <span class="status-badge success"><span class="dot"></span>Online</span>
                    @else
                        <span class="status-badge danger"><span class="dot"></span>{{ $node->status }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="text-xs text-muted mb-4">Uptime Tercatat: {{ $node->uptime ?? '-' }}</div>

                    <div class="resource-bar mb-2">
                        <div class="flex-between text-xs mb-1">
                            <span>Kapasitas CPU ({{ $node->kapasitas_cpu ?? 0 }} Cores)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar {{ $node->status == 'Online' ? 'bg-blue' : '' }}" style="width: 100%; {{ $node->status != 'Online' ? 'background: #cbd5e1;' : '' }}"></div>
                        </div>
                    </div>

                    <div class="resource-bar mb-2">
                        <div class="flex-between text-xs mb-1">
                            <span>Kapasitas RAM ({{ $node->kapasitas_ram ?? 0 }} GB)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar {{ $node->status == 'Online' ? 'bg-purple' : '' }}" style="width: 100%; {{ $node->status != 'Online' ? 'background: #cbd5e1;' : '' }}"></div>
                        </div>
                    </div>

                    <div class="resource-bar">
                        <div class="flex-between text-xs mb-1">
                            <span>Storage Fisik ({{ $node->storage_fisik ?? 0 }})</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar {{ $node->status == 'Online' ? 'bg-green' : '' }}" style="width: 100%; {{ $node->status != 'Online' ? 'background: #cbd5e1;' : '' }}"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer flex-between gap-2">
                    <button class="btn btn-sm btn-outline text-primary flex-grow-1" style="justify-content: center;"><i class="ph ph-info"></i> Detail Spesifikasi</button>
                    <button class="icon-btn-sm text-warning" title="Edit Data" onclick="openEditModal({{ json_encode($node) }})"><i class="ph ph-pencil-simple"></i></button>
                    <form action="{{ route('nodes.destroy', $node->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus node ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="icon-btn-sm text-danger" title="Hapus Data"><i class="ph ph-trash"></i></button>
                    </form>
                </div>
            </div>
            @endforeach

        </div>

        <!-- Cluster Data Table -->
        <div class="card">
            <div class="card-header flex-between flex-wrap gap-2">
                <h3 class="card-title">Buku Detail Node (Corosync)</h3>
                <div class="table-controls">
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
                            @foreach($nodes as $node)
                            <tr class="{{ $node->status != 'Online' ? 'row-disabled' : '' }}">
                                <td><strong>{{ $node->nama_server }}</strong> @if($node->is_master)<span class="text-muted text-xs">(Master)</span>@endif</td>
                                <td>Proxmox {{ $node->versi_proxmox ?? '-' }}</td>
                                <td>{{ $node->alamat_ip ?? '-' }}</td>
                                <td>{{ $node->lokasi_rak ?? '-' }}</td>
                                <td>{{ $node->tahun_pembelian ?? '-' }}</td>
                                <td class="text-right">
                                    <button class="icon-btn-sm text-primary" title="Detail Data"><i class="ph ph-info"></i></button>
                                    <button class="icon-btn-sm text-warning" title="Edit Data" onclick="openEditModal({{ json_encode($node) }})"><i class="ph ph-pencil-simple"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="pagination-info">Menampilkan total {{ $nodes->count() }} data server</div>
            </div>
        </div>
    </div>

    <!-- Modal Template for Add Node -->
    <div class="modal-overlay" id="createModal" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Catat Data Server Node Baru</h3>
                <button class="icon-btn close-modal" onclick="closeModal()"><i class="ph ph-x"></i></button>
            </div>
            <form id="nodeForm" action="{{ route('nodes.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Server (Hostname)</label>
                        <input type="text" name="nama_server" id="nama_server" class="input-form" placeholder="Contoh: pve-04" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Alamat IP Manajeman</label>
                            <input type="text" name="alamat_ip" id="alamat_ip" class="input-form" placeholder="192.168.1.13">
                        </div>
                        <div class="form-group">
                            <label>Versi Proxmox</label>
                            <input type="text" name="versi_proxmox" id="versi_proxmox" class="input-form" placeholder="8.1.3">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Total CPU Cores</label>
                            <input type="number" name="kapasitas_cpu" id="kapasitas_cpu" class="input-form" value="32">
                        </div>
                        <div class="form-group">
                            <label>Total RAM (GB)</label>
                            <input type="number" name="kapasitas_ram" id="kapasitas_ram" class="input-form" value="128">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Storage Fisik</label>
                            <input type="text" name="storage_fisik" id="storage_fisik" class="input-form" placeholder="1.5 TB">
                        </div>
                        <div class="form-group">
                            <label>Tahun Pembelian</label>
                            <input type="number" name="tahun_pembelian" id="tahun_pembelian" class="input-form" placeholder="2022">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Lokasi Rak Fisik</label>
                        <input type="text" name="lokasi_rak" id="lokasi_rak" class="input-form" placeholder="Contoh: Rak C1, Data Center lt.2">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="status" class="input-form">
                            <option value="Online">Online</option>
                            <option value="Offline">Offline</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('createModal');
        const nodeForm = document.getElementById('nodeForm');
        const modalTitle = document.getElementById('modalTitle');
        const formMethod = document.getElementById('formMethod');

        document.getElementById('openModalBtn').addEventListener('click', function() {
            modalTitle.innerText = 'Catat Data Server Node Baru';
            nodeForm.action = '{{ route('nodes.store') }}';
            formMethod.value = 'POST';
            nodeForm.reset();
            modal.style.display = 'flex';
        });

        function closeModal() {
            modal.style.display = 'none';
        }

        function openEditModal(node) {
            modalTitle.innerText = 'Edit Data Server Node';
            nodeForm.action = `/nodes/${node.id}`;
            formMethod.value = 'PUT';
            
            document.getElementById('nama_server').value = node.nama_server;
            document.getElementById('alamat_ip').value = node.alamat_ip;
            document.getElementById('versi_proxmox').value = node.versi_proxmox;
            document.getElementById('kapasitas_cpu').value = node.kapasitas_cpu;
            document.getElementById('kapasitas_ram').value = node.kapasitas_ram;
            document.getElementById('storage_fisik').value = node.storage_fisik;
            document.getElementById('tahun_pembelian').value = node.tahun_pembelian;
            document.getElementById('lokasi_rak').value = node.lokasi_rak;
            document.getElementById('status').value = node.status;

            modal.style.display = 'flex';
        }
    </script>
</x-layouts.app>
