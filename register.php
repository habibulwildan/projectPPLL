<?php
include 'config.php';

// Inisialisasi error dan input value
$error_nama = $error_phone = $error_email = $error_password = "";
$nama = $phone = $email = $password = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $valid = true;

    $nama = trim($_POST['nama']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validasi Nama
    if (empty($nama)) {
        $error_nama = "Nama wajib diisi."; $valid = false;
    } elseif (strlen($nama) > 30) {
        $error_nama = "Nama maksimal 30 karakter."; $valid = false;
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $nama)) {
        $error_nama = "Nama hanya boleh huruf dan spasi."; $valid = false;
    }

    // Validasi Phone
    if (empty($phone)) {
        $error_phone = "Phone wajib diisi."; $valid = false;
    } elseif (!preg_match("/^\d{1,13}$/", $phone)) {
        $error_phone = "Phone hanya boleh angka, maksimal 13 digit."; $valid = false;
    }

    // Validasi Email
    if (empty($email)) {
        $error_email = "Email wajib diisi."; $valid = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_email = "Format email tidak valid."; $valid = false;
    }

    // Validasi Password
    if (empty($password)) {
        $error_password = "Password wajib diisi."; $valid = false;
    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/", $password)) {
        $error_password = "Password minimal 8 karakter, mengandung huruf besar, kecil, dan angka."; $valid = false;
    }

    if ($valid) {
        $password_hash = md5($password);
        $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password, email_verified_at, created_at, updated_at, role_id) VALUES (?, ?, ?, ?, NOW(), NOW(), NOW(), 1)");
        $stmt->bind_param("ssss", $nama, $phone, $email, $password_hash);
        if ($stmt->execute()) {
            header("Location: login.php"); exit;
        } else {
            $error_email = "Gagal menyimpan data. Email mungkin sudah terdaftar.";
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
<title>Register Kopi Senja</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #f2e8dc, #e0cba8);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.card {
    border-radius: 20px;
    max-width: 900px;
    width: 100%;
    display: flex;
    flex-direction: row;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

/* PANEL KIRI */
.left-panel {
    background: #8B4513;
    color: white;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 40px;
}

.left-panel h2 {
    font-size: 2.5rem;
    font-weight: 600;
    margin-bottom: 20px;
}

.left-panel p {
    font-size: 1.1rem;
    margin-bottom: 25px;
}

.left-panel a {
    border-radius: 18px;
    padding: 12px 25px;
    font-weight: 500;
    width: 220px;
}

/* PANEL KANAN */
.right-panel {
    flex: 1;
    background: #fffaf2;
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.right-panel h3 {
    color: #8B4513;
    font-weight: 700;
    margin-bottom: 20px;
    text-align: center;
}

.input-group-text {
    background: #f0e0d6;
    border: 1px solid #c8a27e;
}

.form-control {
    border-radius: 10px;
    border: 1px solid #c8a27e;
}

.btn-signup {
    background: #8B4513;
    color: white;
    border-radius: 18px;
    width: 100%;
    padding: 12px;
    margin-top: 15px;
    font-weight: 600;
}

.btn-signup:hover {
    background: #6F330E;
    color: white;
}

.error-message {
    background-color: rgba(255,0,0,0.1);
    color: red;
    border: 1px solid red;
    border-radius: 5px;
    padding: 10px;
    margin-bottom: 10px;
    text-align: center;
}

@media (max-width: 768px) {
    .card {
        flex-direction: column;
    }
    .left-panel, .right-panel {
        width: 100%;
        text-align: center;
        padding: 30px;
    }
}
</style>
</head>

<body>
<div class="card">
    <div class="left-panel">
        <h2>Hello, Friend!</h2>
        <p>Sudah punya akun? Masuk dan nikmati kopi terbaik kami.</p>
        <a href="login.php" class="btn btn-outline-light fw-bold">LOG IN</a>
    </div>

    <div class="right-panel">
        <h3>Buat Akun</h3>
        <form action="" method="POST">
            <?php if (!empty($error_nama)) echo "<div class='error-message'>{$error_nama}</div>"; ?>
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" name="nama" placeholder="Nama" value="<?php echo htmlspecialchars($nama); ?>" required>
                </div>
            </div>

            <?php if (!empty($error_phone)) echo "<div class='error-message'>{$error_phone}</div>"; ?>
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                    <input type="tel" class="form-control" name="phone" placeholder="Phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                </div>
            </div>

            <?php if (!empty($error_email)) echo "<div class='error-message'>{$error_email}</div>"; ?>
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
            </div>

            <?php if (!empty($error_password)) echo "<div class='error-message'>{$error_password}</div>"; ?>
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-signup fw-bold">SIGN UP</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
