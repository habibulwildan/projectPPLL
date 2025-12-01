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
// 1. INCLUDE KONFIGURASI DATABASE (MySQLi)
// Variabel $conn (koneksi MySQLi) akan tersedia setelah include ini
// ==========================================================
// Pastikan file config.php sudah di-include
include "config.php";
// Data pengguna yang sudah login
$admin_name = $_SESSION['user_role'] ?? "Admin"; // Menggunakan ID sebagai nama default jika nama tidak ada
$admin_email = $_SESSION['user_email'] ?? "admin@example.com";

$total_users = "Error"; // Default error value
$sales_today = 0; // Total penjualan hari ini
$sales_alltime = 0; // Total penjualan sepanjang masa (untuk debugging)
$new_products_this_month = 0; // BARU: Produk baru bulan ini
$latest_orders = []; // Default array kosong
$error_message = "";
$sales_today_debug_info = ""; // Untuk menyimpan info debug SQL

// ==========================================================
// 2. LOGIKA PENGAMBILAN DATA (Fetch Data) - MENGGUNAKAN MYSQLi
// ==========================================================
if ($conn) {
    // PENTING: Set zona waktu server MySQLi (misal: WIB = +07:00) 
    // Ini membantu memastikan CURDATE() sesuai dengan waktu yang Anda lihat
    mysqli_query($conn, "SET time_zone = '+07:00'");

    try {
        // --- 2.1. AMBIL TOTAL PENGGUNA ---
        $sql_users = "SELECT COUNT(id) AS total_count FROM users";
        $result_users_query = mysqli_query($conn, $sql_users);

        if ($result_users_query) {
            $result_users = mysqli_fetch_assoc($result_users_query);
            mysqli_free_result($result_users_query);
            $total_users = number_format($result_users['total_count'] ?? 0);
        } else {
            $error_message .= "Kueri Pengguna Gagal: " . mysqli_error($conn) . ". ";
            $total_users = "Error Kueri";
        }

        // --- 2.2. AMBIL TOTAL PENJUALAN HARI INI (Menggunakan order_date) ---
        $sql_sales_today = "SELECT SUM(total_amount) AS total_sales FROM orders WHERE DATE(order_date) = CURDATE()";
        $result_sales_query = mysqli_query($conn, $sql_sales_today);

        if ($result_sales_query) {
            $result_sales = mysqli_fetch_assoc($result_sales_query);
            mysqli_free_result($result_sales_query);

            $sales_today = $result_sales['total_sales'] ?? 0;
            $sales_today_debug_info = "Kueri Hari Ini Berhasil. Total: " . $sales_today;
        } else {
            $sql_error = mysqli_error($conn);
            $error_message .= "Kueri Penjualan Hari Ini GAGAL: " . $sql_error . ". ";
            $sales_today_debug_info = "Kueri Gagal: " . $sql_error;
            $sales_today = 0;
        }

        // --- 2.3. AMBIL TOTAL PENJUALAN KESELURUHAN ---
        $sql_sales_alltime = "SELECT SUM(total_amount) AS total_sales FROM orders";
        $result_alltime_query = mysqli_query($conn, $sql_sales_alltime);

        if ($result_alltime_query) {
            $result_alltime = mysqli_fetch_assoc($result_alltime_query);
            mysqli_free_result($result_alltime_query);
            $sales_alltime = $result_alltime['total_sales'] ?? 0;
        } else {
            $error_message .= "Kueri Total Keseluruhan GAGAL: " . mysqli_error($conn) . ". ";
            $sales_alltime = 0;
        }

        // --- 2.4. BARU: AMBIL TOTAL PRODUK BARU BULAN INI (Menggunakan menus.created_at) ---
        // Mencari semua baris di tabel 'menus' di mana bulan dari created_at sama dengan bulan saat ini
        // Contoh: WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())
        $sql_new_products = "
            SELECT COUNT(id) AS total_new 
            FROM menus 
            WHERE MONTH(created_at) = MONTH(CURDATE()) 
            AND YEAR(created_at) = YEAR(CURDATE())
        ";
        $result_new_products_query = mysqli_query($conn, $sql_new_products);

        if ($result_new_products_query) {
            $result_new_products = mysqli_fetch_assoc($result_new_products_query);
            mysqli_free_result($result_new_products_query);
            $new_products_this_month = $result_new_products['total_new'] ?? 0;
        } else {
            $error_message .= "Kueri Produk Baru GAGAL: " . mysqli_error($conn) . ". ";
            $new_products_this_month = 0;
        }

        // --- 2.5. AMBIL PESANAN TERBARU ---
        $sql_orders = "SELECT orders.id, order_number, users.name, status, total_amount, order_date FROM orders, users WHERE orders.customer_id = users.id ORDER BY order_date DESC LIMIT 3";
        $result_orders_query = mysqli_query($conn, $sql_orders);

        if ($result_orders_query) {
            while ($row = mysqli_fetch_assoc($result_orders_query)) {
                $latest_orders[] = [
                    'id' => $row['order_number'],
                    'pelanggan' => $row['name'],
                    'status' => $row['status'],
                    'total' => $row['total_amount'],
                ];
            }
            mysqli_free_result($result_orders_query);
        } else {
            $error_message .= "Kueri Pesanan Gagal: Cek kolom 'customer_name' dan 'total_amount'. Pesan error SQL: " . mysqli_error($conn);
            $latest_orders = [];
        }
    } catch (\Throwable $e) {
        // Tangani error umum (termasuk error koneksi PHP)
        $error_message = "Error Umum/Fatal: " . $e->getMessage();
        error_log($error_message);
        $total_users = "Error Fatal";
        $sales_today = 0;
        $sales_alltime = 0;
        $new_products_this_month = 0;
        $latest_orders = [];
    }
} else {
    // Menangani koneksi yang benar-benar gagal dari config.php
    $error_message = "Koneksi database GAGAL! Cek config.php.";
    $total_users = "Error Config";
    $sales_today = 0;
    $sales_alltime = 0;
    $new_products_this_month = 0;
}

