<?php
session_start();

// Cek 1 & 2: Pastikan user sudah login dan memiliki role sebagai Admin (ID role = 3)
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['user_role_id']) && $_SESSION['user_role_id'] == 3;

// Jika user TIDAK login ATAU user login TAPI bukan Admin, arahkan kembali.
if (!$is_logged_in || !$is_admin) {
    $_SESSION['login_message'] = "Akses ditolak. Anda harus login sebagai Admin.";
    header("Location: login.php");
    exit;
}
// ==========================================================
// 1. INCLUDE FILE EXTERNAL: Koneksi dan Fungsi DB
// ==========================================================
// Asumsikan file ini menyediakan koneksi $conn dan mungkin $db_error
include "config.php";
// PENTING: File yang berisi fungsi add_user, update_user, delete_user, dan get_all_users
include "db_functions.php";

// Data pengguna yang sudah login
// Perbaikan Warning: Pastikan variabel didefinisikan dengan nilai default
$admin_name = $_SESSION['admin_name'] ?? "Admin";
$admin_email = $_SESSION['admin_email'] ?? "admin@example.com";

// Inisialisasi variabel untuk tampilan
$users_list = [];
$error_messages = [];
$success_message = "";

// Inisialisasi variabel untuk form Tambah
$old_post = [
    'username' => '',
    'email' => '',
    'phone' => '',
    'role' => 'user'
];
// Variabel untuk modal edit yang mungkin perlu dibuka
$user_to_edit = null;
$edit_mode_active = false; // Flag untuk mendeteksi apakah modal edit harus terbuka

// Ambil pesan sukses dari sesi jika ada
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// ==========================================================
// 2. LOGIKA PEMROSESAN FORMULIR (TAMBAH, UPDATE, DELETE)
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // --- LOGIKA TAMBAH PENGGUNA (CREATE) ---
    if ($_POST['action'] === 'add_user') {
        // Ambil data dari formulir dan simpan ke $old_post
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $phone = trim($_POST['phone'] ?? '');

        $old_post = [
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'role' => $role
        ];

        if ($conn) {
            // Validasi dasar
            if (empty($username) || empty($email) || empty($password)) {
                $error_messages[] = "Semua field wajib (Username, Email, Password) harus diisi.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error_messages[] = "Format email tidak valid.";
            } elseif (strlen($password) < 6) {
                $error_messages[] = "Password harus minimal 6 karakter.";
            } else {
                // Panggil fungsi untuk menambahkan pengguna
                $result = add_user($conn, $username, $email, $password, $error_messages, $role, $phone);

                if ($result) {
                    $_SESSION['success_message'] = "Pengguna **" . htmlspecialchars($username) . "** berhasil ditambahkan.";
                    header("Location: pengguna.php");
                    exit;
                }
                // Jika gagal, error_messages sudah terisi oleh fungsi add_user
            }
        } else {
            $error_messages[] = "Koneksi database tidak tersedia untuk memproses form Tambah.";
        }
    }

    // --- LOGIKA HAPUS PENGGUNA (DELETE) ---
    elseif ($_POST['action'] === 'delete_user') {
        $user_id = (int) ($_POST['user_id'] ?? 0);

        if ($conn && $user_id > 0) {
            // Cek untuk mencegah admin menghapus dirinya sendiri
            if ($user_id == ($_SESSION['user_id'] ?? 0)) {
                $error_messages[] = "Anda tidak dapat menghapus akun Anda sendiri.";
            } else {
                $result = delete_user($conn, $user_id, $error_messages);
                if ($result) {
                    $_SESSION['success_message'] = "Pengguna ID #{$user_id} berhasil dihapus.";
                    header("Location: pengguna.php");
                    exit;
                } else {
                    // Pesan error sudah diisi oleh fungsi delete_user
                }
            }
        } else {
            $error_messages[] = "ID pengguna tidak valid atau koneksi DB bermasalah.";
        }
    }

    // --- LOGIKA UPDATE PENGGUNA (UPDATE) ---
    elseif ($_POST['action'] === 'update_user') {
        $user_id = (int) ($_POST['edit_id'] ?? 0);
        $username = trim($_POST['edit_username'] ?? '');
        $email = trim($_POST['edit_email'] ?? '');
        $password = $_POST['edit_password'] ?? ''; // Boleh kosong
        $role = $_POST['edit_role'] ?? 'user';
        $phone = trim($_POST['edit_phone'] ?? '');

        // Validasi dasar
        if (empty($username) || empty($email)) {
            $error_messages[] = "Field wajib (Username, Email) harus diisi.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_messages[] = "Format email tidak valid.";
        } elseif (!empty($password) && strlen($password) < 6) {
            $error_messages[] = "Password (jika diubah) harus minimal 6 karakter.";
        }

        if (!empty($error_messages)) {
            // Simpan data POST edit ke sesi/variabel untuk 'sticky form' jika ada error
            $_SESSION['edit_error'] = [
                'id' => $user_id,
                'username' => $username,
                'email' => $email,
                'phone' => $phone,
                'role' => $role,
                'messages' => $error_messages // Simpan error
            ];
            // Redirect untuk mempertahankan modal edit tetap terbuka
            header("Location: pengguna.php?edit_id={$user_id}&action=edit_error");
            exit;
        }

        if ($conn && $user_id > 0) {
            // Panggil fungsi untuk memperbarui pengguna
            $result = update_user($conn, $user_id, $username, $email, $password, $error_messages,  $role, $phone,);

            if ($result) {
                // Jika admin mengedit akunnya sendiri, update info sesi
                if ($user_id == ($_SESSION['user_id'] ?? 0)) {
                    $_SESSION['admin_name'] = $username;
                    $_SESSION['admin_email'] = $email;
                }
                $_SESSION['success_message'] = "Pengguna **" . htmlspecialchars($username) . "** berhasil diperbarui.";
                header("Location: pengguna.php");
                exit;
            }
        } else {
            $error_messages[] = "ID pengguna tidak valid atau koneksi DB bermasalah untuk update.";
            // Simpan error ini juga ke sesi agar modal tetap terbuka
            $_SESSION['edit_error'] = [
                'id' => $user_id,
                'username' => $username,
                'email' => $email,
                'phone' => $phone,
                'role' => $role,
                'messages' => $error_messages
            ];
            header("Location: pengguna.php?edit_id={$user_id}&action=edit_error");
            exit;
        }
    }
}


