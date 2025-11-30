<?php
session_start();

// Cek 1: Pastikan user sudah login (menggunakan kunci 'user_id' yang benar)
$is_logged_in = isset($_SESSION['user_id']);

// Cek 2: Pastikan user memiliki role sebagai Admin (ID role = 3)
$is_admin = isset($_SESSION['user_role_id']) && $_SESSION['user_role_id'] == 3;

// Jika user TIDAK login ATAU user login TAPI bukan Admin, arahkan kembali.
if (!$is_logged_in || !$is_admin) {
    // Opsional: Anda bisa menambahkan pesan error ke session untuk ditampilkan di halaman login
    $_SESSION['login_message'] = "Akses ditolak. Anda harus login sebagai Admin.";
    header("Location: login.php");
    exit;
}
// ==========================================================
// 1. INCLUDE FILE EXTERNAL: Koneksi dan Fungsi DB
// ==========================================================
// Asumsikan file ini menyediakan koneksi $conn dan mungkin $db_error
include "config.php"; 
// PENTING: File yang berisi fungsi add_user dan get_all_users
include "db_functions.php"; 

// Data pengguna yang sudah login
$admin_name = $_SESSION['admin_name'] ?? "Admin"; // Menggunakan admin_name di sesi
$admin_email = $_SESSION['admin_email'] ?? "admin@example.com";

// Inisialisasi variabel untuk tampilan
$users_list = []; // Daftar semua pengguna
$error_messages = []; // Menggunakan array untuk menampung semua error
$success_message = ""; // Pesan sukses

// ==========================================================
// 2. LOGIKA PEMROSESAN FORMULIR (TAMBAH PENGGUNA)
// ==========================================================
// Simpan data POST lama jika terjadi error agar form tetap 'sticky'
$old_post = [
    'username' => '', 
    'email' => '', 
    'phone' => '', 
    'role' => 'user'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    
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
            // Panggil fungsi untuk menambahkan pengguna (sudah dimodifikasi agar sesuai dengan DB)
            $result = add_user($conn, $username, $email, $password, $role, $phone, $error_messages);
            
            if ($result) {
                // Redirect untuk mencegah resubmission form dan membersihkan POST data
                $_SESSION['success_message'] = "Pengguna **" . htmlspecialchars($username) . "** berhasil ditambahkan.";
                header("Location: pengguna.php");
                exit;
            } else {
                // Pesan error sudah diisi oleh fungsi add_user ke dalam $error_messages
            }
        }
    } else {
        $error_messages[] = "Koneksi database tidak tersedia untuk memproses form.";
    }
} else {
    // Jika bukan POST atau aksi bukan 'add_user', ambil nilai default untuk form kosong
    $old_post = [
        'username' => '', 
        'email' => '', 
        'phone' => '', 
        'role' => 'user'
    ];
}


