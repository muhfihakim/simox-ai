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
            <h3 class="card-title" style="font-size: 1rem;">Daftar VM</h3>
            <div class="search-box-sm">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="searchInput" placeholder="Cari Hostname / IP / Fungsi..." onkeyup="filterItems()">
            </div>
        </div>
        <div class="content-grid" id="cardsContainer"
            style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); margin-bottom: 1.5rem;">

            @foreach($vms as $vm)
            <div class="card searchable-item" data-search="{{ strtolower($vm->hostname . ' ' . $vm->ip_public_private . ' ' . $vm->fungsi_layanan) }}">
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
                    <button class="btn btn-sm btn-outline text-primary flex-grow-1" style="justify-content: center;"><i
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

    <!-- Modal Template for Add/Edit VM -->
    <div class="modal-overlay" id="createModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="modal" style="background: var(--bg-card); width: 100%; max-width: 600px; border-radius: 12px; padding: 1.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div class="modal-header flex-between mb-3 border-bottom pb-2">
                <h3 class="modal-title" id="modalTitle">Catat Data Virtual Machine</h3>
                <button type="button" class="icon-btn close-modal" onclick="closeModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-color);"><i class="ph ph-x"></i></button>
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
            modal.style.display = 'flex';
        });

        function closeModal() {
            modal.style.display = 'none';
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

            modal.style.display = 'flex';
        }

        function filterItems() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let items = document.getElementsByClassName('searchable-item');

            for (let i = 0; i < items.length; i++) {
                let text = items[i].getAttribute('data-search');
                if (text.includes(input)) {
                    items[i].style.display = "";
                } else {
                    items[i].style.display = "none";
                }
            }
        }
    </script>
</x-layouts.app>
