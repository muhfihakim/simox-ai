<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
// Ambil data server fisik untuk opsi dropdown relasi server fisik (Node)
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_inventaris_dc";

$conn = new mysqli($host, $user, $pass, $db);
$query_server = "SELECT id, nama_server FROM server_fisik";
$result_server = $conn->query($query_server);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Data Virtual Machine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 800px;">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Form Input Data Virtual Machine (VM)</h4>
        </div>
        <div class="card-body">
            <form action="simpan.php" method="POST">
                
                <div class="mb-3">
                    <label for="server_fisik_id" class="form-label fw-bold">Ditempatkan pada Server Fisik (Node)</label>
                    <select class="form-select" id="server_fisik_id" name="server_fisik_id" required>
                        <option value="">-- Pilih Server Fisik --</option>
                        <?php while($server = $result_server->fetch_assoc()): ?>
                            <option value="<?= $server['id']; ?>"><?= $server['nama_server']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="hostname" class="form-label fw-bold">Nama Server (Hostname)</label>
                        <input type="text" class="form-content form-control" id="hostname" name="hostname" placeholder="Contoh: VPS-SISINGAAN.APIKS-18.46" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="ip_public_private" class="form-label fw-bold">IP Address (Public/Private)</label>
                        <input type="text" class="form-control" id="ip_public_private" name="ip_public_private" placeholder="Contoh: 103.156.88.21/192.168.18.46" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="status" class="form-label fw-bold">Status</label>
                        <select class="form-select text-white bg-dark" id="status" name="status" onchange="changeStatusColor(this)">
                            <option value="Running" class="bg-success text-white" selected>Running</option>
                            <option value="Stopped" class="bg-danger text-white">Stopped</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="allocated_cpu" class="form-label fw-bold">CPU (Cores)</label>
                        <input type="number" class="form-control" id="allocated_cpu" name="allocated_cpu" placeholder="Contoh: 4" min="1" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="allocated_ram_gb" class="form-label fw-bold">RAM (GB)</label>
                        <input type="number" class="form-control" id="allocated_ram_gb" name="allocated_ram_gb" placeholder="Contoh: 8" min="1" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="allocated_disk_gb" class="form-label fw-bold">DISK / DIS (GB)</label>
                        <input type="number" class="form-control" id="allocated_disk_gb" name="allocated_disk_gb" placeholder="Contoh: 256" min="1" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="os_distro" class="form-label fw-bold">OS / Distro</label>
                        <input type="text" class="form-control" id="os_distro" name="os_distro" placeholder="Contoh: Ubuntu 20.04" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="fungsi_layanan" class="form-label fw-bold">Fungsi / Layanan</label>
                        <input type="text" class="form-control" id="fungsi_layanan" name="fungsi_layanan" placeholder="Contoh: BP4D-APIKS" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="vlan" class="form-label fw-bold">VLAN</label>
                    <input type="text" class="form-control" id="vlan" name="vlan" placeholder="Contoh: 3022 atau diisi '-' jika tidak ada">
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="index.php" class="btn btn-secondary">Kembali ke Dashboard</a>
                    <button type="submit" class="btn btn-primary">Simpan Data VM</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Animasi kecil untuk mengubah warna dropdown sesuai status (seperti warna badge di gambar)
function changeStatusColor(element) {
    if(element.value === 'Running') {
        element.className = 'form-select text-white bg-success';
    } else {
        element.className = 'form-select text-white bg-danger';
    }
}
// Set awal saat load halaman
document.getElementById('status').className = 'form-select text-white bg-success';
</script>
</body>
</html>