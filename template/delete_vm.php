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

if (isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);

    $sql = "DELETE FROM virtual_machines WHERE id = '$id'";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('Data Virtual Machine Berhasil Dihapus!');
                window.location.href='list_vm.php';
              </script>";
    } else {
        echo "Error saat menghapus data: " . $conn->error;
    }
} else {
    header("Location: list_vm.php");
}
?>