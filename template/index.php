<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
// 1. KONEKSI DATABASE
$host = "localhost";
$user = "root";
$pass = ""; 
$db   = "db_inventaris_dc";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// 2. QUERY PENYESUAIAN: MENGGUNAKAN INNER JOIN AGAR SERVER TANPA VM DIKECUALIKAN
$query = "
    SELECT 
        f.nama_server,
        f.total_cpu_cores,
        SUM(v.allocated_cpu) AS cpu_terpakai,
        (f.total_cpu_cores - SUM(v.allocated_cpu)) AS cpu_sisa,
        
        f.total_ram_gb,
        SUM(v.allocated_ram_gb) AS ram_terpakai,
        (f.total_ram_gb - SUM(v.allocated_ram_gb)) AS ram_sisa,
        
        f.total_disk_gb,
        SUM(v.allocated_disk_gb) AS disk_terpakai,
        (f.total_disk_gb - SUM(v.allocated_disk_gb)) AS disk_sisa
    FROM server_fisik f
    INNER JOIN virtual_machines v ON f.id = v.server_fisik_id
    GROUP BY f.id
    ORDER BY f.nama_server ASC
";

$result = $conn->query($query);

// Variabel array untuk data grafik Chart.js
$labels = [];
$cpu_percentage = [];
$ram_percentage = [];
$disk_percentage = [];

// Variabel tooltip kustom untuk memunculkan detail angka asli saat hover
$cpu_details = [];
$ram_details = [];
$disk_details = [];

// Variabel akumulator untuk SUMMARY TOTAL keseluruhan (Hanya dari server yang aktif memiliki VM)
$total_all_cpu = 0; $total_used_cpu = 0;
$total_all_ram = 0; $total_used_ram = 0;
$total_all_disk = 0; $total_used_disk = 0;

while($row = $result->fetch_assoc()) {
    $labels[] = $row['nama_server'];
    
    // Hitung Persentase Penggunaan (Bisa Lebih dari 100%)
    $pct_cpu  = ($row['total_cpu_cores'] > 0) ? round(($row['cpu_terpakai'] / $row['total_cpu_cores']) * 100, 1) : 0;
    $pct_ram  = ($row['total_ram_gb'] > 0) ? round(($row['ram_terpakai'] / $row['total_ram_gb']) * 100, 1) : 0;
    $pct_disk = ($row['total_disk_gb'] > 0) ? round(($row['disk_terpakai'] / $row['total_disk_gb']) * 100, 1) : 0;
    
    $cpu_percentage[]  = $pct_cpu;
    $ram_percentage[]  = $pct_ram;
    $disk_percentage[] = $pct_disk;
    
    // Simpan teks detail teks asli perangkat
    $cpu_details[]  = "Terpakai: " . $row['cpu_terpakai'] . " / " . $row['total_cpu_cores'] . " Cores";
    $ram_details[]  = "Terpakai: " . $row['ram_terpakai'] . " / " . $row['total_ram_gb'] . " GB";
    $disk_details[] = "Terpakai: " . number_format($row['disk_terpakai'], 0, ',', '.') . " / " . number_format($row['total_disk_gb'], 0, ',', '.') . " GB";

    // Hitung Akumulasi Total Global (Hanya menjumlahkan server fisik yang lolos INNER JOIN)
    $total_all_cpu  += $row['total_cpu_cores'];
    $total_used_cpu += $row['cpu_terpakai'];
    
    $total_all_ram  += $row['total_ram_gb'];
    $total_used_ram += $row['ram_terpakai'];
    
    $total_all_disk += $row['total_disk_gb'];
    $total_used_disk += $row['disk_terpakai'];
}