// Ambil pesan sukses dari sesi jika ada (dari redirect setelah submit berhasil)
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// ==========================================================
// 3. LOGIKA PENGAMBILAN DATA (Setelah proses form selesai)
// ==========================================================
if (!empty($db_error)) {
    // Jika ada error koneksi (dari config.php)
    $error_messages[] = $db_error;
} elseif ($conn) {
    try {
        // Panggil fungsi untuk mengambil daftar semua pengguna (sudah dimodifikasi dengan JOIN)
        $users_list = get_all_users($conn, $error_messages);

    } catch(\Throwable $e) {
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
            display: none; /* Default hidden, JS will manage opacity */
        }
        .modal.open {
             display: flex; /* Show when open */
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
            <a href="#" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 text-text-light font-medium border-l-4 border-transparent hover:border-accent-gold flex items-center space-x-2">
                <i data-lucide="receipt" class="w-5 h-5"></i>
                <span>Pesanan</span>
            </a>
            <a href="#" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 text-text-light font-medium border-l-4 border-transparent hover:border-accent-gold flex items-center space-x-2">
                <i data-lucide="settings" class="w-5 h-5"></i>
                <span>Pengaturan</span>
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
                <button id="open-modal-btn" class="bg-accent-gold hover:bg-secondary-gold text-primary-dark font-bold py-2 px-4 rounded-lg shadow-md transition duration-300 flex items-center space-x-2">
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
                                    // Logika untuk badge role
                                    $role = htmlspecialchars($user['role'] ?? 'user');
                                    // Sesuaikan kelas CSS berdasarkan role name dari tabel roles
                                    $role_class = (strtolower($role) == 'admin') ? 'bg-red-900 text-red-300' : 
                                                  ((strtolower($role) == 'kasir') ? 'bg-yellow-900 text-yellow-300' : 'bg-blue-900 text-blue-300');
                                ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">#<?= htmlspecialchars($user['id']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-text-light"><?= htmlspecialchars($user['username'] ?? '-') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?= htmlspecialchars($user['email'] ?? '-') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm hidden sm:table-cell"><?= htmlspecialchars($user['phone'] ?? '-') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $role_class ?>">
                                                <?= ucfirst($role) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                            <button title="Edit Pengguna" class="text-secondary-gold hover:text-accent-gold transition duration-150">
                                                <i data-lucide="square-pen" class="w-5 h-5"></i>
                                            </button>
                                            <button title="Hapus Pengguna" class="text-red-500 hover:text-red-700 transition duration-150">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
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
        $modal_open_class = (!empty($error_messages) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') ? 'open' : '';
    ?>
    <div id="add-user-modal" class="modal fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-opacity duration-300 z-50 <?= $modal_open_class ?>">
        
        <div class="bg-primary-dark w-full max-w-lg mx-auto rounded-xl shadow-2xl p-6 transform transition-transform duration-300 ease-out 
             <?= (!empty($error_messages) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') ? 'translate-y-0' : 'translate-y-10'; ?>">
            
            <div class="flex justify-between items-center border-b border-gray-700 pb-3 mb-4">
                <h3 class="text-xl font-bold text-text-light">Tambah Pengguna Baru</h3>
                <button id="close-modal-btn" class="text-gray-400 hover:text-white transition duration-150">
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


    <div id="sidebar-overlay" class="fixed inset-0 bg-black opacity-50 z-30 hidden md:hidden"></div>

    <script>
        // Inisialisasi Lucide Icons
        lucide.createIcons();

        // ==========================================================
        // SIDEBAR LOGIC (SAMA DENGAN DASHBOARD.PHP)
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
        // MODAL (POPUP) LOGIC
        // ==========================================================

        const modal = document.getElementById('add-user-modal');
        const openModalBtn = document.getElementById('open-modal-btn');
        const closeModalBtn = document.getElementById('close-modal-btn');
        const modalContent = modal.querySelector('div'); // Ambil div konten modal
        
        // Cek apakah modal harus tetap terbuka setelah POST request dengan error
        const isErrorOnLoad = modal.classList.contains('open');

        // Fungsi untuk membuka modal
        function openModal() {
            modal.classList.add('open');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            // Efek transisi transform (masuk dari atas)
            setTimeout(() => {
                modalContent.classList.remove('translate-y-10');
            }, 10);
        }
        
        // Jika ada error saat memuat halaman, pastikan modal terlihat
        if (isErrorOnLoad) {
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalContent.classList.remove('translate-y-10');
        }

        // Fungsi untuk menutup modal
        function closeModal() {
            // Hapus class 'open' jika ada untuk memastikan penutupan berjalan
            if (modal.classList.contains('open')) {
                modalContent.classList.add('translate-y-10');
                modal.classList.add('opacity-0', 'pointer-events-none');
                // Tunggu transisi selesai sebelum menyembunyikan display
                setTimeout(() => {
                    modal.classList.remove('open');
                }, 300);
            }
        }

        // Event Listeners
        openModalBtn.addEventListener('click', openModal);
        closeModalBtn.addEventListener('click', closeModal);

        // Menutup modal jika mengklik di luar konten modal (overlay)
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });
    </script>

</body>

</html>