// --- LOGIKA PEMUATAN MODAL EDIT (Jika ada error atau tombol edit diklik) ---
if (isset($_GET['edit_id']) && isset($_GET['action']) && $_GET['action'] === 'edit_error') {
    // Memuat ulang data dari sesi setelah error redirect
    if (isset($_SESSION['edit_error']) && $_SESSION['edit_error']['id'] == $_GET['edit_id']) {
        $user_to_edit = $_SESSION['edit_error'];
        // Pastikan kita hanya menambahkan error yang belum ada
        $error_messages = array_merge($error_messages, $user_to_edit['messages']);
        unset($_SESSION['edit_error']);
        $edit_mode_active = true;
    }
}


// Ambil data default untuk form Tambah jika tidak ada post/error (untuk form Tambah)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || (isset($_POST['action']) && $_POST['action'] !== 'add_user')) {
    $old_post = [
        'username' => '',
        'email' => '',
        'phone' => '',
        'role' => 'user'
    ];
}

// ==========================================================
// 3. LOGIKA PENGAMBILAN DATA (Setelah proses form selesai)
// ==========================================================
if (!empty($db_error)) {
    $error_messages[] = $db_error;
} elseif ($conn) {
    try {
        // Panggil fungsi untuk mengambil daftar semua pengguna
        $users_list = get_all_users($conn, $error_messages);

        // Jika tombol edit diklik via GET, cari data user untuk pre-fill modal edit
        if (isset($_GET['edit_id']) && ($_GET['action'] === 'edit') && !$edit_mode_active) {
            $edit_id = (int) $_GET['edit_id'];
            foreach ($users_list as $user) {
                if ($user['id'] == $edit_id) {
                    $user_to_edit = [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'phone' => $user['phone'],
                        'role' => $user['role'],
                    ];
                    $edit_mode_active = true; // Buka modal edit
                    break;
                }
            }
            if (!$user_to_edit) {
                $error_messages[] = "Data pengguna yang akan diedit (ID #{$edit_id}) tidak ditemukan.";
            }
        }
    } catch (\Throwable $e) {
        $error_messages[] = "Error Umum/Fatal saat fetching data: " . $e->getMessage();
        error_log("Fatal Pengguna Error: " . $e->getMessage());
    }
}

