<?php
// panggil koneksi database
include 'koneksi.php';

// Inisialisasi variabel error dan input value
$error_nama = $error_phone = $error_email = $error_password = "";
$nama = $phone = $email = $password = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $valid = true;

    // Ambil dan bersihkan data input
    $nama = trim($_POST['nama']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validasi nama: maks 30 karakter, hanya huruf dan spasi
    if (empty($nama)) {
        $error_nama = "Nama wajib diisi.";
        $valid = false;
    } elseif (strlen($nama) > 30) {
        $error_nama = "Nama maksimal 30 karakter.";
        $valid = false;
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $nama)) {
        $error_nama = "Nama hanya boleh huruf dan spasi.";
        $valid = false;
    }

    // Validasi phone: maksimal 13 digit, hanya angka
    if (empty($phone)) {
        $error_phone = "Phone wajib diisi.";
        $valid = false;
    } elseif (!preg_match("/^\d{1,13}$/", $phone)) {
        $error_phone = "Phone hanya boleh angka, maksimal 13 digit.";
        $valid = false;
    }

    // Validasi email: format email valid
    if (empty($email)) {
        $error_email = "Email wajib diisi.";
        $valid = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_email = "Format email tidak valid.";
        $valid = false;
    }

    // Validasi password: minimal 8 karakter, ada huruf besar, huruf kecil, dan angka
    if (empty($password)) {
        $error_password = "Password wajib diisi.";
        $valid = false;
    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/", $password)) {
        $error_password = "Password harus terdiri dari minimal 8 karakter, mengandung huruf besar, huruf kecil, dan angka.";
        $valid = false;
    }

    // Jika validasi lolos, simpan ke database
    if ($valid) {
        // Enkripsi password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Siapkan statement insert
        // Query dengan NOW() untuk email_verified_at, created_at, updated_at
        $stmt = $koneksi->prepare("INSERT INTO users (name, phone, email, password, email_verified_at, remember_token, created_at, updated_at, role_id) VALUES (?, ?, ?, ?, NOW(), NULL, NOW(), NOW(), 1)");

        // set email_verified_at saat ini
        // $email_verified_at = date('Y-m-d H:i:s');

        $stmt->bind_param("ssss", $nama, $phone, $email, $password_hash);

        if ($stmt->execute()) {
            // Redirect ke halaman login atau home setelah berhasil register
            header("Location: login.php");
            exit;
        } else {
            // Jika gagal insert, tampilkan error (misal email sudah ada)
            $error_email = "Gagal menyimpan data. Email mungkin sudah terdaftar.";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Register Kopi Senja</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
        <style>
            html,
            body {
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
            }

            .page-title {
                font-size: 2.3rem;
                font-weight: 900;
                text-align: center;
                margin-top: 150px;
                margin-bottom: 35px;
                color: black;
                letter-spacing: 1px;
                margin-right: 20%;
                margin-left: 20%;
            }

            .container {
                max-width: 90%; /* diperlebar dari 87% */
                margin-bottom: 60px;
                padding-bottom: 50px;
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
                padding: 10px 40px;
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

            .btn-login {
                border-radius: 18px;
                padding: 13px 20px;
                font-weight: 500;
                width: 220px;
                margin-top: 20px;
            }

            .card-body {
                width: 500px; /* diperlebar dari 450px */
            }

            .judul {
                font-size: 2.8rem;
                font-weight: 900;
                margin-top: 17px;
                margin-left: 55px;
                color: #8B4513;
            }

            .registration-text {
                color: #6c757d;
                text-align: center;
                font-weight: 400;
                margin-top: 40px;
                margin-bottom: 40px;
            }

            .input-field,
            .input-field1 {
                width: 100%;
                max-width: 800px;
                padding: 10px;
            }

            .btn-signup {
                border-radius: 18px;
                padding: 12px 20px;
                font-weight: 500;
                width: 60%;
                max-width: 300px;
                display: block;
                margin: 0 auto;
            }

            .btn-brown {
                background-color: #8B4513;
                border-color: #8B4513;
                color: white;
            }

            .btn-brown:hover {
                background-color: #6F330E;
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

                .card-body {
                    width: 100%;
                }
            }
        </style>
    </head>

    <body>
        <div class="container mt-5 pt-5">
            <h1 class="page-title">Selamat Datang di Kopi Senja</h1>
            <div class="card shadow-lg">
                <div class="left-panel">
                    <h2>Hello, Friend!</h2>
                    <p>Enter your personal information and enjoy the best coffee experience with us</p>
                    <p>Already have an account?</p>
                    <a href="login.html" class="btn btn-outline-light btn-login fw-bold">LOG IN</a>
                </div>

                <div class="right-panel">
                    <div class="card-body">
                        <h3 class="judul">Create Account</h3>
                        <p class="registration-text">Use your email for registration:</p>
                        <form action="" method="POST">
                            <!-- Nama -->
                            <?php if (!empty($error_nama)) : ?>
                            <div class="error-message"><?php echo $error_nama; ?></div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control input-field" name="nama" placeholder="Nama" value="<?php echo htmlspecialchars($nama); ?>" required />
                                </div>
                            </div>

                            <!-- Phone -->
                            <?php if (!empty($error_phone)) : ?>
                            <div class="error-message"><?php echo $error_phone; ?></div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" class="form-control input-field" name="phone" placeholder="Phone" value="<?php echo htmlspecialchars($phone); ?>" required />
                                </div>
                            </div>

                            <!-- Email -->
                            <?php if (!empty($error_email)) : ?>
                            <div class="error-message"><?php echo $error_email; ?></div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control input-field" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required />
                                </div>
                            </div>

                            <!-- Password -->
                            <?php if (!empty($error_password)) : ?>
                            <div class="error-message"><?php echo $error_password; ?></div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control input-field1" name="password" placeholder="Password" required />
                                </div>
                            </div>

                            <button type="submit" class="btn btn-brown btn-signup fw-bold">SIGN UP</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>