<x-layouts.app>
    <!-- Dashboard Content -->
    <div class="dashboard">
        <div class="page-header">
            <div>
                <h1>Buku Inventaris Mesin Virtual</h1>
                <p>Manajemen dan pendataan alokasi Mesin Virtual kluster Diskominfo Subang.</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline" onclick="window.location.reload()"><i
                        class="ph ph-arrows-clockwise"></i> Sinkronisasi</button>
                <button class="btn btn-primary" id="customOpenModalBtn"><i class="ph ph-plus"></i> Catat VM Baru</button>
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

        <!-- VM Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Total VM Tercatat</span>
                        <h3 class="stat-value">{{ $allVms->count() }}</h3>
                    </div>
                    <div class="stat-icon bg-purple"><i class="ph ph-desktop"></i></div>
                </div>
                <div class="stat-footer">
                    <span class="text-success">{{ $allVms->where('status', 'Running')->count() }} Aktif</span> &bull; 
                    <span class="text-danger">{{ $allVms->where('status', '!=', 'Running')->count() }} Nonaktif</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">vCPU Dialokasikan</span>
                        <h3 class="stat-value">{{ $allVms->sum('allocated_cpu') }} <span class="text-muted" style="font-size:0.9rem">Cores</span></h3>
                    </div>
                    <div class="stat-icon bg-blue"><i class="ph ph-cpu"></i></div>
                </div>
                <div class="stat-footer">
                    <span class="text-muted">Total dari seluruh VM</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">RAM Dialokasikan</span>
                        <h3 class="stat-value">{{ $allVms->sum('allocated_ram_gb') }} GB</h3>
                    </div>
                    <div class="stat-icon bg-orange"><i class="ph ph-memory"></i></div>
                </div>
                <div class="stat-footer">
                    <div class="progress-bar-container">
                        <div class="progress-bar bg-orange" style="width: 50%;"></div>
                    </div>
                    <span class="text-muted mt-1 d-block">Teralokasi dari Node</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <span class="stat-title">Total Disk</span>
                        <h3 class="stat-value text-success">{{ $allVms->sum('allocated_disk_gb') }} GB</h3>
                    </div>
                    <div class="stat-icon bg-green"><i class="ph ph-hard-drive"></i></div>
                </div>
                <div class="stat-footer">
                    <span class="text-muted">Kapasitas dialokasikan</span>
                </div>
            </div>
        </div>

        <!-- Top Resource VMs (Grid) -->
        <div class="flex-between mb-2 mt-4 flex-wrap gap-2">
            <div class="flex-align-center gap-2">
                <h3 class="card-title" style="font-size: 1rem; margin-right: 10px;">Daftar VM</h3>
                <div class="flex-align-center" style="gap: 8px;">
                    <button class="view-toggle-btn" id="btnGrid" onclick="toggleView('grid')" title="Tampilan Grid"><i class="ph ph-squares-four"></i></button>
                    <button class="view-toggle-btn active" id="btnList" onclick="toggleView('list')" title="Tampilan List"><i class="ph ph-list"></i></button>
                </div>
            </div>
            <div class="flex-align-center gap-2">
                <div class="search-box-sm">
                    <i class="ph ph-hard-drive"></i>
                    <select id="nodeFilter" onchange="filterItems()" style="background: transparent; border: none; color: inherit; outline: none; padding-right: 1rem; cursor: pointer;">
                        <option value="" style="background: #1e1e2f;">-- Filter Node --</option>
                        @foreach($nodes as $n)
                            <option value="{{ strtolower($n->nama_server) }}" style="background: #1e1e2f;">{{ $n->nama_server }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="search-box-sm">
                    <i class="ph ph-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Cari Hostname / IP / Fungsi..." onkeyup="filterItems()">
                </div>
            </div>
        </div>
        <style>
            .view-toggle-btn { padding: 4px 8px; border-radius: 6px; background: transparent; border: 1px solid var(--border); color: var(--text-muted); cursor: pointer; transition: all 0.2s; }
            .view-toggle-btn.active { background: var(--primary-light); color: var(--primary); border-color: var(--primary-light); }
            .view-toggle-btn:hover:not(.active) { background: rgba(255,255,255,0.05); }
        </style>
        <div class="content-grid" id="cardsContainer"
            style="display: none; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); margin-bottom: 1.5rem;">

            @foreach($vms as $vm)
            <div class="card searchable-item" data-node="{{ strtolower($vm->serverFisik->nama_server ?? '') }}" data-search="{{ strtolower($vm->hostname . ' ' . $vm->ip_public_private . ' ' . $vm->fungsi_layanan) }}">
                <div class="card-header flex-between">
                    <div class="flex-align-center gap-2">
                        @if(stripos($vm->os_distro, 'windows') !== false)
                            <i class="ph-fill ph-windows-logo text-info" style="font-size: 1.4rem;"></i>
                        @else
                            <i class="ph-fill ph-linux-logo text-primary" style="font-size: 1.4rem;"></i>
                        @endif
                        <div>
                            <h3 class="card-title">{{ $vm->hostname }} <span class="text-muted text-xs">#{{ $vm->id }}</span></h3>
                        </div>
                    </div>
                    @if($vm->status == 'Running')
                        <span class="status-badge success"><span class="dot"></span>Aktif</span>
                    @else
                        <span class="status-badge danger"><span class="dot"></span>{{ $vm->status }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="text-xs text-muted mb-4">Pengguna: <strong>{{ $vm->fungsi_layanan }}</strong> &bull; Node: {{ $vm->serverFisik->nama_server ?? '-' }}
                    </div>

                    <div class="resource-bar mb-2">
                        <div class="flex-between text-xs mb-1">
                            <span>Alokasi CPU ({{ $vm->allocated_cpu }} Cores)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-blue" style="width: 100%;"></div>
                        </div>
                    </div>

                    <div class="resource-bar">
                        <div class="flex-between text-xs mb-1">
                            <span>Alokasi RAM ({{ $vm->allocated_ram_gb }} GB)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-purple" style="width: 100%;"></div>
                        </div>
                    </div>
                    
                    <div class="resource-bar mt-2">
                        <div class="flex-between text-xs mb-1">
                            <span>Alokasi Disk ({{ $vm->allocated_disk_gb }} GB)</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar bg-green" style="width: 100%;"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer flex-between gap-2">
                    <button class="btn btn-sm btn-outline text-primary flex-grow-1" style="justify-content: center;" onclick="openDetailModal({{ json_encode($vm) }})"><i
                            class="ph ph-info"></i> Detail Data</button>
                    <button class="icon-btn-sm text-warning" title="Edit Data" onclick="openEditModal({{ json_encode($vm) }})"><i
                            class="ph ph-pencil-simple"></i></button>
                    <form action="{{ route('vms.destroy', $vm->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus VM ini?');">
                        @csrf
                        @method('DELETE')
                        <button class="icon-btn-sm text-danger" title="Hapus Data"><i class="ph ph-trash"></i></button>
                    </form>
                </div>
            </div>
            @endforeach

        </div>

        <!-- VM Data Table -->
        <div class="card mb-4" id="tableContainer" style="display: block;">
            <div class="card-header flex-between flex-wrap gap-2">
                <h3 class="card-title">Tabel Detail Virtual Machine</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table dense-table">
                        <thead>
                            <tr>
                                <th>Hostname</th>
                                <th>OS Distro</th>
                                <th>IP Address</th>
                                <th>Node Induk</th>
                                <th>CPU (Cores)</th>
                                <th>RAM (GB)</th>
                                <th>Fungsi</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vms as $vm)
                            <tr class="searchable-table-item" data-node="{{ strtolower($vm->serverFisik->nama_server ?? '') }}" data-search="{{ strtolower($vm->hostname . ' ' . $vm->ip_public_private . ' ' . $vm->fungsi_layanan) }}">
                                <td>
                                    <div class="flex-align-center gap-2">
                                        @if(stripos($vm->os_distro, 'windows') !== false)
                                            <i class="ph-fill ph-windows-logo text-info"></i>
                                        @else
                                            <i class="ph-fill ph-linux-logo text-muted"></i>
                                        @endif
                                        <strong>{{ $vm->hostname }}</strong>
                                    </div>
                                </td>
                                <td>{{ $vm->os_distro }}</td>
                                <td>{{ $vm->ip_public_private }}</td>
                                <td>{{ $vm->serverFisik->nama_server ?? '-' }}</td>
                                <td>{{ $vm->allocated_cpu }} Cores</td>
                                <td>{{ $vm->allocated_ram_gb }} GB</td>
                                <td><span class="badge bg-purple-light text-purple">{{ $vm->fungsi_layanan }}</span></td>
                                <td>
                                    @if($vm->status == 'Running')
                                        <span class="status-badge success"><span class="dot"></span>Aktif</span>
                                    @else
                                        <span class="status-badge danger"><span class="dot"></span>{{ $vm->status }}</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <button class="icon-btn-sm text-warning" title="Edit Data" onclick="openEditModal({{ json_encode($vm) }})"><i class="ph ph-pencil-simple"></i></button>
                                    <form action="{{ route('vms.destroy', $vm->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus VM ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="icon-btn-sm text-danger" title="Hapus Data"><i class="ph ph-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            @if($vms->isEmpty())
                            <tr>
                                <td colspan="9" class="text-center text-muted" style="padding: 2rem;">Belum ada data virtual machine.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($vms->hasPages())
        <div class="card-footer flex-between" style="background: transparent; border: none; padding: 0;">
            <div class="pagination-info text-muted">Menampilkan {{ $vms->firstItem() }}-{{ $vms->lastItem() }} dari {{ $vms->total() }} data</div>
            <div class="pagination flex-align-center gap-1">
                @if ($vms->onFirstPage())
                    <button class="page-btn disabled" disabled><i class="ph ph-caret-left"></i></button>
                @else
                    <a href="{{ $vms->previousPageUrl() }}" class="page-btn"><i class="ph ph-caret-left"></i></a>
                @endif
                
                @foreach ($vms->getUrlRange(1, $vms->lastPage()) as $page => $url)
                    @if ($page == $vms->currentPage())
                        <button class="page-btn active">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                    @endif
                @endforeach

                @if ($vms->hasMorePages())
                    <a href="{{ $vms->nextPageUrl() }}" class="page-btn"><i class="ph ph-caret-right"></i></a>
                @else
                    <button class="page-btn disabled" disabled><i class="ph ph-caret-right"></i></button>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Modal Template for Detail VM -->
    <div class="modal-overlay" id="detailModal">
        <div class="modal" style="max-width: 500px;">
            <div class="modal-header flex-between mb-3 border-bottom pb-2">
                <h3 class="modal-title">Detail Spesifikasi VM</h3>
                <button type="button" class="icon-btn close-modal" onclick="closeDetailModal()"><i class="ph ph-x"></i></button>
            </div>
            <div class="modal-body">
                <div class="card p-3 mb-3 bg-dark" style="border: 1px solid rgba(255,255,255,0.1);">
                    <div class="mb-4 border-bottom pb-4" style="display: flex; flex-direction: column; align-items: center; text-align: center;">
                        <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 16px; background: rgba(255,255,255,0.03); margin-bottom: 12px; border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <i class="ph-fill ph-desktop text-primary" style="font-size: 2.2rem;"></i>
                        </div>
                        <h4 id="detail_hostname" style="margin: 0; font-size: 1.4rem; font-weight: 600; letter-spacing: 0.5px;">-</h4>
                        <div style="margin-top: 10px;">
                            <span class="text-muted text-sm" id="detail_ip" style="background: rgba(255,255,255,0.05); padding: 4px 14px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.05); display: inline-flex; align-items: center; gap: 6px;"><i class="ph ph-wifi-high"></i> -</span>
                        </div>
                    </div>
                    
                    <div class="table-responsive" style="border: 1px solid rgba(255,255,255,0.05); border-radius: 8px;">
                        <table class="table dense-table" style="margin: 0; background: transparent;">
                            <tbody>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td class="text-muted" style="width: 50%; padding: 12px;">Status VM</td>
                                    <td style="padding: 12px; text-align: right;"><strong id="detail_status">-</strong></td>
                                </tr>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td class="text-muted" style="padding: 12px;">OS Distro</td>
                                    <td style="padding: 12px; text-align: right;"><strong id="detail_os_distro">-</strong></td>
                                </tr>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td class="text-muted" style="padding: 12px;">Node Induk</td>
                                    <td style="padding: 12px; text-align: right;"><strong id="detail_node">-</strong></td>
                                </tr>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td class="text-muted" style="padding: 12px;">Fungsi Layanan</td>
                                    <td style="padding: 12px; text-align: right;"><strong id="detail_fungsi">-</strong></td>
                                </tr>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td class="text-muted" style="padding: 12px;">Alokasi CPU</td>
                                    <td style="padding: 12px; text-align: right;"><strong id="detail_cpu">-</strong></td>
                                </tr>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td class="text-muted" style="padding: 12px;">Alokasi RAM</td>
                                    <td style="padding: 12px; text-align: right;"><strong id="detail_ram">-</strong></td>
                                </tr>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td class="text-muted" style="padding: 12px;">Alokasi Disk</td>
                                    <td style="padding: 12px; text-align: right;"><strong id="detail_disk">-</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted" style="padding: 12px;">VLAN</td>
                                    <td style="padding: 12px; text-align: right;"><strong id="detail_vlan">-</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer flex-end pt-2 border-top">
                <button type="button" class="btn btn-outline" onclick="closeDetailModal()">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Modal Template for Add/Edit VM -->
    <div class="modal-overlay" id="createModal">
        <div class="modal" style="max-width: 600px;">
            <div class="modal-header flex-between mb-3 border-bottom pb-2">
                <h3 class="modal-title" id="modalTitle">Catat Data Virtual Machine</h3>
                <button type="button" class="icon-btn close-modal" onclick="closeModal()"><i class="ph ph-x"></i></button>
            </div>
            <form id="vmForm" action="{{ route('vms.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                    <div class="form-group mb-2">
                        <label class="text-sm font-medium mb-1 d-block">Nama Server (Hostname)</label>
                        <input type="text" name="hostname" id="hostname" class="input-form w-100" required placeholder="Contoh: VPS-APP">
                    </div>
                    <div class="form-group mb-2">
                        <label class="text-sm font-medium mb-1 d-block">Node Induk</label>
                        <select name="server_fisik_id" id="server_fisik_id" class="input-form w-100" required>
                            <option value="">-- Pilih Node --</option>
                            @foreach($nodes as $node)
                                <option value="{{ $node->id }}">{{ $node->nama_server }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row flex-between gap-2 mb-2">
                        <div class="form-group flex-grow-1">
                            <label class="text-sm font-medium mb-1 d-block">IP Address</label>
                            <input type="text" name="ip_public_private" id="ip_public_private" class="input-form w-100" required placeholder="192.168.1.50">
                        </div>
                        <div class="form-group flex-grow-1">
                            <label class="text-sm font-medium mb-1 d-block">OS Distro</label>
                            <input type="text" name="os_distro" id="os_distro" class="input-form w-100" required placeholder="Ubuntu 22.04">
                        </div>
                    </div>
                    <div class="form-row flex-between gap-2 mb-2">
                        <div class="form-group flex-grow-1">
                            <label class="text-sm font-medium mb-1 d-block">Alokasi CPU</label>
                            <input type="number" name="allocated_cpu" id="allocated_cpu" class="input-form w-100" required value="4">
                        </div>
                        <div class="form-group flex-grow-1">
                            <label class="text-sm font-medium mb-1 d-block">Alokasi RAM (GB)</label>
                            <input type="number" name="allocated_ram_gb" id="allocated_ram_gb" class="input-form w-100" required value="8">
                        </div>
                    </div>
                    <div class="form-row flex-between gap-2 mb-2">
                        <div class="form-group flex-grow-1">
                            <label class="text-sm font-medium mb-1 d-block">Alokasi Disk (GB)</label>
                            <input type="number" name="allocated_disk_gb" id="allocated_disk_gb" class="input-form w-100" required value="100">
                        </div>
                        <div class="form-group flex-grow-1">
                            <label class="text-sm font-medium mb-1 d-block">Fungsi / Layanan</label>
                            <input type="text" name="fungsi_layanan" id="fungsi_layanan" class="input-form w-100" required placeholder="Web Server">
                        </div>
                    </div>
                    <div class="form-row flex-between gap-2 mb-2">
                        <div class="form-group flex-grow-1">
                            <label class="text-sm font-medium mb-1 d-block">VLAN</label>
                            <input type="text" name="vlan" id="vlan" class="input-form w-100" placeholder="3022">
                        </div>
                        <div class="form-group flex-grow-1">
                            <label class="text-sm font-medium mb-1 d-block">Status</label>
                            <select name="status" id="status" class="input-form w-100" required>
                                <option value="Running">Running</option>
                                <option value="Stopped">Stopped</option>
                            </select>
                        </div>
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
        const vmForm = document.getElementById('vmForm');
        const modalTitle = document.getElementById('modalTitle');
        const formMethod = document.getElementById('formMethod');

        document.getElementById('customOpenModalBtn').addEventListener('click', function() {
            modalTitle.innerText = 'Catat Data Virtual Machine Baru';
            vmForm.action = '{{ route('vms.store') }}';
            formMethod.value = 'POST';
            vmForm.reset();
            modal.classList.add('active');
        });

        function closeModal() {
            modal.classList.remove('active');
        }

        function openEditModal(vm) {
            modalTitle.innerText = 'Edit Data Virtual Machine';
            vmForm.action = `/vms/${vm.id}`;
            formMethod.value = 'PUT';
            
            document.getElementById('hostname').value = vm.hostname;
            document.getElementById('server_fisik_id').value = vm.server_fisik_id;
            document.getElementById('ip_public_private').value = vm.ip_public_private;
            document.getElementById('os_distro').value = vm.os_distro;
            document.getElementById('allocated_cpu').value = vm.allocated_cpu;
            document.getElementById('allocated_ram_gb').value = vm.allocated_ram_gb;
            document.getElementById('allocated_disk_gb').value = vm.allocated_disk_gb;
            document.getElementById('fungsi_layanan').value = vm.fungsi_layanan;
            document.getElementById('vlan').value = vm.vlan || '';
            document.getElementById('status').value = vm.status;

            modal.classList.add('active');
        }

        function filterItems() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let nodeFilter = document.getElementById('nodeFilter').value.toLowerCase();
            let items = document.getElementsByClassName('searchable-item');
            let tableItems = document.getElementsByClassName('searchable-table-item');

            for (let i = 0; i < items.length; i++) {
                let text = items[i].getAttribute('data-search');
                let node = items[i].getAttribute('data-node') || '';
                
                let matchesSearch = text.includes(input);
                let matchesNode = nodeFilter === '' || node === nodeFilter;

                if (matchesSearch && matchesNode) {
                    items[i].style.display = "";
                } else {
                    items[i].style.display = "none";
                }
            }

            for (let i = 0; i < tableItems.length; i++) {
                let text = tableItems[i].getAttribute('data-search');
                let node = tableItems[i].getAttribute('data-node') || '';
                
                let matchesSearch = text.includes(input);
                let matchesNode = nodeFilter === '' || node === nodeFilter;

                if (matchesSearch && matchesNode) {
                    tableItems[i].style.display = "";
                } else {
                    tableItems[i].style.display = "none";
                }
            }
        }

        function toggleView(view) {
            const cards = document.getElementById('cardsContainer');
            const table = document.getElementById('tableContainer');
            const btnGrid = document.getElementById('btnGrid');
            const btnList = document.getElementById('btnList');

            if (view === 'grid') {
                cards.style.display = 'grid';
                table.style.display = 'none';
                btnGrid.classList.add('active');
                btnList.classList.remove('active');
            } else {
                cards.style.display = 'none';
                table.style.display = 'block';
                btnList.classList.add('active');
                btnGrid.classList.remove('active');
            }
        }

        const detailModal = document.getElementById('detailModal');
        function openDetailModal(vm) {
            document.getElementById('detail_hostname').innerText = vm.hostname || '-';
            document.getElementById('detail_ip').innerText = vm.ip_public_private || '-';
            
            let statusEl = document.getElementById('detail_status');
            statusEl.innerText = vm.status || '-';
            statusEl.className = vm.status === 'Running' ? 'text-success' : 'text-danger';

            document.getElementById('detail_os_distro').innerText = vm.os_distro || '-';
            document.getElementById('detail_node').innerText = vm.server_fisik ? vm.server_fisik.nama_server : '-';
            document.getElementById('detail_fungsi').innerText = vm.fungsi_layanan || '-';
            
            document.getElementById('detail_cpu').innerText = (vm.allocated_cpu || 0) + ' Cores';
            document.getElementById('detail_ram').innerText = (vm.allocated_ram_gb || 0) + ' GB';
            document.getElementById('detail_disk').innerText = (vm.allocated_disk_gb || 0) + ' GB';
            document.getElementById('detail_vlan').innerText = vm.vlan || '-';

            detailModal.classList.add('active');
        }

        function closeDetailModal() {
            detailModal.classList.remove('active');
        }
    </script>
</x-layouts.app>
