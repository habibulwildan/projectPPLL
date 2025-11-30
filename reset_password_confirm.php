<?php
session_start();
require_once __DIR__ . '/config.php';

$mysqli = $conn ?? $mysqli ?? null;

if (!$mysqli) {
    die("Koneksi database gagal.");
}

if (!isset($_GET['token'])) {
    die("Token tidak ditemukan.");
}

$token = $_GET['token'];

// Ambil email dari token
$stmt = $conn->prepare("SELECT email, created_at FROM password_reset_tokens WHERE token=? LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) { die("Token tidak valid."); }
if ((time() - strtotime($row['created_at'])) > 3600) { die("Token kadaluarsa."); }

$user_email = $row['email'];
$error = $success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Cek password sama dengan konfirmasi
    if ($password !== $confirm) {
        $error = "Password tidak sama.";
    } else {
        // Update password di DB
        $hash = md5($password); // sesuai penyimpanan DB
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
        $stmt->bind_param("ss", $hash, $user_email);
        $stmt->execute();
        $stmt->close();

        // Hapus token
        $stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE token=?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->close();

        // Jangan set session baru! Hanya tampilkan pesan sukses
        $success = "Password berhasil diubah. Silakan login kembali.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ganti Password – Kopi Senja</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background: linear-gradient(135deg, #f7f1e3, #e6d5b8);
        font-family: 'Poppins', sans-serif;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .card-reset {
        width: 100%;
        max-width: 450px;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        background-color: #fffaf0;
    }

    .card-reset h2 {
        font-weight: 700;
        color: #6f4e37;
        text-align: center;
        margin-bottom: 25px;
    }

    .form-control {
        border-radius: 10px;
        border: 1px solid #d2b48c;
    }

    .btn-change {
        background-color: #8b5e3c;
        color: white;
        font-weight: 600;
        border-radius: 10px;
        transition: background 0.3s ease;
    }

    .btn-change:hover {
        background-color: #734b2a;
    }

    .alert {
        border-radius: 10px;
    }

    .back-link {
        display: block;
        text-align: center;
        margin-top: 20px;
        color: #6f4e37;
        text-decoration: none;
        font-weight: 500;
    }

    .back-link:hover {
        text-decoration: underline;
        color: #8b5e3c;
    }
</style>
</head>
<body>

<div class="card-reset">
    <h2>Ganti Password</h2>

    <?php if($error) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if($success) : ?>
        <div class="alert alert-success"><?= $success ?></div>
        <a href="login.php" class="btn btn-change w-100 mt-2">Login</a>
    <?php else: ?>
        <form method="POST">
            <div class="mb-3">
                <input type="password" class="form-control" name="password" placeholder="Password baru" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" name="confirm_password" placeholder="Konfirmasi password" required>
            </div>
            <button type="submit" class="btn btn-change w-100">Ubah Password</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
