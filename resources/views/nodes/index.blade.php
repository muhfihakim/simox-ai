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
                <button class="btn btn-primary" id="customOpenModalBtn"><i class="ph ph-plus"></i> Catat Node Baru</button>
            </div>
        </div>

        @if(session('success'))
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    showToast("{{ session('success') }}", "success");
                });
            </script>
        @endif

        @if($errors->any())
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    @foreach($errors->all() as $error)
                        showToast("{{ $error }}", "error");
                    @endforeach
                });
            </script>
        @endif

        <!-- Node Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Total Node</span>
                        <h3 class="stat-value">{{ $allNodes->count() }}</h3>
                    </div>
                    <div class="stat-icon bg-blue"><i class="ph ph-hard-drives"></i></div>
                </div>
                <div class="stat-footer">
                    <span class="text-success">{{ $allNodes->where('status', 'Online')->count() }} Online</span> &bull; 
                    <span class="text-danger">{{ $allNodes->where('status', '!=', 'Online')->count() }} Offline</span>
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
                        <h3 class="stat-value">{{ $allNodes->sum('kapasitas_cpu') }} Cores</h3>
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
                        <h3 class="stat-value">{{ $allNodes->sum('kapasitas_ram') }} GB</h3>
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
        <div class="flex-between mb-2 mt-4 flex-wrap gap-2">
            <h3 class="card-title" style="font-size: 1rem;">Daftar Node</h3>
            <div class="search-box-sm">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="searchInput" placeholder="Cari Nama / IP..." onkeyup="filterItems()">
            </div>
        </div>
        <div class="content-grid" id="cardsContainer"
            style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); margin-bottom: 1.5rem;">

            @foreach($nodes as $node)
            <div class="card searchable-item" data-search="{{ strtolower($node->nama_server . ' ' . $node->alamat_ip) }}">
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
                    <button class="btn btn-sm btn-outline text-primary flex-grow-1" style="justify-content: center;"><i class="ph ph-info"></i> Detail</button>
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
        <div class="card mb-4">
            <div class="card-header flex-between flex-wrap gap-2">
                <h3 class="card-title">Buku Detail Node</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table dense-table">
                        <thead>
                            <tr>
                                <th>Nama Server</th>
                                <th>Versi Proxmox</th>
                                <th>IP Manajeman</th>
                                <th>CPU (Cores)</th>
                                <th>RAM (GB)</th>
                                <th>Storage Fisik</th>
                                <th>Lokasi Rak</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($nodes as $node)
                            <tr class="searchable-table-item" data-search="{{ strtolower($node->nama_server . ' ' . $node->alamat_ip) }}">
                                <td><strong>{{ $node->nama_server }}</strong></td>
                                <td>{{ $node->versi_proxmox }}</td>
                                <td>{{ $node->alamat_ip }}</td>
                                <td>{{ $node->kapasitas_cpu }} Cores</td>
                                <td>{{ $node->kapasitas_ram }} GB</td>
                                <td>{{ $node->storage_fisik }}</td>
                                <td>{{ $node->lokasi_rak }}</td>
                                <td>
                                    @if($node->status == 'Online')
                                        <span class="status-badge success"><span class="dot"></span>Online</span>
                                    @else
                                        <span class="status-badge danger"><span class="dot"></span>{{ $node->status }}</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <button class="icon-btn-sm text-warning" title="Edit Data" onclick="openEditModal({{ json_encode($node) }})"><i class="ph ph-pencil-simple"></i></button>
                                    <form action="{{ route('nodes.destroy', $node->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus node ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-btn-sm text-danger" title="Hapus Data"><i class="ph ph-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            @if($nodes->isEmpty())
                            <tr>
                                <td colspan="9" class="text-center text-muted" style="padding: 2rem;">Belum ada data server node.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($nodes->hasPages())
        <div class="card-footer flex-between" style="background: transparent; border: none; padding: 0;">
            <div class="pagination-info text-muted">Menampilkan {{ $nodes->firstItem() }}-{{ $nodes->lastItem() }} dari {{ $nodes->total() }} data</div>
            <div class="pagination flex-align-center gap-1">
                @if ($nodes->onFirstPage())
                    <button class="page-btn disabled" disabled><i class="ph ph-caret-left"></i></button>
                @else
                    <a href="{{ $nodes->previousPageUrl() }}" class="page-btn"><i class="ph ph-caret-left"></i></a>
                @endif
                
                @foreach ($nodes->getUrlRange(1, $nodes->lastPage()) as $page => $url)
                    @if ($page == $nodes->currentPage())
                        <button class="page-btn active">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                    @endif
                @endforeach

                @if ($nodes->hasMorePages())
                    <a href="{{ $nodes->nextPageUrl() }}" class="page-btn"><i class="ph ph-caret-right"></i></a>
                @else
                    <button class="page-btn disabled" disabled><i class="ph ph-caret-right"></i></button>
                @endif
            </div>
        </div>
        @endif

    </div>

    <!-- Modal Template for Add Node -->
    <div class="modal-overlay" id="createModal">
        <div class="modal" style="max-width: 600px;">
            <div class="modal-header flex-between mb-3 border-bottom pb-2">
                <h3 class="modal-title" id="modalTitle">Catat Data Server Node Baru</h3>
                <button type="button" class="icon-btn close-modal" onclick="closeModal()"><i class="ph ph-x"></i></button>
            </div>
            <form id="nodeForm" action="{{ route('nodes.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-body">
                    <div class="form-group mb-2">
                        <label class="text-sm font-medium mb-1 d-block">Nama Server (Hostname)</label>
                        <input type="text" name="nama_server" id="nama_server" class="input-form w-100" placeholder="Contoh: pve-04" required>
                    </div>
                    <div class="form-row flex-between gap-2 mb-2">
                        <div class="form-group flex-grow-1">
                            <label class="text-sm font-medium mb-1 d-block">Alamat IP Manajeman</label>
                            <input type="text" name="alamat_ip" id="alamat_ip" class="input-form w-100" placeholder="192.168.1.13">
                        </div>
                        <div class="form-group flex-grow-1">
                            <label class="text-sm font-medium mb-1 d-block">Versi Proxmox</label>
                            <input type="text" name="versi_proxmox" id="versi_proxmox" class="input-form w-100" placeholder="8.1.3">
                        </div>
                    </div>
                    <div class="form-row flex-between gap-2 mb-2">
                        <div class="form-group flex-grow-1">
                            <label class="text-sm font-medium mb-1 d-block">Total CPU Cores</label>
                            <input type="number" name="kapasitas_cpu" id="kapasitas_cpu" class="input-form w-100" value="32">
                        </div>
                        <div class="form-group flex-grow-1">
                            <label class="text-sm font-medium mb-1 d-block">Total RAM (GB)</label>
                            <input type="number" name="kapasitas_ram" id="kapasitas_ram" class="input-form w-100" value="128">
                        </div>
                    </div>
                    <div class="form-row flex-between gap-2 mb-2">
                        <div class="form-group flex-grow-1">
                            <label class="text-sm font-medium mb-1 d-block">Storage Fisik</label>
                            <input type="text" name="storage_fisik" id="storage_fisik" class="input-form w-100" placeholder="1.5 TB">
                        </div>
                        <div class="form-group flex-grow-1">
                            <label class="text-sm font-medium mb-1 d-block">Tahun Pembelian</label>
                            <input type="number" name="tahun_pembelian" id="tahun_pembelian" class="input-form w-100" placeholder="2022">
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label class="text-sm font-medium mb-1 d-block">Lokasi Rak Fisik</label>
                        <input type="text" name="lokasi_rak" id="lokasi_rak" class="input-form w-100" placeholder="Contoh: Rak C1, Data Center lt.2">
                    </div>
                    <div class="form-group mb-3">
                        <label class="text-sm font-medium mb-1 d-block">Status</label>
                        <select name="status" id="status" class="input-form w-100">
                            <option value="Online">Online</option>
                            <option value="Offline">Offline</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-end gap-2 mt-4 pt-2 border-top">
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

        document.getElementById('customOpenModalBtn').addEventListener('click', function() {
            modalTitle.innerText = 'Catat Data Server Node Baru';
            nodeForm.action = '{{ route('nodes.store') }}';
            formMethod.value = 'POST';
            nodeForm.reset();
            modal.classList.add('active');
        });

        function closeModal() {
            modal.classList.remove('active');
        }

        function openEditModal(node) {
            modalTitle.innerText = 'Edit Data Server Node';
            nodeForm.action = `/nodes/${node.id}`;
            formMethod.value = 'PUT';
            
            document.getElementById('nama_server').value = node.nama_server || '';
            document.getElementById('alamat_ip').value = node.alamat_ip || '';
            document.getElementById('versi_proxmox').value = node.versi_proxmox || '';
            document.getElementById('kapasitas_cpu').value = node.kapasitas_cpu || '';
            document.getElementById('kapasitas_ram').value = node.kapasitas_ram || '';
            document.getElementById('storage_fisik').value = node.storage_fisik || '';
            document.getElementById('tahun_pembelian').value = node.tahun_pembelian || '';
            document.getElementById('lokasi_rak').value = node.lokasi_rak || '';
            document.getElementById('status').value = node.status || 'Online';

            modal.classList.add('active');
        }

        function filterItems() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let items = document.getElementsByClassName('searchable-item');
            let tableItems = document.getElementsByClassName('searchable-table-item');

            for (let i = 0; i < items.length; i++) {
                let text = items[i].getAttribute('data-search');
                if (text.includes(input)) {
                    items[i].style.display = "";
                } else {
                    items[i].style.display = "none";
                }
            }

            for (let i = 0; i < tableItems.length; i++) {
                let text = tableItems[i].getAttribute('data-search');
                if (text.includes(input)) {
                    tableItems[i].style.display = "";
                } else {
                    tableItems[i].style.display = "none";
                }
            }
        }
    </script>
</x-layouts.app>