// Gabungkan semua pesan error menjadi satu string untuk tampilan
$display_error_message = implode(". ", $error_messages);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pengguna | Admin KopiSenja</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-dark': '#3b2f2f',
                        'secondary-gold': '#8a6d46',
                        'text-light': '#f2e4cf',
                        'accent-gold': '#c8a66a',
                    },
                }
            }
        }
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #1a1a1a;
        }

        /* Styling tambahan untuk scrollbar agar terlihat lebih elegan */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #3b2f2f;
        }

        ::-webkit-scrollbar-thumb {
            background: #8a6d46;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #c8a66a;
        }

        /* Efek hover pada sidebar item */
        .sidebar-item:hover {
            background-color: #5c422f;
            border-left: 4px solid #c8a66a;
        }

        /* * LOGIKA SIDEBAR */

        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            position: fixed;
            height: 100%;
            z-index: 50;
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .main-content {
            padding-left: 0;
            transition: padding-left 0.3s ease-in-out;
            min-height: 100vh;
        }

        /* MEDIA QUERY UNTUK DESKTOP (> 768px) */
        @media (min-width: 768px) {
            .sidebar {
                transform: translateX(0);
                position: relative;
                z-index: 10;
            }

            .sidebar.is-closed {
                transform: translateX(-100%);
                position: fixed;
            }

            .main-content {
                padding-left: 16rem;
            }

            .main-content.sidebar-closed {
                padding-left: 0;
            }
        }

        /* Modal Custom Style */
        .modal {
            transition: opacity 0.3s ease-in-out;
            display: none;
            /* Default hidden, JS will manage opacity */
        }

        .modal.open {
            display: flex;
            /* Show when open */
        }
    </style>
</head>

