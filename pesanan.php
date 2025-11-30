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
$admin_name = $_SESSION['admin_id'] ?? "Admin"; // Menggunakan ID sebagai nama default jika nama tidak ada
$admin_email = $_SESSION['admin_email'] ?? "admin@example.com";

$total_users = "Error"; // Default error value
$sales_today = 0; // Total penjualan hari ini
$sales_alltime = 0; // Total penjualan sepanjang masa (untuk debugging)
$new_products_this_month = 0; // BARU: Produk baru bulan ini
$latest_orders = []; // Default array kosong
$error_message = "";
$sales_today_debug_info = ""; // Untuk menyimpan info debug SQL
// ==========================================================
// 2. LOGIKA CRUD UNTUK ORDERS
// ==========================================================
$success_message = "";
$error_message = "";

// Aksi DELETE
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $order_id_to_delete = (int)$_GET['id'];
    
    // Mulai transaksi untuk memastikan konsistensi
    $conn->begin_transaction();

    try {
        // 1. Hapus entri dari tabel order_details (wajib, karena ada FOREIGN KEY)
        $stmt_detail = $conn->prepare("DELETE FROM detail_orders WHERE order_id = ?");
        $stmt_detail->bind_param("i", $order_id_to_delete);
        $stmt_detail->execute();

        // 2. Hapus entri dari tabel orders
        $stmt_order = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt_order->bind_param("i", $order_id_to_delete);
        $stmt_order->execute();

        if ($stmt_order->affected_rows > 0) {
            $conn->commit();
            $success_message = "Pesanan ID: $order_id_to_delete berhasil dihapus.";
        } else {
            $conn->rollback();
            $error_message = "Gagal menghapus pesanan. Pesanan mungkin tidak ditemukan.";
        }
        
        $stmt_detail->close();
        $stmt_order->close();

    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error: " . $e->getMessage();
    }
}

// Aksi UPDATE STATUS
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    
    // Daftar status yang valid (sesuaikan dengan tabel Anda)
    $valid_statuses = ['confirmed', 'processed', 'delivered', 'cancelled']; 
    if (!in_array($new_status, $valid_statuses)) {
        $error_message = "Status tidak valid.";
    } else {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        
        if ($stmt->execute()) {
            $success_message = "Status Pesanan ID: $order_id berhasil diubah menjadi **" . htmlspecialchars($new_status) . "**.";
        } else {
            $error_message = "Gagal mengubah status: " . $conn->error;
        }
        $stmt->close();
    }
}


// ==========================================================
// 3. AMBIL DATA PESANAN (READ)
// ==========================================================
$orders = [];
// Query menggunakan JOIN untuk mendapatkan nama customer (jika ada tabel users/customers)
// dan nama kasir (jika ada tabel users/kasirs)
$sql = "
    SELECT 
        o.id, 
        o.order_number, 
        o.customer_id, 
        o.kasir_id, 
        o.subtotal, 
        o.total_amount, 
        o.status,
        o.order_type,
        o.customer_name, 
        o.customer_phone, 
        o.notes, 
        o.order_date, 
        o.estimated_ready_time,
        c.name AS customer_name_from_table,
        k.name AS kasir_name
    FROM 
        orders o
    LEFT JOIN 
        users c ON o.customer_id = c.id
    LEFT JOIN 
        users k ON o.kasir_id = k.id
    ORDER BY 
        o.order_date DESC
