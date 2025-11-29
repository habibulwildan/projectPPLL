<?php
session_start();
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit;
}

$mysqli = $conn ?? $mysqli ?? null;
$user_email = $_SESSION['user_email'];

// Ambil data user
$stmt = $mysqli->prepare("SELECT id, name, email, phone FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

$user_id = $user['id'];
$name = $user['name'];
$email = $user['email'];
$phone = $user['phone'] ?? "-";
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Profil Saya – Kopi Senja</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg, #c7b299, #d9cbb8, #bfa88c);
    min-height: 100vh;
    font-family: "Poppins", sans-serif;
}

.profile-box {
    background: #fdf8f3;
    padding: 30px;
    border-radius: 15px;
    border: 2px solid #b08c6d;
    box-shadow: 0 8px 20px rgba(50,30,10,0.2);
}

.profile-title {
    text-align: center;
    color: #4a321c;
    font-weight: 700;
}

.label {
    font-weight: 600;
    color: #4a321c;
}

.input-custom {
    border-radius: 10px;
    border: 1px solid #c8a27e;
    padding: 10px;
}

.btn-save {
    background: #8b5e34;
    color: white;
    padding: 10px 20px;
    border-radius: 10px;
}
.btn-save:hover { background: #734b2a; }
</style>

</head>
<body>

<div class="container mt-5" style="max-width: 600px;">

    <h2 class="profile-title mb-4">Profil Saya</h2>

    <div class="profile-box">

        <form action="update_profile.php" method="POST">

            <input type="hidden" name="id" value="<?= $user_id ?>">

            <label class="label">Nama Lengkap</label>
            <input type="text" name="name" class="form-control input-custom mb-3"
                   value="<?= htmlspecialchars($name) ?>" required>

            <label class="label">Email</label>
            <input type="email" class="form-control input-custom mb-3"
                   value="<?= htmlspecialchars($email) ?>" readonly>

            <label class="label">Nomor HP</label>
            <input type="text" name="phone" class="form-control input-custom mb-4"
                   value="<?= htmlspecialchars($phone) ?>">

            <button type="submit" class="btn btn-save w-100">Simpan Perubahan</button>
        </form>

        <div class="text-center mt-4">
            <a href="menu.php" class="text-decoration-none" style="color:#4a321c;">
                ← Kembali ke Menu
            </a>
        </div>

    </div>

</div>

</body>
</html>
