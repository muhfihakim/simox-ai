<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$host = "localhost";
$user = "root";
$pass = ""; 
$db   = "db_inventaris_dc";

$conn = new mysqli($host, $user, $pass, $db);

// Ambil ID dari URL
if (!isset($_GET['id'])) {
    header("Location: list_vm.php");
    exit;
}
$id = $conn->real_escape_string($_GET['id']);

// Ambil data lama VM berdasarkan ID
$query_vm = "SELECT * FROM virtual_machines WHERE id = '$id'";
$result_vm = $conn->query($query_vm);
if ($result_vm->num_rows == 0) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='list_vm.php';</script>";
    exit;
}
$vm = $result_vm->fetch_assoc();

// Ambil data Server Fisik untuk dropdown
$query_server = "SELECT id, nama_server FROM server_fisik ORDER BY nama_server ASC";
$result_server = $conn->query($query_server);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Data Virtual Machine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4" style="max-width: 700px;">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning text-white py-3">
                <h5 class="mb-0 fw-bold">⚙️ Edit Spesifikasi Virtual Machine</h5>
            </div>
            <div class="card-body p-4">
                <form action="proses_edit_vm.php" method="POST">
                    <input type="hidden" name="id" value="<?= $vm['id']; ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Server / Hostname</label>
                        <input type="text" class="form-control" name="hostname" value="<?= htmlspecialchars($vm['hostname']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">IP Address (Public/Private)</label>
                        <input type="text" class="form-control" name="ip_public_private" value="<?= htmlspecialchars($vm['ip_public_private']); ?>" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status VM</label>
                            <select class="form-select" name="status" required>
                                <option value="Running" <?= $vm['status'] == 'Running' ? 'selected' : ''; ?>>Running</option>
                                <option value="Stopped" <?= $vm['status'] == 'Stopped' ? 'selected' : ''; ?>>Stopped / Down</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Node Server Fisik</label>
                            <select class="form-select" name="server_fisik_id" required>
                                <option value="">-- Pilih Server Fisik --</option>
                                <?php while($server = $result_server->fetch_assoc()): ?>
                                    <option value="<?= $server['id']; ?>" <?= $vm['server_fisik_id'] == $server['id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($server['nama_server']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Alokasi CPU (Cores)</label>
                            <input type="number" class="form-control" name="allocated_cpu" value="<?= $vm['allocated_cpu']; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Alokasi RAM (GB)</label>
                            <input type="number" class="form-control" name="allocated_ram_gb" value="<?= $vm['allocated_ram_gb']; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Alokasi Disk (GB)</label>
                            <input type="number" class="form-control" name="allocated_disk_gb" value="<?= $vm['allocated_disk_gb']; ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Sistem Operasi / Distro</label>
                        <input type="text" class="form-control" name="os_distro" value="<?= htmlspecialchars($vm['os_distro']); ?>" placeholder="Contoh: Ubuntu 22.04 / Windows Server">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Fungsi / Layanan Aplikasi</label>
                        <textarea class="form-control" name="fungsi_layanan" rows="2" required><?= htmlspecialchars($vm['fungsi_layanan']); ?></textarea>
                    </div>

                    <div class="text-end">
                        <a href="list_vm.php" class="btn btn-secondary me-2">Batal</a>
                        <button type="submit" class="btn btn-warning text-white fw-bold">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>