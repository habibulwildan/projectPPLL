<?php
// db_functions.php - Fungsi-fungsi untuk Interaksi Database (Tabel users dan roles)

/**
 * Fungsi untuk menambahkan pengguna baru ke database.
 * ... (kode add_user() yang sudah ada) ...
 */
function add_user(mysqli $conn, string $username, string $email, string $password, string $role = 'user', string $phone = '', array &$error_messages): bool {
    
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
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM users WHERE (name = ? OR email = ?) AND id != 0"); // id != 0 agar bisa dipakai di update juga
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
 * ... (kode get_all_users() yang sudah ada) ...
 */
function get_all_users(mysqli $conn, array &$error_messages): array {
    $users = [];
    
    $sql = "SELECT 
                U.id, 
                U.name AS username,      -- Kolom 'name' dari users
                U.email, 
                U.phone, 
                R.name_role AS role,     -- Kolom 'name_role' dari roles
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

// ----------------------------------------------------------
// 🚀 FUNGSI BARU UNTUK MENGHAPUS (DELETE)
// ----------------------------------------------------------

/**
 * Fungsi untuk menghapus pengguna dari database.
 * @param mysqli $conn Objek koneksi mysqli.
 * @param int $user_id ID pengguna yang akan dihapus.
 * @param array $error_messages Array referensi untuk menampung pesan error.
 * @return bool True jika berhasil, False jika gagal.
 */
function delete_user(mysqli $conn, int $user_id, array &$error_messages): bool {
    // Gunakan transaksi untuk memastikan semua operasi (jika ada) berhasil
    $conn->begin_transaction();
    
    try {
        // Pastikan $user_id valid
        if ($user_id <= 0) {
            $error_messages[] = "ID pengguna tidak valid.";
            return false;
        }

        // Statement DELETE
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        if (!$stmt) {
            throw new \Exception("Gagal menyiapkan statement DELETE: " . $conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $conn->commit();
            $stmt->close();
            return true;
        } else {
            $conn->rollback();
            $stmt->close();
            $error_messages[] = "Pengguna ID #{$user_id} tidak ditemukan atau gagal dihapus.";
            return false;
        }

    } catch (\Exception $e) {
        $conn->rollback();
        $error_messages[] = "Error menghapus pengguna: " . $e->getMessage();
        return false;
    }
}

// ----------------------------------------------------------
// 🔄 FUNGSI BARU UNTUK MEMPERBARUI (UPDATE)
// ----------------------------------------------------------

/**
 * Fungsi untuk memperbarui data pengguna.
 * @param mysqli $conn Objek koneksi mysqli.
 * @param int $user_id ID pengguna yang akan diperbarui.
 * @param string $username Nama pengguna.
 * @param string $email Alamat email.
 * @param string $password Password baru (kosongkan jika tidak diubah).
 * @param string $role Role ('admin', 'user', atau 'kasir').
 * @param string $phone Nomor telepon.
 * @param array $error_messages Array referensi untuk menampung pesan error.
 * @return bool True jika berhasil, False jika gagal.
 */
function update_user(mysqli $conn, int $user_id, string $username, string $email, string $password, string $role, string $phone, array &$error_messages): bool {
    
    // 1. Tentukan nama role di database
    $db_role_name = $role; 
    if ($role === 'user') {
        $db_role_name = 'customer'; 
    }

    // 2. Dapatkan role_id dari tabel roles
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

    // 3. Cek duplikasi email atau username (kecuali milik user_id yang sedang diedit)
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM users WHERE (name = ? OR email = ?) AND id != ?"); 
    if (!$stmt_check) {
        $error_messages[] = "Gagal menyiapkan pengecekan duplikasi: " . $conn->error;
        return false;
    }
    $stmt_check->bind_param("ssi", $username, $email, $user_id);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        $error_messages[] = "Username atau Email sudah terdaftar oleh pengguna lain.";
        return false;
    }
    
    // 4. Siapkan Query UPDATE (dinamis tergantung password)
    $params = [$username, $email, $role_id, $phone, $user_id];
    $types = "ssisi"; // name, email, role_id, phone, user_id

    if (!empty($password)) {
        // Jika password diisi, hash dan masukkan ke dalam array parameter
        $hashed_password = md5($password);
        array_unshift($params, $hashed_password); // Tambahkan password ke awal array
        $types = "ssssisi"; // password, name, email, role_id, phone, user_id
        
        $sql = "UPDATE users SET password = ?, name = ?, email = ?, role_id = ?, phone = ? WHERE id = ?";
    } else {
        // Jika password kosong, tidak perlu mengupdate kolom password
        $sql = "UPDATE users SET name = ?, email = ?, role_id = ?, phone = ? WHERE id = ?";
    }

    // 5. Eksekusi UPDATE
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Menggunakan call_user_func_array untuk bind_param dinamis
        $bind_params = array_merge([$types], $params);
        $refs = [];
        foreach ($bind_params as $key => $value) {
            $refs[$key] = &$bind_params[$key];
        }
        call_user_func_array([$stmt, 'bind_param'], $refs);
        
        if ($stmt->execute()) {
            // Perlu diperiksa apakah ada baris yang benar-benar diubah
            // affected_rows > 0 berarti ada perubahan, = 0 berarti data sama (masih dianggap sukses)
            if ($stmt->affected_rows >= 0) {
                $stmt->close();
                return true;
            } else {
                 // Kasus ini seharusnya tertangani oleh error DB, tapi sebagai jaga-jaga
                 $error_messages[] = "Gagal memperbarui pengguna (ID: {$user_id}).";
                 $stmt->close();
                 return false;
            }
        } else {
            $error_messages[] = "Gagal memperbarui pengguna: " . $stmt->error;
            $stmt->close();
            return false;
        }
    } else {
        $error_messages[] = "Gagal menyiapkan statement UPDATE: " . $conn->error;
        return false;
    }
}
?>
