<?php
// db_functions.php - Fungsi-fungsi untuk Interaksi Database (Tabel users dan roles)

/**
 * Fungsi untuk menambahkan pengguna baru ke database.
 * @param mysqli $conn Objek koneksi mysqli.
 * @param string $username Nama pengguna (akan disimpan di kolom 'name' di DB).
 * @param string $email Alamat email.
 * @param string $password Password mentah.
 * @param array $error_messages Array referensi untuk menampung pesan error.
 * @param string $role Role ('admin', 'user', atau 'kasir' - yang akan diubah menjadi role_id).
 * @param string $phone Nomor telepon.
 * @return bool True jika berhasil, False jika gagal.
 */
function add_user(mysqli $conn, string $username, string $email, string $password, array &$error_messages, string $role = 'user', string $phone = ''): bool
{

    // 1. Hash Password (PENTING!)
    // DIKOREKSI: Menggunakan MD5 murni sesuai permintaan
    $hashed_password = md5($password);

    // 2. Tentukan nama role di database (asumsi: 'user' di form = 'customer' di DB)
    $db_role_name = $role;

    // Konversi role 'user' di form menjadi 'customer' di DB (sesuai data tabel roles Anda)
    if ($role === 'user') {
        $db_role_name = 'customer';
    }
    // Jika form mengirim 'kasir' atau 'admin', biarkan $db_role_name tetap.

    // 3. Dapatkan role_id dari tabel roles
    // KOREKSI UTAMA: Ganti 'name' menjadi 'name_role'
    $stmt_role = $conn->prepare("SELECT id FROM roles WHERE name_role = ?");
    if (!$stmt_role) {
        $error_messages[] = "Gagal menyiapkan pencarian Role ID: " . $conn->error;
        return false;
    }
    $stmt_role->bind_param("s", $db_role_name);
    $stmt_role->execute();
    $result_role = $stmt_role->get_result();
    $role_row = $result_role->fetch_assoc();
    $stmt_role->close();

    if (!$role_row) {
        $error_messages[] = "Role ('{$db_role_name}') tidak ditemukan dalam database.";
        return false;
    }
    $role_id = $role_row['id'];

    // 4. Cek duplikasi email atau NAME (menggunakan kolom 'name' di tabel users)
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM users WHERE name = ? OR email = ?");
    if (!$stmt_check) {
        $error_messages[] = "Gagal menyiapkan pengecekan: " . $conn->error;
        return false;
    }
    $stmt_check->bind_param("ss", $username, $email);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        $error_messages[] = "Username atau Email sudah terdaftar.";
        return false;
    }

    // 5. Persiapan statement SQL untuk INSERT
    // MENGGUNAKAN KOLOM 'name' dan 'role_id'
    $sql = "INSERT INTO users (name, email, password, role_id, phone) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind parameter: name(s), email(s), password(s), role_id(i), phone(s)
        $stmt->bind_param("sssis", $username, $email, $hashed_password, $role_id, $phone);

        // Eksekusi statement
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $error_messages[] = "Gagal menambahkan pengguna: " . $stmt->error;
            $stmt->close();
            return false;
        }
    } else {
        $error_messages[] = "Gagal menyiapkan statement: " . $conn->error;
        return false;
    }
}


/**
 * Fungsi untuk mengambil semua data pengguna dari database (dengan JOIN ke roles).
 * @param mysqli $conn Objek koneksi mysqli.
 * @param array $error_messages Array referensi untuk menampung pesan error.
 * @return array Array berisi daftar pengguna atau array kosong jika gagal/tidak ada data.
 */
function get_all_users(mysqli $conn, array &$error_messages): array
{
    $users = [];

    // Bagian ini sudah benar
    $sql = "SELECT 
                U.id, 
                U.name AS username,      -- Kolom 'name' dari users
                U.email, 
                U.phone, 
                R.name_role AS role,     -- Kolom 'name_role' dari roles (Sudah dikoreksi)
                U.created_at 
            FROM users U
            JOIN roles R ON U.role_id = R.id 
            ORDER BY U.id DESC";

    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        $result->free();
    } else {
        $error_messages[] = "Gagal mengambil data pengguna: " . $conn->error;
    }

    return $users;
}

/**
 * Fungsi untuk mengedit data pengguna.
 * @param mysqli $conn Objek koneksi mysqli.
 * @param int $user_id ID pengguna yang akan diedit.
 * @param string $username Nama pengguna baru.
 * @param string $email Email baru.
 * @param string|null $password Password baru (jika tidak null, akan diupdate).
 * @param array $error_messages Array referensi untuk menampung pesan error.
 * @param string $role Role baru ('admin', 'user', atau 'kasir').
 * @param string $phone Nomor telepon baru.
 * @return bool True jika berhasil, False jika gagal.
 */
function update_user(mysqli $conn, int $user_id, string $username, string $email, ?string $password, array &$error_messages, string $role = 'user', string $phone = ''): bool
{
    // Konversi role 'user' di form menjadi 'customer' di DB
    $db_role_name = $role === 'user' ? 'customer' : $role;

    // Ambil role_id
    $stmt_role = $conn->prepare("SELECT id FROM roles WHERE name_role = ?");
    if (!$stmt_role) {
        $error_messages[] = "Gagal menyiapkan pencarian Role ID: " . $conn->error;
        return false;
    }
    $stmt_role->bind_param("s", $db_role_name);
    $stmt_role->execute();
    $result_role = $stmt_role->get_result();
    $role_row = $result_role->fetch_assoc();
    $stmt_role->close();

    if (!$role_row) {
        $error_messages[] = "Role ('{$db_role_name}') tidak ditemukan dalam database.";
        return false;
    }
    $role_id = $role_row['id'];

    // Cek duplikasi username/email selain user ini
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM users WHERE (name = ? OR email = ?) AND id != ?");
    if (!$stmt_check) {
        $error_messages[] = "Gagal menyiapkan pengecekan: " . $conn->error;
        return false;
    }
    $stmt_check->bind_param("ssi", $username, $email, $user_id);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        $error_messages[] = "Username atau Email sudah digunakan oleh pengguna lain.";
        return false;
    }

    // Update statement
    if ($password !== null && $password !== '') {
        $hashed_password = md5($password);
        $sql = "UPDATE users SET name = ?, email = ?, password = ?, role_id = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssisi", $username, $email, $hashed_password, $role_id, $phone, $user_id);
        }
    } else {
        $sql = "UPDATE users SET name = ?, email = ?, role_id = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssisi", $username, $email, $role_id, $phone, $user_id);
        }
    }

    if ($stmt) {
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $error_messages[] = "Gagal mengupdate pengguna: " . $stmt->error;
            $stmt->close();
            return false;
        }
    } else {
        $error_messages[] = "Gagal menyiapkan statement update: " . $conn->error;
        return false;
    }
}

/**
 * Fungsi untuk menghapus pengguna berdasarkan ID.
 * @param mysqli $conn Objek koneksi mysqli.
 * @param int $user_id ID pengguna yang akan dihapus.
 * @param array $error_messages Array referensi untuk menampung pesan error.
 * @return bool True jika berhasil, False jika gagal.
 */
function delete_user(mysqli $conn, int $user_id, array &$error_messages): bool
{
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $error_messages[] = "Gagal menghapus pengguna: " . $stmt->error;
            $stmt->close();
            return false;
        }
    } else {
        $error_messages[] = "Gagal menyiapkan statement hapus: " . $conn->error;
        return false;
    }
}

// Tambahkan fungsi lain (edit_user, delete_user) di sini nanti