// Tutup koneksi MySQLi jika berhasil dibuat
if (isset($conn) && $conn) {
    mysqli_close($conn);
}

// Fungsi pembantu untuk memformat nilai rupiah dengan 'juta' jika angkanya besar
function format_rupiah_sales($number)
{
    // Pastikan input adalah angka
    if (!is_numeric($number)) return 'Rp 0';

    if ($number >= 1000000) {
        // Format ke 'X Juta'
        return 'Rp ' . number_format($number / 1000000, 1, ',', '.') . ' Juta';
    } else {
        // Format normal
        return 'Rp ' . ' ' . number_format($number, 0, ',', '.');
    }
}

// Konversi total penjualan hari ini dan keseluruhan ke format Rupiah yang mudah dibaca
$sales_today_formatted = format_rupiah_sales($sales_today);
$sales_alltime_formatted = format_rupiah_sales($sales_alltime);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Admin</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-dark': '#3b2f2f', // Senada dengan via-[#3b2f2f]
                        'secondary-gold': '#8a6d46', // Senada dengan to-[#8a6d46]
                        'text-light': '#f2e4cf',
                        'accent-gold': '#c8a66a',
                    },
                }
            }
        }
    </script>

    <!-- Google Font & Icons (Lucide) -->
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

        /* * LOGIKA SIDEBAR
         */

        .sidebar {
            /* Default Mobile: Sidebar tersembunyi di luar layar */
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            position: fixed;
            /* Selalu fixed di mobile */
            height: 100%;
            z-index: 50;
        }

        .sidebar.active {
            /* Ketika 'active', muncul di layar (baik mobile/desktop) */
            transform: translateX(0);
        }

        .main-content {
            /* Default padding untuk desktop (akan diubah oleh JS di mobile) */
            padding-left: 0;
            transition: padding-left 0.3s ease-in-out;
            min-height: 100vh;
        }

        /* MEDIA QUERY UNTUK DESKTOP (> 768px) */
        @media (min-width: 768px) {
            .sidebar {
                /* Default Desktop: Terbuka dan memengaruhi layout (relatif) */
                transform: translateX(0);
                /* Default terbuka */
                position: relative;
                z-index: 10;
            }

            /* Sidebar Tertutup di Desktop */
            .sidebar.is-closed {
                /* Atur menjadi fixed dan sembunyikan sepenuhnya (untuk toggle) */
                transform: translateX(-100%);
                position: fixed;
            }

            .main-content {
                /* Default Desktop: Padding sebesar lebar sidebar (w-64 = 16rem = 256px) */
                padding-left: 16rem;
            }

            /* Main Content Sidebar Tertutup */
            .main-content.sidebar-closed {
                /* Ketika sidebar ditutup, hapus padding-left */
                padding-left: 0;
            }
        }
    </style>
