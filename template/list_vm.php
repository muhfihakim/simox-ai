<?php
// 1. VALIDASI SESSION & LOGIN
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// 2. KONEKSI DATABASE
$host = "localhost";
$user = "root";
$pass = ""; 
$db   = "db_inventaris_dc";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// 3. AMBIL DATA SERVER FISIK UNTUK OPSI FILTER DROPDOWN
$query_server = "SELECT id, nama_server FROM server_fisik ORDER BY nama_server ASC";
$result_server = $conn->query($query_server);

// 4. QUERY UTAMA: AMBIL SEMUA DATA VM DAN JOIN DENGAN SERVER FISIK
$query_vm = "
    SELECT 
        v.*, 
        f.nama_server AS nama_node,
        f.lokasi_dc
    FROM virtual_machines v
    LEFT JOIN server_fisik f ON v.server_fisik_id = f.id
    ORDER BY v.id DESC
";
$result_vm = $conn->query($query_vm);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Keseluruhan Virtual Machine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .bg-header-list { background-color: #0b438c; color: white; padding: 15px; margin-bottom: 25px; border-radius: 4px; }
        .badge-running { background-color: #198754; color: white; }
        .badge-stopped { background-color: #dc3545; color: white; }
    </style>
</head>
<body class="bg-light">

    <div class="container-fluid px-4 mt-3">
        
        <div class="bg-header-list d-flex justify-content-between align-items-center shadow-sm">
            <div>
                <h4 class="mb-1 fw-bold">Daftar Keseluruhan Virtual Machine (VM)</h4>
                <small class="text-white-50">Diskominfo Kabupaten Subang</small>
            </div>
            <div>
                <a href="index.php" class="btn btn-light btn-sm fw-bold me-2"><i class="fa fa-chart-pie"></i> Lihat Dashboard</a>
                <a href="create.php" class="btn btn-success btn-sm fw-bold"><i class="fa fa-plus"></i> Tambah VM</a>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body bg-white rounded">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary small">Cari Hostname / IP / Fungsi</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa fa-search text-muted"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Ketik kata kunci pencarian..." onkeyup="filterTable()">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary small">Filter Berdasarkan Server Fisik (Node)</label>
                        <select id="filterNode" class="form-select" onchange="filterTable()">
                            <option value="">-- Semua Server Fisik --</option>
                            <?php while($server = $result_server->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($server['nama_server']); ?>"><?= htmlspecialchars($server['nama_server']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary small">Filter Status VM</label>
                        <select id="filterStatus" class="form-select" onchange="filterTable()">
                            <option value="">-- Semua Status --</option>
                            <option value="Running">Running</option>
                            <option value="Stopped">Stopped / Down</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0" id="vmTable">
                        <thead class="table-dark text-uppercase small" style="font-size: 0.75rem;">
                            <tr>
                                <th width="4%" class="text-center">No</th>
                                <th width="18%">Nama Server (Hostname)</th>
                                <th width="13%">IP Address</th>
                                <th width="8%" class="text-center">Status</th>
                                <th width="5%" class="text-center">CPU</th>
                                <th width="5%" class="text-center">RAM</th>
                                <th width="5%" class="text-center">DISK</th>
                                <th width="10%">OS / Distro</th>
                                <th width="12%">Fungsi / Layanan</th>
                                <th width="10%">Node Induk</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.875rem;">
                            <?php 
                            $no = 1;
                            if($result_vm->num_rows > 0):
                                while($row = $result_vm->fetch_assoc()): 
                                    $statusClass = (strtolower($row['status']) == 'running') ? 'badge-running' : 'badge-stopped';
                            ?>
                                <tr class="vm-row">
                                    <td class="text-center fw-bold text-muted"><?= $no++; ?></td>
                                    <td class="fw-bold text-primary search-field"><?= htmlspecialchars($row['hostname']); ?></td>
                                    <td class="search-field"><?= htmlspecialchars($row['ip_public_private']); ?></td>
                                    <td class="text-center status-field" data-status="<?= htmlspecialchars($row['status']); ?>">
                                        <span class="badge <?= $statusClass; ?> px-2.5 py-1.5 rounded-pill"><?= htmlspecialchars($row['status']); ?></span>
                                    </td>
                                    <td class="text-center fw-bold"><?= htmlspecialchars($row['allocated_cpu']); ?> Cores</td>
                                    <td class="text-center"><?= htmlspecialchars($row['allocated_ram_gb']); ?> GB</td>
                                    <td class="text-center"><?= htmlspecialchars($row['allocated_disk_gb']); ?> GB</td>
                                    <td><?= htmlspecialchars($row['os_distro']); ?></td>
                                    <td class="search-field text-secondary small"><?= htmlspecialchars($row['fungsi_layanan']); ?></td>
                                    <td class="node-field fw-bold" data-node="<?= htmlspecialchars($row['nama_node']); ?>">
                                        <?= $row['nama_node'] ? htmlspecialchars($row['nama_node']) : '<span class="text-muted italic small">Belum di-assign</span>'; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="edit_vm.php?id=<?= $row['id']; ?>" class="btn btn-warning text-white" title="Edit Data VM">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="delete_vm.php?id=<?= $row['id']; ?>" class="btn btn-danger" title="Hapus Data VM" onclick="return confirm('Apakah Anda yakin ingin menghapus VM [<?= htmlspecialchars($row['hostname']); ?>]?');">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                endwhile; 
                            else:
                            ?>
                                <tr>
                                    <td colspan="11" class="text-center py-4 text-muted">Belum ada data Virtual Machine di dalam database.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-2 text-muted small" id="rowCountInfo">
            Menampilkan semua data VM.
        </div>

    </div>

    <script>
    function filterTable() {
        const searchInput = document.getElementById('searchInput').value.toLowerCase();
        const selectedNode = document.getElementById('filterNode').value;
        const selectedStatus = document.getElementById('filterStatus').value;
        
        const rows = document.querySelectorAll('#vmTable .vm-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const hostnameText = row.querySelector('.search-field:nth-child(2)').textContent.toLowerCase();
            const ipText = row.querySelector('.search-field:nth-child(3)').textContent.toLowerCase();
            const fungsiText = row.querySelector('.search-field:nth-child(9)').textContent.toLowerCase();
            
            const rowStatus = row.querySelector('.status-field').getAttribute('data-status');
            const rowNode = row.querySelector('.node-field').getAttribute('data-node');

            const matchSearch = hostnameText.includes(searchInput) || 
                                ipText.includes(searchInput) || 
                                fungsiText.includes(searchInput);

            const matchNode = (selectedNode === "") || (rowNode === selectedNode);
            const matchStatus = (selectedStatus === "") || (rowStatus === selectedStatus);

            if (matchSearch && matchNode && matchStatus) {
                row.style.display = "";
                visibleCount++;
            } else {
                row.style.display = "none";
            }
        });

        const infoText = document.getElementById('rowCountInfo');
        infoText.innerHTML = `Menampilkan <strong>${visibleCount}</strong> data Virtual Machine dari total <strong>${rows.length}</strong> data.`;
    }
    </script>
    <script>
        Swal.fire({
  title: "Good job!",
  text: "You clicked the button!",
  icon: "success"
});
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
</body>
</html>