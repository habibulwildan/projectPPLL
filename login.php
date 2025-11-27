<?php
// ==============================================================================
// 
// PENTING: Gunakan bagian ini hanya untuk UJI COBA membuat HASH baru.
// Setelah mendapatkan HASH yang benar, HAPUS kode di bawah ini 
// dan masukkan HASH tersebut ke database secara manual (kolom 'password').
// 
// ==============================================================================

/*
$password_baru_admin = "admin123"; // Ganti dengan password yang ingin Anda gunakan
$hash_password_baru = password_hash($password_baru_admin, PASSWORD_DEFAULT);
echo "Password Anda: " . $password_baru_admin . "<br>";
echo "Hash (yang harus disimpan di DB): " . $hash_password_baru . "<br>";
// Setelah Anda mendapatkan hash-nya, HAPUS atau komentari bagian kode ini.
*/

// ==============================================================================
//
// AKHIR KODE UJI COBA
//
// ==============================================================================
session_start();
// Pastikan file config.php berisi koneksi $conn
include "config.php"; 

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $email = $_POST["email"];
    $password = $_POST["password"];

    // 1. PENCEGAHAN SQL INJECTION: Gunakan prepared statement.
    // Tanda '?' adalah placeholder untuk nilai yang akan di-bind.
    $sql = "SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1";
    
    // Siapkan statement
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        // Bind parameter: 's' menandakan tipe data string ($email)
        mysqli_stmt_bind_param($stmt, "s", $email);

        // Eksekusi statement
        mysqli_stmt_execute($stmt);

        // Ambil hasil kueri
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            // 2. VERIFIKASI PASSWORD (Disesuaikan untuk MD5)
            // KODE INI MENGGUNAKAN MD5 (TIDAK AMAN), disesuaikan dengan database Anda.
            // Jika Anda ingin kembali ke Bcrypt, ganti baris di bawah dengan: 
            // if (password_verify($password, $user["password"])) {
            if (md5($password) === $user["password"]) {
                // Simpan sesi pengguna
                $_SESSION["admin_id"] = $user["id"];
                $_SESSION["admin_name"] = $user["name"];
                $_SESSION["admin_email"] = $user["email"];

                // Tutup statement sebelum redirect
                mysqli_stmt_close($stmt);

                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Email tidak ditemukan!";
        }

        // Tutup statement
        if (isset($stmt)) {
            mysqli_stmt_close($stmt);
        }
    } else {
        // Kesalahan pada persiapan statement
        $error = "Terjadi kesalahan database saat persiapan query.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Admin</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* ANIMASI */
        .fade-in {
            animation: fadeIn 1s ease-in-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-black via-[#3b2f2f] to-[#8a6d46] flex items-center justify-center p-4">

    <!-- Background Blur Circle -->
    <div class="absolute top-10 left-10 w-72 h-72 bg-[#8a6d46] opacity-20 blur-3xl rounded-full"></div>
    <div class="absolute bottom-10 right-10 w-72 h-72 bg-[#3b2f2f] opacity-20 blur-3xl rounded-full"></div>

    <div class="relative z-10 w-full max-w-md fade-in">
        <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl shadow-2xl p-8">

            <h2 class="text-3xl font-bold text-center text-[#e7d1b1] mb-6">Login Admin</h2>

            <?php if (!empty($error)) : ?>
                <p class="mb-4 text-red-300 text-center text-sm"><?= $error ?></p>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-4">

                <div>
                    <label class="text-[#f2e4cf]">Email</label>
                    <input type="email" name="email" required
                        class="w-full px-4 py-2 rounded-lg bg-white/20 text-white placeholder-gray-200 focus:ring-2 focus:ring-[#c8a66a] outline-none">
                </div>

                <div>
                    <label class="text-[#f2e4cf]">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-2 rounded-lg bg-white/20 text-white placeholder-gray-200 focus:ring-2 focus:ring-[#c8a66a] outline-none">
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-[#5c422f] to-[#8a6d46] text-white py-2 rounded-lg font-semibold shadow-lg hover:scale-[1.02] transition">
                    Login
                </button>

            </form>

        </div>
    </div>

</body>

</html>