<body class="flex min-h-screen">

    <div id="sidebar" class="sidebar bg-primary-dark w-64 space-y-6 py-7 px-2 absolute inset-y-0 left-0 transition duration-200 ease-in-out z-40 shadow-2xl">

        <a href="#" class="text-white flex items-center space-x-2 px-4">
            <i data-lucide="coffee" class="w-8 h-8 text-accent-gold"></i>
            <span class="text-2xl font-extrabold text-text-light">Admin KopiSenja</span>
        </a>

        <nav>
            <a href="dashboard.php" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 text-text-light font-medium border-l-4 border-transparent hover:border-accent-gold flex items-center space-x-2 ">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 text-text-light font-medium border-l-4 border-accent-gold flex items-center space-x-2 bg-[#5c422f]">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span>Pengguna</span>
            </a>
            <a href="products.php" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 text-text-light font-medium border-l-4 border-transparent hover:border-accent-gold flex items-center space-x-2">
                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                <span>Produk</span>
            </a>
            <a href="pesanan.php" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 text-text-light font-medium border-l-4 border-transparent hover:border-accent-gold flex items-center space-x-2">
                <i data-lucide="receipt" class="w-5 h-5"></i>
                <span>Pesanan</span>
            </a>
            <a href="logout.php" class="mt-8 sidebar-item block py-2.5 px-4 rounded transition duration-200 text-red-400 font-medium border-l-4 border-transparent hover:border-red-600 flex items-center space-x-2">
                <i data-lucide="log-out" class="w-5 h-5"></i>
                <span>Logout</span>
            </a>
        </nav>
    </div>

    <div id="main-content" class="main-content flex-1 flex flex-col overflow-hidden">

        <header class="flex items-center p-4 bg-primary-dark shadow-md sticky top-0 z-20">

            <button id="menu-btn" class="text-text-light mr-4 md:mr-8 block">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>

            <h1 class="text-lg sm:text-2xl font-bold text-text-light flex-grow">Manajemen Pengguna</h1>

            <div class="flex items-center space-x-3 ml-auto">
                <span class="text-sm text-gray-400 hidden sm:block"><?= htmlspecialchars($admin_email) ?></span>
                <div class="w-10 h-10 bg-accent-gold rounded-full flex items-center justify-center text-white font-semibold flex-shrink-0">
                    <?= strtoupper(substr($admin_name, 0, 1)) ?>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 md:p-8 bg-[#2c2c2c]">

            <?php if (!empty($display_error_message)): ?>
                <div role="alert" class="bg-red-800 text-white p-4 rounded-lg mb-6 border border-red-600">
                    <div class="flex items-center">
                        <i data-lucide="alert-triangle" class="w-5 h-5 mr-3"></i>
                        <span class="font-semibold">Peringatan/Kesalahan:</span>
                    </div>
                    <p class="text-sm mt-1"><?= htmlspecialchars($display_error_message) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div role="alert" class="bg-green-700 text-white p-4 rounded-lg mb-6 border border-green-500">
                    <div class="flex items-center">
                        <i data-lucide="check-circle" class="w-5 h-5 mr-3"></i>
                        <span class="font-semibold">Sukses!</span>
                    </div>
                    <p class="text-sm mt-1"><?= htmlspecialchars($success_message) ?></p>
                </div>
            <?php endif; ?>

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-text-light">Daftar Semua Pengguna</h2>
                <button id="open-add-modal-btn" class="bg-accent-gold hover:bg-secondary-gold text-primary-dark font-bold py-2 px-4 rounded-lg shadow-md transition duration-300 flex items-center space-x-2">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                    <span>Tambah Pengguna Baru</span>
                </button>
            </div>

            <div class="bg-primary-dark rounded-xl shadow-xl p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead>
                            <tr class="text-text-light/80 text-sm font-semibold uppercase tracking-wider">
                                <th class="px-6 py-3 text-left">ID</th>
                                <th class="px-6 py-3 text-left">Username</th>
                                <th class="px-6 py-3 text-left">Email</th>
                                <th class="px-6 py-3 text-left hidden sm:table-cell">Telepon</th>
                                <th class="px-6 py-3 text-left hidden lg:table-cell">Role</th>
                                <th class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700 text-gray-300">
                            <?php if (empty($users_list)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                        Tidak ada data pengguna yang ditemukan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users_list as $user):
                                    $role = htmlspecialchars($user['role'] ?? 'user');
                                    $role_class = (strtolower($role) == 'admin') ? 'bg-red-900 text-red-300' : ((strtolower($role) == 'kasir') ? 'bg-yellow-900 text-yellow-300' : 'bg-blue-900 text-blue-300');
                                    // Cek apakah user adalah admin yang sedang login
                                    $is_self = ($user['id'] == ($_SESSION['user_id'] ?? 0));
                                ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">#<?= htmlspecialchars($user['id']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-text-light"><?= htmlspecialchars($user['username'] ?? '-') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?= htmlspecialchars($user['email'] ?? '-') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm hidden sm:table-cell"><?= htmlspecialchars($user['phone'] ?? '-') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $role_class ?>">
                                                <?= ucfirst($role) . ($is_self ? ' (Anda)' : '') ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                            <button
                                                title="Edit Pengguna"
                                                class="edit-btn text-secondary-gold hover:text-accent-gold transition duration-150"
                                                data-id="<?= htmlspecialchars($user['id']) ?>"
                                                data-username="<?= htmlspecialchars($user['username']) ?>"
                                                data-email="<?= htmlspecialchars($user['email']) ?>"
                                                data-phone="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                                data-role="<?= htmlspecialchars($user['role'] ?? 'user') ?>">
                                                <i data-lucide="square-pen" class="w-5 h-5"></i>
                                            </button>

                                            <?php if (!$is_self): ?>
                                                <button
                                                    title="Hapus Pengguna"
                                                    class="delete-btn text-red-500 hover:text-red-700 transition duration-150"
                                                    data-id="<?= htmlspecialchars($user['id']) ?>"
                                                    data-username="<?= htmlspecialchars($user['username']) ?>">
                                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                                </button>
                                            <?php else: ?>
                                                <button title="Tidak bisa menghapus diri sendiri" class="text-gray-600 cursor-not-allowed">
                                                    <i data-lucide="lock" class="w-5 h-5"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <?php
    // Tutup koneksi di akhir script jika memang menggunakan prosedur ini
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
    ?>

    <?php
    $add_modal_open_class = (!empty($error_messages) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') ? 'open' : '';
    ?>
    <div id="add-user-modal" class="modal fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-opacity duration-300 z-50 <?= $add_modal_open_class ?>">

        <div class="bg-primary-dark w-full max-w-lg mx-auto rounded-xl shadow-2xl p-6 transform transition-transform duration-300 ease-out 
             <?= $add_modal_open_class ? 'translate-y-0' : 'translate-y-10'; ?>">

            <div class="flex justify-between items-center border-b border-gray-700 pb-3 mb-4">
                <h3 class="text-xl font-bold text-text-light">Tambah Pengguna Baru</h3>
                <button id="close-add-modal-btn" class="text-gray-400 hover:text-white transition duration-150">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form action="pengguna.php" method="POST">

                <input type="hidden" name="action" value="add_user">

                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-300 mb-1">Username</label>
                    <input type="text" id="username" name="username" required
                        class="w-full px-4 py-2 bg-[#2c2c2c] text-text-light border border-gray-600 rounded-lg focus:ring-accent-gold focus:border-accent-gold transition duration-150"
                        placeholder="Masukkan nama pengguna" value="<?= htmlspecialchars($old_post['username']) ?>">
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                    <input type="email" id="email" name="email" required
                        class="w-full px-4 py-2 bg-[#2c2c2c] text-text-light border border-gray-600 rounded-lg focus:ring-accent-gold focus:border-accent-gold transition duration-150"
                        placeholder="contoh@domain.com" value="<?= htmlspecialchars($old_post['email']) ?>">
                </div>

                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium text-gray-300 mb-1">Telepon (Opsional)</label>
                    <input type="text" id="phone" name="phone"
                        class="w-full px-4 py-2 bg-[#2c2c2c] text-text-light border border-gray-600 rounded-lg focus:ring-accent-gold focus:border-accent-gold transition duration-150"
                        placeholder="08xxxxxxxxxx" value="<?= htmlspecialchars($old_post['phone']) ?>">
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-2 bg-[#2c2c2c] text-text-light border border-gray-600 rounded-lg focus:ring-accent-gold focus:border-accent-gold transition duration-150"
                        placeholder="Minimal 6 karakter">
                </div>

                <div class="mb-4">
                    <label for="role" class="block text-sm font-medium text-gray-300 mb-1">Role (Hak Akses)</label>
                    <select id="role" name="role"
                        class="w-full px-4 py-2 bg-[#2c2c2c] text-text-light border border-gray-600 rounded-lg focus:ring-accent-gold focus:border-accent-gold transition duration-150 appearance-none">
                        <option value="user" <?= ($old_post['role'] == 'user') ? 'selected' : ''; ?>>User Biasa (Customer)</option>
                        <option value="admin" <?= ($old_post['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="kasir" <?= ($old_post['role'] == 'kasir') ? 'selected' : ''; ?>>Kasir</option>
                    </select>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-700">
                    <button type="submit" class="bg-accent-gold hover:bg-secondary-gold text-primary-dark font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                        Simpan Pengguna
                    </button>
                </div>

            </form>
        </div>
    </div>

    <?php
    $edit_modal_open_class = $edit_mode_active ? 'open' : '';
    ?>
    <div id="edit-user-modal" class="modal fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-opacity duration-300 z-50 <?= $edit_modal_open_class ?>">

        <div class="bg-primary-dark w-full max-w-lg mx-auto rounded-xl shadow-2xl p-6 transform transition-transform duration-300 ease-out 
             <?= $edit_modal_open_class ? 'translate-y-0' : 'translate-y-10'; ?>">

            <div class="flex justify-between items-center border-b border-gray-700 pb-3 mb-4">
                <h3 class="text-xl font-bold text-text-light">Edit Pengguna #<span id="edit-user-id-display"></span></h3>
                <button id="close-edit-modal-btn" class="text-gray-400 hover:text-white transition duration-150">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form action="pengguna.php" method="POST">

                <input type="hidden" name="action" value="update_user">
                <input type="hidden" id="edit-id" name="edit_id" value="<?= htmlspecialchars($user_to_edit['id'] ?? '') ?>">

                <div class="mb-4">
                    <label for="edit-username" class="block text-sm font-medium text-gray-300 mb-1">Username</label>
                    <input type="text" id="edit-username" name="edit_username" required
                        class="w-full px-4 py-2 bg-[#2c2c2c] text-text-light border border-gray-600 rounded-lg focus:ring-accent-gold focus:border-accent-gold transition duration-150"
                        placeholder="Masukkan nama pengguna" value="<?= htmlspecialchars($user_to_edit['username'] ?? '') ?>">
                </div>

                <div class="mb-4">
                    <label for="edit-email" class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                    <input type="email" id="edit-email" name="edit_email" required
                        class="w-full px-4 py-2 bg-[#2c2c2c] text-text-light border border-gray-600 rounded-lg focus:ring-accent-gold focus:border-accent-gold transition duration-150"
                        placeholder="contoh@domain.com" value="<?= htmlspecialchars($user_to_edit['email'] ?? '') ?>">
                </div>

                <div class="mb-4">
                    <label for="edit-phone" class="block text-sm font-medium text-gray-300 mb-1">Telepon (Opsional)</label>
                    <input type="text" id="edit-phone" name="edit_phone"
                        class="w-full px-4 py-2 bg-[#2c2c2c] text-text-light border border-gray-600 rounded-lg focus:ring-accent-gold focus:border-accent-gold transition duration-150"
                        placeholder="08xxxxxxxxxx" value="<?= htmlspecialchars($user_to_edit['phone'] ?? '') ?>">
                </div>

                <div class="mb-4">
                    <label for="edit-password" class="block text-sm font-medium text-gray-300 mb-1">Ganti Password (Kosongkan jika tidak ingin ganti)</label>
                    <input type="password" id="edit-password" name="edit_password"
                        class="w-full px-4 py-2 bg-[#2c2c2c] text-text-light border border-gray-600 rounded-lg focus:ring-accent-gold focus:border-accent-gold transition duration-150"
                        placeholder="Minimal 6 karakter">
                </div>

                <div class="mb-4">
                    <label for="edit-role" class="block text-sm font-medium text-gray-300 mb-1">Role (Hak Akses)</label>
                    <select id="edit-role" name="edit_role"
                        class="w-full px-4 py-2 bg-[#2c2c2c] text-text-light border border-gray-600 rounded-lg focus:ring-accent-gold focus:border-accent-gold transition duration-150 appearance-none">
                        <option value="user" <?= (isset($user_to_edit['role']) && $user_to_edit['role'] == 'user') ? 'selected' : ''; ?>>User Biasa (Customer)</option>
                        <option value="admin" <?= (isset($user_to_edit['role']) && $user_to_edit['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="kasir" <?= (isset($user_to_edit['role']) && $user_to_edit['role'] == 'kasir') ? 'selected' : ''; ?>>Kasir</option>
                    </select>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-700">
                    <button type="submit" class="bg-accent-gold hover:bg-secondary-gold text-primary-dark font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                        Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>


    <div id="sidebar-overlay" class="fixed inset-0 bg-black opacity-50 z-30 hidden md:hidden"></div>

    <form id="delete-form" action="pengguna.php" method="POST" style="display:none;">
        <input type="hidden" name="action" value="delete_user">
        <input type="hidden" name="user_id" id="delete-user-id">
    </form>

    <script>
        // Inisialisasi Lucide Icons
        lucide.createIcons();

        // ==========================================================
        // SIDEBAR LOGIC (SAMA SEPERTI SEBELUMNYA)
        // ==========================================================

        const menuBtn = document.getElementById('menu-btn');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const mainContent = document.getElementById('main-content');

        function toggleSidebar() {
            const isDesktop = window.innerWidth >= 768;

            if (!isDesktop) {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('hidden');
            } else {
                const isClosed = sidebar.classList.toggle('is-closed');
                mainContent.classList.toggle('sidebar-closed', isClosed);

                if (isClosed) {
                    sidebar.classList.remove('active');
                } else {
                    sidebar.classList.add('active');
                }
            }
        }

        menuBtn.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', () => {
            if (window.innerWidth < 768) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.add('hidden');
            }
        });

        function initializeLayout() {
            const isDesktop = window.innerWidth >= 768;

            if (isDesktop) {
                sidebar.classList.add('active');
                sidebar.classList.remove('is-closed');
                mainContent.classList.remove('sidebar-closed');
            } else {
                sidebar.classList.remove('active');
                sidebar.classList.remove('is-closed');
                mainContent.classList.remove('sidebar-closed');
            }
        }

        document.addEventListener('DOMContentLoaded', initializeLayout);

        window.addEventListener('resize', () => {
            const isDesktop = window.innerWidth >= 768;

            if (isDesktop) {
                sidebar.classList.add('active');
                sidebar.classList.remove('is-closed');
                mainContent.classList.remove('sidebar-closed');
                sidebarOverlay.classList.add('hidden');

            } else {
                sidebar.classList.remove('active');
                sidebar.classList.remove('is-closed');
                mainContent.classList.remove('sidebar-closed');
                sidebarOverlay.classList.add('hidden');
            }
        });

        // ==========================================================
        // MODAL LOGIC (TAMBAH DAN EDIT)
        // ==========================================================

        const addModal = document.getElementById('add-user-modal');
        const openAddModalBtn = document.getElementById('open-add-modal-btn');
        const closeAddModalBtn = document.getElementById('close-add-modal-btn');
        const addModalContent = addModal.querySelector('div');

        const editModal = document.getElementById('edit-user-modal');
        const editBtns = document.querySelectorAll('.edit-btn');
        const closeEditModalBtn = document.getElementById('close-edit-modal-btn');
        const editModalContent = editModal.querySelector('div');

        // Fungsi pembuka/penutup umum
        function openModal(modal, content) {
            modal.classList.add('open');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            setTimeout(() => {
                content.classList.remove('translate-y-10');
            }, 10);
        }

        function closeModal(modal, content) {
            if (modal.classList.contains('open')) {
                content.classList.add('translate-y-10');
                modal.classList.add('opacity-0', 'pointer-events-none');
                setTimeout(() => {
                    modal.classList.remove('open');
                    // Reset URL jika ini adalah modal edit dan dibuka karena error POST
                    if (modal.id === 'edit-user-modal' && window.location.search.includes('edit_id')) {
                        window.history.pushState({}, document.title, "pengguna.php");
                    }
                }, 300);
            }
        }

        // --- Event Listeners Modal Tambah ---
        openAddModalBtn.addEventListener('click', () => openModal(addModal, addModalContent));
        closeAddModalBtn.addEventListener('click', () => closeModal(addModal, addModalContent));
        addModal.addEventListener('click', (e) => {
            if (e.target === addModal) {
                closeModal(addModal, addModalContent);
            }
        });

        // Jika ada error pada modal tambah saat load, pastikan transisi sudah benar
        if (addModal.classList.contains('open')) {
            addModal.classList.remove('opacity-0', 'pointer-events-none');
            addModalContent.classList.remove('translate-y-10');
        }

        // --- Event Listeners Modal Edit ---
        editBtns.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const username = this.dataset.username;
                const email = this.dataset.email;
                const phone = this.dataset.phone;
                const role = this.dataset.role;

                // Isi form modal edit
                document.getElementById('edit-user-id-display').textContent = id;
                document.getElementById('edit-id').value = id;
                document.getElementById('edit-username').value = username;
                document.getElementById('edit-email').value = email;
                document.getElementById('edit-phone').value = phone;
                // Atur role yang terpilih
                document.getElementById('edit-role').value = role;
                // Kosongkan password
                document.getElementById('edit-password').value = '';

                // Ganti URL tanpa reload
                history.pushState(null, null, `pengguna.php?edit_id=${id}&action=edit`);

                openModal(editModal, editModalContent);
            });
        });

        closeEditModalBtn.addEventListener('click', () => closeModal(editModal, editModalContent));
        editModal.addEventListener('click', (e) => {
            if (e.target === editModal) {
                closeModal(editModal, editModalContent);
            }
        });

        // Jika ada error pada modal edit saat load, pastikan modal edit terbuka
        if (editModal.classList.contains('open')) {
            editModal.classList.remove('opacity-0', 'pointer-events-none');
            editModalContent.classList.remove('translate-y-10');
        }

        // ==========================================================
        // DELETE LOGIC (KONFIRMASI)
        // ==========================================================
        const deleteBtns = document.querySelectorAll('.delete-btn');
        const deleteForm = document.getElementById('delete-form');
        const deleteUserIdInput = document.getElementById('delete-user-id');

        deleteBtns.forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.dataset.id;
                const username = this.dataset.username;

                // Konfirmasi sederhana
                const isConfirmed = confirm(`Anda yakin ingin menghapus pengguna:\n\nID: #${userId}\nUsername: ${username}\n\n⚠️ Tindakan ini tidak dapat dibatalkan!`);

                if (isConfirmed) {
                    deleteUserIdInput.value = userId;
                    deleteForm.submit();
                }
            });
        });
    </script>

</body>

</html>