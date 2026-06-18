<?php
// 1. KONEKSI DATABASE
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_inventaris_dc";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// 2. TANGKAP DATA DARI FORM (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $server_fisik_id    = $_POST['server_fisik_id'];
    $hostname           = $_POST['hostname'];
    $ip_public_private  = $_POST['ip_public_private'];
    $status             = $_POST['status'];
    $allocated_cpu      = $_POST['allocated_cpu'];
    $allocated_ram_gb   = $_POST['allocated_ram_gb'];
    $allocated_disk_gb  = $_POST['allocated_disk_gb'];
    $os_distro          = $_POST['os_distro'];
    $fungsi_layanan     = $_POST['fungsi_layanan'];
    
    // 3. QUERY INSERT INTO DATABASE
    $sql = "INSERT INTO virtual_machines (server_fisik_id, hostname, ip_public_private, status, allocated_cpu, allocated_ram_gb, allocated_disk_gb, os_distro, fungsi_layanan) 
            VALUES ('$server_fisik_id', '$hostname', '$ip_public_private', '$status', '$allocated_cpu', '$allocated_ram_gb', '$allocated_disk_gb', '$os_distro', '$fungsi_layanan')";

    if ($conn->query($sql) === TRUE) {
        // Jika berhasil, munculkan alert sukses dan redirect kembali ke dashboard utama (index.php)
        echo "<script>
                alert('Data Virtual Machine berhasil ditambahkan!');
                window.location.href='index.php';
              </script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>