</head>

<body class="flex min-h-screen">

    <!-- Sidebar (Primary Dark) -->
    <div id="sidebar" class="sidebar bg-primary-dark w-64 space-y-6 py-7 px-2 absolute inset-y-0 left-0 transition duration-200 ease-in-out z-40 shadow-2xl">

        <!-- Logo/Judul -->
        <a href="#" class="text-white flex items-center space-x-2 px-4">
            <i data-lucide="coffee" class="w-8 h-8 text-accent-gold"></i>
            <span class="text-2xl font-extrabold text-text-light">Admin KopiSenja</span>
        </a>

        <!-- Navigasi -->
        <nav>
            <a href="#" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 text-text-light font-medium border-l-4 border-transparent hover:border-accent-gold flex items-center space-x-2 bg-[#5c422f]">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span>Dashboard</span>
            </a>
            <a href="pengguna.php" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 text-text-light font-medium border-l-4 border-transparent hover:border-accent-gold flex items-center space-x-2">
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

    <!-- Main Content -->
    <div id="main-content" class="main-content flex-1 flex flex-col overflow-hidden">

        <!-- Header Top Bar -->
        <header class="flex items-center p-4 bg-primary-dark shadow-md sticky top-0 z-20">

            <!-- Mobile Menu Button (Selalu terlihat di mobile & desktop) -->
            <button id="menu-btn" class="text-text-light mr-4 md:mr-8 block">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>

            <!-- Judul (Ambil sisa ruang, kecuali untuk info user) -->


            <!-- User Info and Avatar -->
            <div class="flex items-center space-x-3 ml-auto">
                <span class="text-sm text-gray-400 hidden sm:block"><?= htmlspecialchars($admin_email) ?></span>
                <div class="w-10 h-10 bg-accent-gold rounded-full flex items-center justify-center text-white font-semibold flex-shrink-0">
                    <?= strtoupper(substr($admin_name, 0, 1)) ?>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 md:p-8 bg-[#2c2c2c]">

            <?php if (!empty($error_message)): ?>
                <!-- Alert/Pesan Error di Atas Tabel -->
                <div role="alert" class="bg-red-800 text-white p-4 rounded-lg mb-6 border border-red-600">
                    <div class="flex items-center">
                        <i data-lucide="alert-triangle" class="w-5 h-5 mr-3"></i>
                        <span class="font-semibold">Peringatan Database:</span>
                    </div>
                    <p class="text-sm mt-1"><?= htmlspecialchars($error_message) ?></p>
                </div>
            <?php endif; ?>

            <!-- DEBUG ALERT: Hanya untuk membantu Anda melihat apa yang terjadi -->
            <!-- <div role="alert" class="bg-yellow-800 text-white p-4 rounded-lg mb-6 border border-yellow-600">
                <p class="font-semibold">INFO DEBUG PENJUALAN:</p>
                <ul class="list-disc list-inside text-sm mt-1">
                    <li>**Total Penjualan Sepanjang Masa:** <?= htmlspecialchars($sales_alltime_formatted) ?></li>
                    <li>**Info Kueri Hari Ini (Filter order_date):** <?= htmlspecialchars($sales_today_debug_info) ?></li>
                    <li>**CATATAN PENTING:** Kueri kini menggunakan kolom **`order_date`** (bukan `created_at`) untuk menghitung Penjualan Hari Ini.</li>
                </ul>
            </div> -->
            <!-- END DEBUG ALERT -->

            <!-- Cards Statistik -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                <!-- Card 1: Total Pengguna (DARI DATABASE) -->
                <div class="bg-primary-dark p-6 rounded-xl shadow-lg border-t-4 border-accent-gold transform hover:scale-[1.02] transition duration-300">
                    <div class="flex justify-between items-center">
                        <!-- TAMPILKAN DATA TOTAL PENGGUNA -->
                        <span class="text-2xl font-bold text-text-light"><?= htmlspecialchars($total_users) ?></span>
                        <i data-lucide="users" class="w-8 h-8 text-secondary-gold opacity-75"></i>
                    </div>
                    <p class="text-gray-400 mt-1">Total Pengguna</p>
                </div>

                <!-- Card 2: Penjualan Hari Ini (DARI DATABASE) -->
                <div class="bg-primary-dark p-6 rounded-xl shadow-lg border-t-4 border-accent-gold transform hover:scale-[1.02] transition duration-300">
                    <div class="flex justify-between items-center">
                        <!-- TAMPILKAN DATA TOTAL PENJUALAN HARI INI -->
                        <span class="text-2xl font-bold text-text-light"><?= htmlspecialchars($sales_today_formatted) ?></span>
                        <i data-lucide="wallet" class="w-8 h-8 text-secondary-gold opacity-75"></i>
                    </div>
                    <p class="text-gray-400 mt-1">Penjualan Hari Ini</p>
                </div>

                <!-- Card 3: Produk Baru BULAN INI (DARI DATABASE menus) -->
                <div class="bg-primary-dark p-6 rounded-xl shadow-lg border-t-4 border-accent-gold transform hover:scale-[1.02] transition duration-300">
                    <div class="flex justify-between items-center">
                        <!-- TAMPILKAN DATA TOTAL PRODUK BARU BULAN INI -->
                        <span class="text-2xl font-bold text-text-light"><?= htmlspecialchars(number_format($new_products_this_month)) ?></span>
                        <i data-lucide="package" class="w-8 h-8 text-secondary-gold opacity-75"></i>
                    </div>
                    <p class="text-gray-400 mt-1">Produk Baru Bulan Ini</p>
                </div>

                <!-- Card 4: Feedback (Placeholder) -->
                <div class="bg-primary-dark p-6 rounded-xl shadow-lg border-t-4 border-accent-gold transform hover:scale-[1.02] transition duration-300">
                    <div class="flex justify-between items-center">
                        <span class="text-2xl font-bold text-text-light">98%</span>
                        <i data-lucide="message-square" class="w-8 h-8 text-secondary-gold opacity-75"></i>
                    </div>
                    <p class="text-gray-400 mt-1">Kepuasan Pelanggan</p>
                </div>
            </div>



            <!-- Bagian Tabel Pesanan Terbaru (DARI DATABASE) -->
            <div class="bg-primary-dark rounded-xl shadow-xl p-6">
                <h2 class="text-xl font-semibold mb-4 text-text-light border-b border-secondary-gold/50 pb-2">Pesanan Terbaru</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead>
                            <tr class="text-text-light/80 text-sm font-semibold uppercase tracking-wider">
                                <th class="px-6 py-3 text-left">ID Pesanan</th>
                                <th class="px-6 py-3 text-left">Pelanggan</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700 text-gray-300">
                            <?php if (empty($latest_orders)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                        Tidak ada data pesanan yang ditemukan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($latest_orders as $order):
                                    // Logika untuk menentukan warna badge status
                                    $badge_class = 'bg-gray-700 text-gray-300';
                                    switch (strtolower($order['status'])) {
                                        case 'selesai':
                                            $badge_class = 'bg-green-900 text-green-300';
                                            break;
                                        case 'diproses':
                                            $badge_class = 'bg-yellow-900 text-yellow-300';
                                            break;
                                        case 'dibatalkan':
                                            $badge_class = 'bg-red-900 text-red-300';
                                            break;
                                        default:
                                            $badge_class = 'bg-blue-900 text-blue-300'; // Default warna lain
                                            break;
                                    }

                                ?>
                                    <tr>
                                        <!-- Menggunakan key 'id', 'pelanggan', 'status', dan 'total' dari array hasil mapping di atas -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">#<?= htmlspecialchars($order['id'] ?? '') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?= htmlspecialchars($order['pelanggan'] ?? '') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $badge_class ?>">
                                                <?= htmlspecialchars($order['status'] ?? '') ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">Rp <?= number_format($order['total'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- Mobile Menu Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black opacity-50 z-30 hidden md:hidden"></div>

    <script>
        // Inisialisasi Lucide Icons
        lucide.createIcons();

        // Elemen DOM
        const menuBtn = document.getElementById('menu-btn');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const mainContent = document.getElementById('main-content');

        // Fungsi untuk membuka/menutup sidebar
        function toggleSidebar() {
            const isDesktop = window.innerWidth >= 768;

            if (!isDesktop) {
                // LOGIKA MOBILE: Menggunakan 'active' untuk transform dan overlay
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('hidden');
            } else {
                // LOGIKA DESKTOP: Menggunakan 'is-closed' pada sidebar dan 'sidebar-closed' pada main content

                // Toggle kelas untuk sidebar (mengubah dari relatif/terbuka menjadi fixed/tersembunyi)
                const isClosed = sidebar.classList.toggle('is-closed');

                // Toggle kelas untuk main content (mengubah padding)
                mainContent.classList.toggle('sidebar-closed', isClosed);

                // Tambahkan/hapus kelas 'active' hanya untuk konsistensi CSS
                if (isClosed) {
                    sidebar.classList.remove('active');
                } else {
                    sidebar.classList.add('active');
                }
            }
        }

        // Event Listener untuk tombol menu
        menuBtn.addEventListener('click', toggleSidebar);

        // Event Listener untuk overlay (hanya di mobile)
        sidebarOverlay.addEventListener('click', () => {
            if (window.innerWidth < 768) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.add('hidden');
            }
        });

        // Fungsi inisialisasi pada saat load
        function initializeLayout() {
            const isDesktop = window.innerWidth >= 768;

            if (isDesktop) {
                // DESKTOP: Default Terbuka
                sidebar.classList.add('active');
                sidebar.classList.remove('is-closed');
                mainContent.classList.remove('sidebar-closed');
            } else {
                // MOBILE: Default Tertutup
                sidebar.classList.remove('active');
                sidebar.classList.remove('is-closed');
                mainContent.classList.remove('sidebar-closed'); // Pastikan tidak ada padding desktop
            }
        }

        document.addEventListener('DOMContentLoaded', initializeLayout);

        // Logika untuk menangani perubahan ukuran layar
        window.addEventListener('resize', () => {
            const isDesktop = window.innerWidth >= 768;

            if (isDesktop) {
                // Transisi ke Desktop:
                sidebar.classList.add('active');
                sidebar.classList.remove('is-closed');
                mainContent.classList.remove('sidebar-closed');
                sidebarOverlay.classList.add('hidden');

            } else {
                // Transisi ke Mobile:
                sidebar.classList.remove('active');
                sidebar.classList.remove('is-closed');
                mainContent.classList.remove('sidebar-closed');
                sidebarOverlay.classList.add('hidden');
            }
        });
    </script>

</body>

</html>