// Sisa kapasitas global yang lebih akurat
$grand_free_cpu  = $total_all_cpu - $total_used_cpu;
$grand_free_ram  = $total_all_ram - $total_used_ram;
$grand_free_disk = $total_all_disk - $total_used_disk;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Executive Summary Infrastruktur Virtualisasi Server</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card-summary { border-left: 5px solid #22c55e; }
        .bg-header-dashboard { background-color: #0b438c; color: white; padding: 15px; margin-bottom: 25px; border-radius: 4px; }
    </style>
</head>
<body class="bg-light">

    <div class="container-fluid px-4 mt-3">
        
        <div class="bg-header-dashboard text-center shadow-sm">
            <h4 class="mb-1 fw-bold">Executive Summary Infrastruktur Virtualisasi Server – Diskominfo Subang</h4>
            <small class="text-white-50">KONDISI REAL-TIME CAPACITY PLANNING (MENGECUALIKAN NODE KOSONG)</small>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <h5 class="mb-3 text-secondary fw-bold">📊 Real Available Kapasitas Tersisa (Active Global Free Space)</h5>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 card-summary" style="border-left-color: #198754;">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small">Total Free CPU Cores (Active Nodes)</h6>
                        <h2 class="fw-bold <?= $grand_free_cpu < 0 ? 'text-danger' : 'text-success'; ?> mb-1">
                            <?= $grand_free_cpu; ?> <span class="fs-6 text-secondary">Cores Available</span>
                        </h2>
                        <small class="text-muted">Total Fisik Aktif: <?= $total_all_cpu; ?> Cores | Terpakai: <?= $total_used_cpu; ?> Cores</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 card-summary" style="border-left-color: #ffc107;">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small">Total Free RAM Capacity (Active Nodes)</h6>
                        <h2 class="fw-bold <?= $grand_free_ram < 0 ? 'text-danger' : 'text-warning'; ?> mb-1">
                            <?= $grand_free_ram; ?> <span class="fs-6 text-secondary">GB Available</span>
                        </h2>
                        <small class="text-muted">Total Fisik Aktif: <?= $total_all_ram; ?> GB | Terpakai: <?= $total_used_ram; ?> GB</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 card-summary" style="border-left-color: #0dcaf0;">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small">Total Free Disk Space (Active Nodes)</h6>
                        <h2 class="fw-bold <?= $grand_free_disk < 0 ? 'text-danger' : 'text-info'; ?> mb-1">
                            <?= number_format($grand_free_disk, 0, ',', '.'); ?> <span class="fs-6 text-secondary">GB Available</span>
                        </h2>
                        <small class="text-muted">Total Fisik Aktif: <?= number_format($total_all_disk, 0, ',', '.'); ?> GB | Terpakai: <?= number_format($total_used_disk, 0, ',', '.'); ?> GB</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="alert alert-warning py-2 shadow-sm mb-4" role="alert">
                    ℹ️ <strong>Informasi Filter:</strong> Server Fisik (Node) yang saat ini tidak menampung VM sama sekali secara otomatis disembunyikan dari grafik dan tidak dihitung ke dalam kalkulasi <em>Global Free Space</em> di atas.
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card p-3 shadow-sm border-0">
                    <span class="fw-bold text-muted small text-uppercase mb-2">Presentase Alokasi CPU (%)</span>
                    <canvas id="cpuChart"></canvas>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card p-3 shadow-sm border-0">
                    <span class="fw-bold text-muted small text-uppercase mb-2">Presentase Alokasi RAM (%)</span>
                    <canvas id="ramChart"></canvas>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card p-3 shadow-sm border-0">
                    <span class="fw-bold text-muted small text-uppercase mb-2">Presentase Alokasi Disk (%)</span>
                    <canvas id="diskChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="text-end my-3">
            <a href="list_vm.php" class="btn btn-outline-primary shadow-sm me-2">🔍 View & Search List VM</a>
            <a href="create.php" class="btn btn-primary shadow-sm">+ Tambah Data Virtual Machine Baru</a>
            <a href="logout.php" class="btn btn-primary shadow-sm"> Logout</a>
        </div>
    </div>

    <script>
        const serverLabels = <?php echo json_encode($labels); ?>;
        
        function getChartOptions(detailsArray) {
            return {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: function(value) { return value + '%'; } }
                    }
                },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let index = context.dataIndex;
                                return 'Alokasi: ' + context.parsed.y + '% (' + detailsArray[index] + ')';
                            }
                        }
                    }
                }
            };
        }

        function generateColors(dataArray) {
            return dataArray.map(value => value > 100 ? '#dc3545' : '#0d6efd');
        }

        // 1. RENDER CHART CPU (%)
        const cpuData = <?php echo json_encode($cpu_percentage); ?>;
        const ctxCpu = document.getElementById('cpuChart').getContext('2d');
        new Chart(ctxCpu, {
            type: 'bar',
            data: {
                labels: serverLabels,
                datasets: [{ label: 'Penggunaan CPU', data: cpuData, backgroundColor: generateColors(cpuData) }]
            },
            options: getChartOptions(<?php echo json_encode($cpu_details); ?>)
        });

        // 2. RENDER CHART RAM (%)
        const ramData = <?php echo json_encode($ram_percentage); ?>;
        const ctxRam = document.getElementById('ramChart').getContext('2d');
        new Chart(ctxRam, {
            type: 'bar',
            data: {
                labels: serverLabels,
                datasets: [{ label: 'Penggunaan RAM', data: ramData, backgroundColor: generateColors(ramData) }]
            },
            options: getChartOptions(<?php echo json_encode($ram_details); ?>)
        });

        // 3. RENDER CHART DISK (%)
        const diskData = <?php echo json_encode($disk_percentage); ?>;
        const ctxDisk = document.getElementById('diskChart').getContext('2d');
        new Chart(ctxDisk, {
            type: 'bar',
            data: {
                labels: serverLabels,
                datasets: [{ label: 'Penggunaan Disk/Storage', data: diskData, backgroundColor: generateColors(diskData) }]
            },
            options: getChartOptions(<?php echo json_encode($disk_details); ?>)
        });
    </script>
</body>
</html>