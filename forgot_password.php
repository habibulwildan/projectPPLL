<?php
session_start();
require_once __DIR__ . '/config.php';

$mysqli = $conn ?? null;

if (!$mysqli) {
    die("Koneksi database gagal.");
}

$message = "";
$error = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        // Cek email ada di database users
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            // Hapus token lama
            $stmt_del = $mysqli->prepare("DELETE FROM password_reset_tokens WHERE email=?");
            $stmt_del->bind_param("s", $email);
            $stmt_del->execute();
            $stmt_del->close();

            // Generate token baru
            $token = bin2hex(random_bytes(16));

            // Insert token baru
            $stmt2 = $mysqli->prepare("INSERT INTO password_reset_tokens(email, token, created_at) VALUES (?, ?, NOW())");
            $stmt2->bind_param("ss", $email, $token);
            if ($stmt2->execute()) {
                $reset_link = "http://localhost/projectPPLL-main/reset_password_confirm.php?token=$token";
                $message = "Link reset password berhasil dibuat!<br>";
                $message .= "<a href='$reset_link'>$reset_link</a>";
            } else {
                $error = "Gagal menyimpan token: " . $stmt2->error;
            }
            $stmt2->close();
        } else {
            $error = "Email tidak ditemukan di database.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lupa Password – Kopi Senja</title>
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

    .btn-reset {
        background-color: #8b5e3c;
        color: white;
        font-weight: 600;
        border-radius: 10px;
        transition: background 0.3s ease;
    }

    .btn-reset:hover {
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
    <h2>Reset Password</h2>

    <?php if($error) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if($message) : ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <input type="email" class="form-control" name="email" placeholder="Masukkan email" value="<?= htmlspecialchars($email ?? '') ?>" required>
        </div>
            <button type="submit" class="btn btn-reset w-100">Kirim Link Reset</button>
    </form>

    <a href="login.php" class="back-link">← Kembali ke Login</a>
</div>

</body>
</html>
