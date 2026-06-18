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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $conn->real_escape_string($_POST['id']);
    $hostname = $conn->real_escape_string($_POST['hostname']);
    $ip_public_private = $conn->real_escape_string($_POST['ip_public_private']);
    $status = $conn->real_escape_string($_POST['status']);
    $server_fisik_id = $conn->real_escape_string($_POST['server_fisik_id']);
    $allocated_cpu = $conn->real_escape_string($_POST['allocated_cpu']);
    $allocated_ram_gb = $conn->real_escape_string($_POST['allocated_ram_gb']);
    $allocated_disk_gb = $conn->real_escape_string($_POST['allocated_disk_gb']);
    $os_distro = $conn->real_escape_string($_POST['os_distro']);
    $fungsi_layanan = $conn->real_escape_string($_POST['fungsi_layanan']);

    $sql = "UPDATE virtual_machines SET 
                hostname = '$hostname',
                ip_public_private = '$ip_public_private',
                status = '$status',
                server_fisik_id = '$server_fisik_id',
                allocated_cpu = '$allocated_cpu',
                allocated_ram_gb = '$allocated_ram_gb',
                allocated_disk_gb = '$allocated_disk_gb',
                os_distro = '$os_distro',
                fungsi_layanan = '$fungsi_layanan'
            WHERE id = '$id'";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('Data Virtual Machine Berhasil Diperbarui!');
                window.location.href='list_vm.php';
              </script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>