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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Saya – Kopi Senja</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { 
    background: linear-gradient(135deg,#c7b299,#d9cbb8,#bfa88c); 
    font-family:"Poppins",sans-serif; 
    min-height:100vh;
    padding-top: 30px;
    padding-bottom: 30px;
}

.profile-box {
    background:#fdf8f3;
    padding:30px;
    border-radius:15px;
    border:2px solid #b08c6d;
    box-shadow:0 8px 20px rgba(50,30,10,0.2);
    width: 100%;
    max-width: 500px;
    margin: 0 auto;
}

.profile-title {
    text-align:center;
    color:#4a321c;
    font-weight:700;
    margin-bottom: 30px;
}

.label {
    font-weight:600;
    color:#4a321c;
}

.input-custom {
    border-radius:10px;
    border:1px solid #c8a27e;
    padding:10px;
}

.btn-save {
    background:#8b5e34;
    color:white;
    padding:10px 20px;
    border-radius:10px;
}
.btn-save:hover { 
    background:#734b2a; 
}

.btn-token {
    background:#4a7c59;
    color:white;
    padding:10px 20px;
    border-radius:10px;
    display:block;
    text-align:center;
    text-decoration:none;
    margin-top:10px;
}
.btn-token:hover { 
    background:#366644; 
}

/* Responsif */
@media (max-width: 576px) {
    .profile-box {
        padding: 20px;
    }
    .btn-save, .btn-token {
        width: 100%;
        padding: 12px 0;
    }
}
</style>
</head>
<body>

<div class="profile-box">
    <h2 class="profile-title">Profil Saya</h2>
    <form action="update_profile.php" method="POST">
        <input type="hidden" name="id" value="<?= $user_id ?>">
        
        <label class="label">Nama Lengkap</label>
        <input type="text" name="name" class="form-control input-custom mb-3" value="<?= htmlspecialchars($name) ?>" required>
        
        <label class="label">Email</label>
        <input type="email" class="form-control input-custom mb-3" value="<?= htmlspecialchars($email) ?>" readonly>
        
        <label class="label">Nomor HP</label>
        <input type="text" name="phone" class="form-control input-custom mb-4" value="<?= htmlspecialchars($phone) ?>">
        
        <button type="submit" class="btn btn-save w-100">Simpan Perubahan</button>
    </form>

    <!-- Tombol tambahan -->
    <!-- <a href="send_reset_email.php" class="btn btn-warning w-100 mt-3">Reset Password</a>
    <a href="reset_tokens.php" class="btn btn-info w-100 mt-2">Lihat Token Reset Password</a> -->

    <div class="text-center mt-4">
        <a href="menu.php" class="text-decoration-none" style="color:#4a321c;">← Kembali ke Menu</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
