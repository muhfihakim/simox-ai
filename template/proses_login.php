<?php
session_start();

$host = "localhost";
$user = "root";
$pass = ""; 
$db   = "db_inventaris_dc";

$conn = new mysqli($host, $user, $pass, $db);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user_data = $result->fetch_assoc();
        
        // Verifikasi password hash
        if (password_verify($password, $user_data['password'])) {
            // Set session jika berhasil login
            $_SESSION['logged_in'] = true;
            $_SESSION['username']  = $user_data['username'];
            $_SESSION['nama']      = $user_data['nama_lengkap'];
            
            header("Location: index.php");
            exit;
        }
    }
    
    // Jika gagal, kembali ke login dan beri alert
    echo "<script>
            alert('Username atau Password salah!');
            window.location.href='login.php';
          </script>";
}
?>