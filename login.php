<?php
session_start(); // Mulai session

// panggil koneksi database
include 'config.php';

// Inisialisasi error dan input
$error_email = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $valid = true;

    // Validasi email format
    if (empty($email)) {
        $error_email = "Email wajib diisi.";
        $valid = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_email = "Format email tidak valid.";
        $valid = false;
    }

    // Validasi password: minimal 8 karakter, huruf besar, kecil, dan angka
    if (empty($password)) {
        $error_email = "Password wajib diisi.";
        $valid = false;
    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/", $password)) {
        $error_email = "Password harus terdiri dari minimal 8 karakter, mengandung huruf besar, huruf kecil, dan angka.";
        $valid = false;
    }

    // Jika validasi lulus, cek kecocokan dengan database
    if ($valid) {
        // Siapkan query untuk ambil id, name, password, dan role_id
        $stmt = $conn->prepare("SELECT id, name, password, role_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $user_name, $hash_password, $role_id);
            $stmt->fetch();

            // Verifikasi password dengan MD5 (sesuai penyimpanan di database)
            if (md5($password) === $hash_password) {
                // Login sukses, simpan SEMUA session user
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $user_name;
                $_SESSION['user_role_id'] = $role_id;
                $_SESSION['user_role'] = $role_id == 1 ? 'customer' : ($role_id == 2 ? 'kasir' : 'admin');

                // Role-based redirect
                switch ($role_id) {
                    case 1: // Customer
                        header("Location: index.php");
                        break;
                    case 2: // Kasir
                        header("Location: kasir_index.php");
                        break;
                    case 3: // Admin
                        header("Location: admin_index.php");
                        break;
                    default:
                        header("Location: index.php"); // Default ke customer
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
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Kopi Senja</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <style>
            html, body {
                height: 100%;
                margin: 0;
            }

            body {
                background-color: #f8f9fa;
                font-family: 'Poppins', sans-serif;
                color: #333;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding-bottom: 260px;
            }

            .page-title {
                font-size: 2.3rem;
                font-weight: 900;
                text-align: center;
                margin-top: 160px;
                margin-bottom: 40px;
                color: black;
                letter-spacing: 1px;
                margin-right: 20%;
                margin-left: 20%;
            }

            .container {
                max-width: 87%;
            }

            .card {
                border-radius: 20px;
                position: relative;
                overflow: hidden;
                min-height: 500px;
                height: auto;
                display: flex;
                flex-direction: row;
            }

            .left-panel {
                background: #8B4513;
                color: white;
                text-align: center;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                border-top-left-radius: 20px;
                border-bottom-left-radius: 20px;
                width: 45%;
                min-height: 100%;
                padding: 40px;
            }

            .left-panel h2 {
                font-size: 2.4rem;
                font-weight: 600;
                margin-top: 15px;
                margin-bottom: 45px;
            }

            .left-panel p {
                font-size: 1.1rem;
                font-weight: 400;
                padding: 10px 50px;
                margin-bottom: 25px;
            }

            .right-panel {
                flex: 1;
                padding: 40px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }

            .btn-register {
                border-radius: 18px;
                padding: 13px 20px;
                font-weight: 500;
                width: 220px;
                margin-top: 20px;
            }

            .card-body {
                width: 450px;
            }

            .judul {
                font-size: 2.8rem;
                font-weight: 900;
                margin-top: 30px;
                margin-left: 150px;
                color: #8B4513; /* Warna coklat */
            }

            .login-text {
                color: #6c757d;
                text-align: center;
                font-weight: 400;
                margin-top: 40px;
                margin-bottom: 40px;
            }

            .input-field {
                width: 100%;
                max-width: 800px;
                padding: 10px;
            }

            .input-field1 {
                width: 100%;
                max-width: 800px;
                padding: 10px;
            }

            .btn-login {
                border-radius: 18px;
                padding: 12px 20px;
                font-weight: 500;
                width: 60%;
                max-width: 300px;
                display: block;
                margin: 0px auto;
            }

            .btn-brown {
                background-color: #8B4513; /* warna coklat */
                border-color: #8B4513;
                color: white;
            }

            .btn-brown:hover {
                background-color: #6F330E; /* warna coklat lebih gelap saat hover */
                border-color: #6F330E;
                color: white;
            }

            .error-message {
                background-color: rgba(255, 0, 0, 0.1);
                color: red;
                border: 1px solid red;
                border-radius: 5px;
                padding: 10px;
                margin-bottom: 10px;
            }

            @media (max-width: 768px) {
                .card {
                    flex-direction: column;
                }

                .left-panel {
                    width: 100%;
                    height: auto;
                    border-radius: 20px 20px 0 0;
                }

                .right-panel {
                    margin-left: 0;
                    padding: 20px;
                }
            }
        </style>
    </head>

    <body>
        <div class="container mt-5 pt-5">
            <h1 class="page-title">Selamat Datang di Kopi Senja</h1>
            <div class="card shadow-lg">
                <div class="left-panel">
                    <h2>Welcome Back!</h2>
                    <p>To keep connected with us please login with your personal info</p>
                    <p>Don't have an account yet?</p>
                    <a href="register.php" class="btn btn-outline-light btn-register fw-bold">REGISTER</a>
                </div>
                
                <div class="right-panel">
                    <div class="card-body">
                        <h3 class="judul">LOGIN</h3>
                        <p class="login-text">Use your email account:</p>
                        <form action="" method="POST">
                            <?php if (!empty($error_email)) : ?>
                            <div class="error-message"><?php echo $error_email; ?></div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control input-field" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control input-field1" name="password" placeholder="Password" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-brown btn-login fw-bold">LOG IN</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        </body>
</html>