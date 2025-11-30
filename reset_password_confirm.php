<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
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

if (!$row) { 
    die("Token tidak valid."); 
}
if ((time() - strtotime($row['created_at'])) > 3600) { 
    die("Token kadaluarsa."); 
}

$user_email = $row['email'];
$error = $success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Cek password sama dengan konfirmasi
    if ($password !== $confirm) {
        $error = "Password tidak sama.";
    } else {
        // Generate timestamp NOW() untuk updated_at
        $current_timestamp = date('Y-m-d H:i:s');
        $hash = md5($password); // sesuai penyimpanan DB
        
        // Update password DAN updated_at di DB
        $stmt = $conn->prepare("UPDATE users SET password=?, updated_at=? WHERE email=?");
        $stmt->bind_param("sss", $hash, $current_timestamp, $user_email);
        $stmt->execute();
        $stmt->close();

        // Hapus token
        $stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE token=?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->close();

        // Pesan sukses
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
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #f7f1e3, #e6d5b8);
                font-family: 'Poppins', sans-serif;
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px;
            }

            .card-reset {
                width: 100%;
                max-width: 450px;
                padding: 40px;
                border-radius: 20px;
                box-shadow: 0 15px 40px rgba(0,0,0,0.15);
                background-color: #fffaf0;
                border: 1px solid rgba(139, 94, 60, 0.1);
            }

            .card-reset h2 {
                font-weight: 700;
                color: #6f4e37;
                text-align: center;
                margin-bottom: 30px;
                font-size: 1.8rem;
            }

            .form-control {
                border-radius: 12px;
                border: 2px solid #d2b48c;
                padding: 12px 15px;
                font-size: 1rem;
                transition: border-color 0.3s ease;
            }

            .form-control:focus {
                border-color: #8b5e3c;
                box-shadow: 0 0 0 0.2rem rgba(139, 94, 60, 0.15);
            }

            .btn-change {
                background-color: #8b5e3c;
                color: white;
                font-weight: 600;
                border-radius: 12px;
                padding: 12px;
                font-size: 1rem;
                transition: all 0.3s ease;
                border: none;
            }

            .btn-change:hover {
                background-color: #734b2a;
                transform: translateY(-1px);
                box-shadow: 0 5px 15px rgba(139, 94, 60, 0.3);
            }

            .alert {
                border-radius: 12px;
                padding: 15px;
                font-weight: 500;
            }

            .back-link {
                display: block;
                text-align: center;
                margin-top: 25px;
                color: #6f4e37;
                text-decoration: none;
                font-weight: 500;
                transition: color 0.3s ease;
            }

            .back-link:hover {
                text-decoration: underline;
                color: #8b5e3c;
            }

            @media (max-width: 576px) {
                .card-reset {
                    padding: 25px 20px;
                    margin: 10px;
                }
            }
        </style>
    </head>

    <body>
        <div class="card-reset">
            <h2>Ganti Password</h2>

            <?php if($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= $success ?>
                </div>
                <a href="login.php" class="btn btn-change w-100 mt-3">
                    <i class="fas fa-sign-in-alt me-2"></i>Login Sekarang
                </a>
            <?php else: ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted mb-2">Password Baru</label>
                        <input type="password" class="form-control" name="password" placeholder="Masukkan password baru" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted mb-2">Konfirmasi Password</label>
                        <input type="password" class="form-control" name="confirm_password" placeholder="Ulangi password baru" required minlength="8">
                    </div>
                    <button type="submit" class="btn btn-change w-100">
                        <i class="fas fa-key me-2"></i>Ubah Password
                    </button>
                </form>
                <a href="login.php" class="back-link">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Login
                </a>
            <?php endif; ?>
        </div>
    </body>
</html>
