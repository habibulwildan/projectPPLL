<?php
session_start(); // Mulai session

include 'config.php';

// Inisialisasi error dan input
$error_email = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $valid = true;

    // Validasi email
    if (empty($email)) {
        $error_email = "Email wajib diisi.";
        $valid = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_email = "Format email tidak valid.";
        $valid = false;
    }

    // Validasi password
    if (empty($password)) {
        $error_email = "Password wajib diisi.";
        $valid = false;
    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/", $password)) {
        $error_email = "Password minimal 8 karakter, mengandung huruf besar, kecil, dan angka.";
        $valid = false;
    }

    if ($valid) {
        $stmt = $conn->prepare("SELECT id, name, password, role_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $user_name, $hash_password, $role_id);
            $stmt->fetch();

            if (md5($password) === $hash_password) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $user_name;
                $_SESSION['user_role_id'] = $role_id;
                $_SESSION['user_role'] = $role_id == 1 ? 'customer' : ($role_id == 2 ? 'kasir' : 'admin');

                switch ($role_id) {
                    case 1: header("Location: index.php"); break;
                    case 2: header("Location: kasir/index.php"); break;
                    case 3: header("Location: admin_index.php"); break;
                    default: header("Location: index.php");
                }
                exit;
            } else {
                $error_email = "Kombinasi email dan password salah.";
            }
        } else {
            $error_email = "Kombinasi email dan password salah.";
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
<title>Login Kopi Senja</title>
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
    overflow: hidden;
    max-width: 900px;
    width: 100%;
    display: flex;
    flex-direction: row;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

/* PANEL KIRI */
.left-panel {
    background: #8B4513;
    color: white;
    padding: 40px;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
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

.btn-login {
    background: #8B4513;
    color: white;
    border-radius: 18px;
    width: 100%;
    padding: 12px;
    margin-top: 15px;
    font-weight: 600;
}

.btn-login:hover {
    background: #6F330E;
    color: white;
}

.btn-register {
    border-radius: 18px;
    padding: 12px 25px;
    font-weight: 500;
    width: 220px;
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

.forgot-link {
    color: #8B4513;
    text-decoration: none;
    font-weight: 500;
}

.forgot-link:hover {
    color: #6F330E;
    text-decoration: underline;
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
    .btn-register {
        width: 100%;
    }
}
</style>
</head>

<body>
<div class="card">
    <div class="left-panel">
        <h2>Selamat Datang!</h2>
        <p>Masuk untuk tetap terhubung dengan akunmu.</p>
        <p>Belum punya akun?</p>
        <a href="register.php" class="btn btn-outline-light btn-register fw-bold">REGISTER</a>
    </div>

    <div class="right-panel">
        <h3>LOGIN</h3>
        <?php if (!empty($error_email)) : ?>
        <div class="error-message"><?php echo $error_email; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>
            </div>

            <div class="text-end mb-3">
                <a href="forgot_password.php" class="forgot-link">Lupa Password?</a>
            </div>

            <button type="submit" class="btn btn-login">LOG IN</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