";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    $result->free();
} else {
    $error_message = "Error saat mengambil data pesanan: " . $conn->error;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pesanan</title>

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
            position: fixed; /* Selalu fixed di mobile */
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
                transform: translateX(0); /* Default terbuka */
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
            <a href="dashboard.php" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 text-text-light font-medium border-l-4 border-transparent hover:border-accent-gold flex items-center space-x-2 ">
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
            <a href="#" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 text-text-light font-medium border-l-4 border-transparent hover:border-accent-gold flex items-center space-x-2 bg-[#5c422f]">
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
         <main class="flex-1 p-6 md:p-10 bg-[#2c2c2c] text-text-light">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-accent-gold flex items-center space-x-2">
                    <i data-lucide="receipt" class="w-8 h-8"></i>
                    <span>Manajemen Pesanan</span>
                </h1>
                </div>

            <?php if ($success_message): ?>
                <div class="bg-green-600/20 text-green-300 p-4 rounded-lg mb-4 flex items-center space-x-3" role="alert">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                    <p><?= htmlspecialchars($success_message) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="bg-red-600/20 text-red-300 p-4 rounded-lg mb-4 flex items-center space-x-3" role="alert">
                    <i data-lucide="x-octagon" class="w-6 h-6"></i>
                    <p><?= htmlspecialchars($error_message) ?></p>
                </div>
            <?php endif; ?>

            <div class="bg-primary-dark p-6 rounded-xl shadow-lg overflow-x-auto">
                
                <?php if (empty($orders)): ?>
                    <p class="text-center text-gray-400 py-10">Tidak ada data pesanan yang ditemukan.</p>
                <?php else: ?>
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="text-left border-b border-secondary-gold/50 text-accent-gold">
                                <th class="py-3 px-2 text-sm font-semibold">ID</th>
                                <th class="py-3 px-2 text-sm font-semibold">No. Pesanan</th>
                                <th class="py-3 px-2 text-sm font-semibold">Customer / No.HP</th>
                                <th class="py-3 px-2 text-sm font-semibold">Kasir</th>
                                <th class="py-3 px-2 text-sm font-semibold">Total</th>
                                <th class="py-3 px-2 text-sm font-semibold">Tipe</th>
                                <th class="py-3 px-2 text-sm font-semibold">Tanggal</th>
                                <th class="py-3 px-2 text-sm font-semibold">Status</th>
                                <th class="py-3 px-2 text-sm font-semibold w-36 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            <?php foreach ($orders as $order): 
                                // Tentukan nama customer yang ditampilkan
                                $display_customer_name = !empty($order['customer_name']) 
                                                        ? htmlspecialchars($order['customer_name']) 
                                                        : (
                                                            !empty($order['customer_name_from_table']) 
                                                            ? htmlspecialchars($order['customer_name_from_table']) 
                                                            : 'Guest'
                                                        );

                                // Fungsi untuk warna status
                                $status_class = match ($order['status']) {
                                    'confirmed' => 'bg-blue-600/50 text-blue-200',
                                    'processed' => 'bg-yellow-600/50 text-yellow-200',
                                    'delivered' => 'bg-green-600/50 text-green-200',
                                    'cancelled' => 'bg-red-600/50 text-red-200',
                                    default => 'bg-gray-600/50 text-gray-200',
                                };

                                // Fungsi untuk memformat mata uang
                                $format_currency = fn($amount) => 'Rp' . number_format($amount, 0, ',', '.');
                            ?>
                                <tr class="hover:bg-primary-dark/80 transition duration-150">
                                    <td class="py-3 px-2 text-sm text-gray-300"><?= $order['id'] ?></td>
                                    <td class="py-3 px-2 text-sm font-medium text-text-light">
                                        <?= htmlspecialchars($order['order_number'] ?? 'N/A') ?>
                                    </td>
                                    <td class="py-3 px-2 text-sm text-gray-300">
                                        <span class="block font-semibold"><?= $display_customer_name ?></span>
                                        <span class="text-xs text-gray-500"><?= htmlspecialchars($order['customer_phone'] ?? '-') ?></span>
                                    </td>
                                    <td class="py-3 px-2 text-sm text-gray-300">
                                        <?= htmlspecialchars($order['kasir_name'] ?? 'System') ?>
                                    </td>
                                    <td class="py-3 px-2 text-sm font-bold text-accent-gold">
                                        <?= $format_currency($order['total_amount']) ?>
                                    </td>
                                    <td class="py-3 px-2 text-sm text-gray-300">
                                        <span class="capitalize"><?= htmlspecialchars($order['order_type'] ?? '-') ?></span>
                                    </td>
                                    <td class="py-3 px-2 text-sm text-gray-300">
                                        <?= date('d M Y H:i', strtotime($order['order_date'])) ?>
                                    </td>
                                    <td class="py-3 px-2 text-sm">
                                        <form method="POST" class="flex flex-col space-y-1">
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <select name="status" class="py-1 px-2 text-xs rounded-lg border border-gray-600 <?= $status_class ?> focus:ring-accent-gold focus:border-accent-gold appearance-none" onchange="this.form.submit()">
                                                <option value="confirmed" <?= $order['status'] == 'confirmed' ? 'selected' : '' ?>>Dikonfirmasi</option>
                                                <option value="processed" <?= $order['status'] == 'processed' ? 'selected' : '' ?>>Diproses</option>
                                                <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Selesai</option>
                                                <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td class="py-3 px-2 text-sm">
                                        <div class="flex justify-center space-x-2">
                                            <a href="# ?>" class="text-blue-400 hover:text-blue-300 transition duration-150 p-1" title="Lihat Detail">
                                                <i data-lucide="eye" class="w-5 h-5"></i>
                                            </a>
                                            <button onclick="confirmDelete(<?= $order['id'] ?>)" class="text-red-400 hover:text-red-300 transition duration-150 p-1" title="Hapus Pesanan">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            </div>
        </main>
        
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
        function confirmDelete(id) {
            if (confirm("Apakah Anda yakin ingin menghapus pesanan dengan ID " + id + "? Tindakan ini tidak dapat dibatalkan dan juga akan menghapus detail pesanan!")) {
                // Arahkan ke URL delete jika dikonfirmasi
                window.location.href = 'orders.php?action=delete&id=' + id;
            }
        }

        // Panggil ulang lucide icons setelah konten dinamis ditambahkan (jika perlu)
        lucide.createIcons();
    </script>

</body>

</html>