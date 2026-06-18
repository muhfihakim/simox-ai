<x-layouts.app>
    <!-- Dashboard Content -->
    <div class="dashboard">
        <div class="page-header">
            <div>
                <h1>Data Pengguna Admin</h1>
                <p>Manajemen akun admin yang dapat mengakses sistem SIMOX.</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" id="customOpenModalBtn"><i class="ph ph-user-plus"></i> Tambah Pengguna</button>
            </div>
        </div>

        @if (session('success'))
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    showToast("{{ session('success') }}", "success");
                });
            </script>
        @endif

        @if (session('error'))
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    showToast("{{ session('error') }}", "error");
                });
            </script>
        @endif

        @if ($errors->any())
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    @foreach ($errors->all() as $error)
                        showToast("{{ $error }}", "error");
                    @endforeach
                });
            </script>
        @endif

        <!-- Users Cards (Grid) -->
        <div class="flex-between mb-2 mt-4 flex-wrap gap-2">
            <div class="flex-align-center gap-2">
                <h3 class="card-title" style="font-size: 1rem; margin-right: 10px;">Daftar Pengguna</h3>
                <div class="flex-align-center" style="gap: 8px;">
                    <button class="view-toggle-btn active" id="btnGrid" onclick="toggleView('grid')"
                        title="Tampilan Grid"><i class="ph ph-squares-four"></i></button>
                    <button class="view-toggle-btn" id="btnList" onclick="toggleView('list')" title="Tampilan List"><i
                            class="ph ph-list"></i></button>
                </div>
            </div>
            <div class="search-box-sm">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="searchInput" placeholder="Cari Nama / Email..." onkeyup="filterItems()">
            </div>
        </div>
        <style>
            .view-toggle-btn {
                padding: 4px 8px;
                border-radius: 6px;
                background: transparent;
                border: 1px solid var(--border);
                color: var(--text-muted);
                cursor: pointer;
                transition: all 0.2s;
            }

            .view-toggle-btn.active {
                background: var(--primary-light);
                color: var(--primary);
                border-color: var(--primary-light);
            }

            .view-toggle-btn:hover:not(.active) {
                background: rgba(255, 255, 255, 0.05);
            }
        </style>
        <div class="content-grid" id="cardsContainer"
            style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); margin-bottom: 1.5rem;">

            @foreach ($users as $user)
                <div class="card searchable-item"
                    data-search="{{ strtolower($user->name . ' ' . $user->email) }}">
                    <div class="card-header flex-between">
                        <div class="flex-align-center gap-2">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=4f46e5&color=fff" alt="User" style="width: 40px; height: 40px; border-radius: 50%;">
                            <div>
                                <h3 class="card-title" style="margin:0;">
                                    {{ $user->name }}
                                </h3>
                                <span class="text-xs text-muted">{{ $user->email }}</span>
                            </div>
                        </div>
                        <span class="badge bg-primary" style="font-size:0.7rem;">Admin</span>
                    </div>
                    <div class="card-body">
                        <div class="text-sm text-muted mb-2">
                            <i class="ph ph-calendar-blank"></i> Bergabung sejak {{ $user->created_at->format('d M Y') }}
                        </div>
                    </div>
                    <div class="card-footer flex-between gap-2">
                        <button class="btn btn-sm btn-outline text-warning flex-grow-1" style="justify-content: center;"
                            onclick="openEditModal({{ json_encode($user) }})"><i
                                class="ph ph-pencil-simple"></i> Edit Profil</button>
                        @if(auth()->id() != $user->id)
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                            style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="icon-btn-sm text-danger" title="Hapus Pengguna"><i
                                    class="ph ph-trash"></i></button>
                        </form>
                        @else
                        <button type="button" class="icon-btn-sm text-muted" title="Ini adalah akun Anda saat ini" disabled><i
                                class="ph ph-trash"></i></button>
                        @endif
                    </div>
                </div>
            @endforeach

        </div>

        <!-- Users Data Table -->
        <div class="card mb-4" id="tableContainer" style="display: none;">
            <div class="card-header flex-between flex-wrap gap-2">
                <h3 class="card-title">Tabel Detail Pengguna</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table dense-table">
                        <thead>
                            <tr>
                                <th>Nama Lengkap</th>
                                <th>Alamat Email</th>
                                <th>Hak Akses</th>
                                <th>Tanggal Bergabung</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr class="searchable-table-item"
                                    data-search="{{ strtolower($user->name . ' ' . $user->email) }}">
                                    <td>
                                        <div class="flex-align-center gap-2">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=4f46e5&color=fff" alt="User" style="width: 28px; height: 28px; border-radius: 50%;">
                                            <strong>{{ $user->name }}</strong>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td><span class="badge bg-primary">Admin</span></td>
                                    <td>{{ $user->created_at->format('d M Y') }}</td>
                                    <td class="text-right">
                                        <button class="icon-btn-sm text-warning" title="Edit Data"
                                            onclick="openEditModal({{ json_encode($user) }})"><i
                                                class="ph ph-pencil-simple"></i></button>
                                        @if(auth()->id() != $user->id)
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-btn-sm text-danger"
                                                title="Hapus Data"><i class="ph ph-trash"></i></button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            @if ($users->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-center text-muted" style="padding: 2rem;">Belum
                                        ada data pengguna.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Template for Add/Edit User -->
    <div class="modal-overlay" id="createModal">
        <div class="modal" style="max-width: 500px;">
            <div class="modal-header flex-between mb-3 border-bottom pb-2">
                <h3 class="modal-title" id="modalTitle">Tambah Pengguna Baru</h3>
                <button type="button" class="icon-btn close-modal" onclick="closeModal()"><i
                        class="ph ph-x"></i></button>
            </div>
            <form id="userForm" action="{{ route('users.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="text-sm font-medium mb-1 d-block">Nama Lengkap</label>
                        <input type="text" name="name" id="name" class="input-form w-100"
                            placeholder="Contoh: Budi Santoso" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="text-sm font-medium mb-1 d-block">Alamat Email</label>
                        <input type="email" name="email" id="email" class="input-form w-100"
                            placeholder="budi@diskominfo.go.id" required>
                    </div>
                    <div class="form-group mb-2">
                        <label class="text-sm font-medium mb-1 d-block">Kata Sandi</label>
                        <input type="password" name="password" id="password" class="input-form w-100"
                            placeholder="Minimal 6 karakter" required>
                        <small class="text-muted" id="passwordHelp" style="display:none; margin-top: 4px;">Kosongkan jika tidak ingin mengubah kata sandi.</small>
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
        const userForm = document.getElementById('userForm');
        const modalTitle = document.getElementById('modalTitle');
        const formMethod = document.getElementById('formMethod');
        const passwordInput = document.getElementById('password');
        const passwordHelp = document.getElementById('passwordHelp');

        document.getElementById('customOpenModalBtn').addEventListener('click', function() {
            modalTitle.innerText = 'Tambah Pengguna Baru';
            userForm.action = '{{ route('users.store') }}';
            formMethod.value = 'POST';
            userForm.reset();
            passwordInput.required = true;
            passwordHelp.style.display = 'none';
            modal.classList.add('active');
        });

        function closeModal() {
            modal.classList.remove('active');
        }

        function openEditModal(user) {
            modalTitle.innerText = 'Edit Data Pengguna';
            userForm.action = `/users/${user.id}`;
            formMethod.value = 'PUT';

            document.getElementById('name').value = user.name || '';
            document.getElementById('email').value = user.email || '';
            
            passwordInput.value = '';
            passwordInput.required = false;
            passwordHelp.style.display = 'block';

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
    </script>
</x-layouts.app>
