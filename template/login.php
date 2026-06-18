<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistem Inventaris Data Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0b438c; }
        .card-login { max-width: 400px; border: none; border-radius: 8px; }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100">

<div class="container">
    <div class="card card-login mx-auto shadow-lg">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <h5 class="fw-bold text-dark">Sistem Inventaris Data Center</h5>
                <small class="text-muted text-uppercase">Diskominfo Kabupaten Subang</small>
            </div>
            
            <form action="proses_login.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label small fw-bold text-secondary">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required placeholder="Masukkan username">
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label small fw-bold text-secondary">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Masuk ke Sistem</button>
            </form>
            
        </div>
    </div>
</div>

</body>
</html>