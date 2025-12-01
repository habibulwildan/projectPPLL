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
// ==========================================================
include "config.php";

// Data pengguna yang sudah login
$admin_name = $_SESSION['admin_id'] ?? "Admin";
$admin_email = $_SESSION['admin_email'] ?? "admin@example.com";

// ==========================================================
// 2. INISIALISASI FEEDBACK DAN PENGATURAN GLOBAL
// ==========================================================
$feedback_message = '';
$feedback_type = '';

// KONFIGURASI UNGGAH GAMBAR
$upload_dir = __DIR__ . '/../public/images/menus/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}


// ==========================================================
// 3. PENANGANAN FEEDBACK SETELAH REDIRECT (PRG)
// ==========================================================
if (isset($_GET['feedback_type']) && isset($_GET['feedback_message'])) {
    $feedback_type = $_GET['feedback_type'];
    $feedback_message = urldecode($_GET['feedback_message']);

    // (Opsional) Membersihkan URL setelah ditampilkan, agar refresh halaman tidak memunculkan pesan lagi.
    // window.history.replaceState(null, null, 'products.php'); // Akan ditangani di JS
}


// ==========================================================
// 4. DEFINISI FUNGSI
// ==========================================================

// FUNGSI UNTUK MENGAMBIL DATA KATEGORI (READ)
function get_all_categories(mysqli $conn): array
{
    $categories = [];
    $sql = "SELECT id, name, description, is_active FROM categories ORDER BY id ASC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        $result->free();
    }
    return $categories;
}

// FUNGSI UNTUK MENGAMBIL DATA MENU (PRODUK) - READ
function get_all_menus(mysqli $conn): array
{
    $menus = [];
    $sql = "SELECT M.id, M.name, M.description, M.price, M.stock, C.name AS category_name, M.category_id, M.image 
             FROM menus M
             JOIN categories C ON M.category_id = C.id
             ORDER BY M.id DESC";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $menus[] = $row;
        }
        $result->free();
    }
    return $menus;
}

// FUNGSI UNTUK MENGURUS UNGGAHAN GAMBAR
function handle_image_upload($file_array, $upload_dir, $old_image_name = null)
{
    global $feedback_message, $feedback_type;
    // ... (Isi fungsi handle_image_upload yang sudah benar, seperti di balasan sebelumnya) ...
    $new_file_name = $old_image_name;

    if ($file_array['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $file_array['tmp_name'];
        $file_ext = strtolower(pathinfo($file_array['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($file_ext, $allowed_ext)) {
            $feedback_message = "Format file tidak diizinkan. Hanya JPG, JPEG, PNG, atau WEBP.";
            $feedback_type = 'error';
            return false;
        }

        if ($old_image_name && file_exists($upload_dir . $old_image_name)) {
            // Hapus file lama hanya jika file baru berhasil diunggah
            // Hapus di sini untuk memastikan nama file lama sudah diganti sebelum operasi DB
            unlink($upload_dir . $old_image_name);
        }

        $new_file_name = uniqid('menu_', true) . '.' . $file_ext;
        $file_destination = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp, $file_destination)) {
            return $new_file_name;
        } else {
            $feedback_message = "Gagal memindahkan file yang diunggah.";
            $feedback_type = 'error';
            return false;
        }
    }

    return $new_file_name;
}

// ==========================================================
// 5. LOGIKA CRUD (KATEGORI & MENU)
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- LOGIKA CRUD KATEGORI ---
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $id = $_POST['category_id'] ?? null;

        $stmt = null;
        $success = false;
        $message = "";

        // ... (Logika ADD, EDIT, DELETE KATEGORI yang sudah ada) ...
        // ----------------- CREATE / TAMBAH -----------------
        if ($action === 'add' && !empty($name)) {
            $sql = "INSERT INTO categories (name, description, is_active, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW())";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ss", $name, $description);
                $success = $stmt->execute();
                $message = $success ? "Kategori berhasil ditambahkan!" : "Gagal menambahkan kategori: " . $stmt->error;
            }
        }

        // ----------------- UPDATE / EDIT -----------------
        if ($action === 'edit' && $id !== null && !empty($name)) {
            $sql = "UPDATE categories SET name = ?, description = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssi", $name, $description, $id);
                $success = $stmt->execute();
                $message = $success ? "Kategori ID {$id} berhasil diperbarui!" : "Gagal memperbarui kategori: " . $stmt->error;
            }
        }

        // ----------------- DELETE / HAPUS -----------------
        if ($action === 'delete' && $id !== null) {
            $sql = "DELETE FROM categories WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $id);
                $success = $stmt->execute();
                $message = $success ? "Kategori ID {$id} berhasil dihapus!" : "Gagal menghapus kategori: " . $stmt->error;
            }
        }
        // END CRUD KATEGORI

        if ($stmt) {
            $stmt->close();
        }

        // REDIRECT PRG UNTUK KATEGORI
        header("Location: products.php?tab=categories&feedback_type=" . ($success ? 'success' : 'error') . "&feedback_message=" . urlencode($message));
        exit();
    }


    // --- LOGIKA CRUD MENU (PRODUK) ---
    if (isset($_POST['menu_action'])) {

        $action = $_POST['menu_action'] ?? '';
        $menu_id = $_POST['menu_id'] ?? null;
        $name = $_POST['menu_name'] ?? '';
        $description = $_POST['menu_description'] ?? '';
        $price = $_POST['menu_price'] ?? 0;
        $stock = $_POST['menu_stock'] ?? 0;
        $category_id = $_POST['menu_category_id'] ?? 0;
        $old_image = $_POST['old_image'] ?? null;

        $stmt = null;
        $success = false;
        $message = "";
        $uploaded_image_name = $old_image;

        // PENANGANAN UNGGAH GAMBAR
        if (isset($_FILES['menu_image']) && $_FILES['menu_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploaded_image_name = handle_image_upload($_FILES['menu_image'], $upload_dir, $old_image);

            if ($uploaded_image_name === false && $_FILES['menu_image']['error'] === UPLOAD_ERR_OK) {
                goto end_crud_menu;
            }
        }

        // ----------------- CREATE / TAMBAH MENU -----------------
        if ($action === 'add' && !empty($name)) {
            $sql = "INSERT INTO menus (name, description, price, stock, category_id, image, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssdiis", $name, $description, $price, $stock, $category_id, $uploaded_image_name);
                $success = $stmt->execute();
                $message = $success ? "Produk **{$name}** berhasil ditambahkan!" : "Gagal menambahkan produk: " . $stmt->error;
            }
        }

        // ----------------- UPDATE / EDIT MENU -----------------
        if ($action === 'edit' && $menu_id !== null && !empty($name)) {
            $sql = "UPDATE menus SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssdiisi", $name, $description, $price, $stock, $category_id, $uploaded_image_name, $menu_id);
                $success = $stmt->execute();
                $message = $success ? "Produk ID {$menu_id} berhasil diperbarui!" : "Gagal memperbarui produk: " . $stmt->error;
            }
        }

        // ----------------- DELETE / HAPUS MENU -----------------
        if ($action === 'delete' && $menu_id !== null) {
            // Ambil nama gambar sebelum dihapus
            $stmt_img = $conn->prepare("SELECT image FROM menus WHERE id = ?");
            $stmt_img->bind_param("i", $menu_id);
            $stmt_img->execute();
            $result_img = $stmt_img->get_result();
            $old_img_name_to_delete = $result_img->fetch_assoc()['image'] ?? null;
            $stmt_img->close();

            $sql = "DELETE FROM menus WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $menu_id);
                $success = $stmt->execute();

                // Hapus file gambar dari server
                if ($success && $old_img_name_to_delete && file_exists($upload_dir . $old_img_name_to_delete)) {
                    unlink($upload_dir . $old_img_name_to_delete);
                }
                $message = $success ? "Produk ID {$menu_id} berhasil dihapus!" : "Gagal menghapus produk: " . $stmt->error;
            }
        }

        // ➡️ LABEL UNTUK GOTO (HARUS DI SINI) ⬅️
        end_crud_menu:

        if ($stmt) {
            $stmt->close();
        }

        // REDIRECT PRG UNTUK MENU
        header("Location: products.php?tab=products&feedback_type=" . ($success ? 'success' : 'error') . "&feedback_message=" . urlencode($message));
        exit();
    }
}

// ==========================================================
// 6. AMBIL DATA UNTUK DITAMPILKAN
// Dijalankan setelah semua Logika CRUD selesai (atau tidak ada POST)
// ==========================================================
$categories = get_all_categories($conn);
$menus = get_all_menus($conn);

// Hentikan Output Buffering dan kirim output ke browser
if (ob_get_level() > 0) {
    ob_end_flush(); // Hanya jalankan jika ada buffer aktif
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>products</title>

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

        /* ... (Bagian Desktop @media) ... */
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
    <div id="sidebar" class="sidebar bg-primary-dark w-64 space-y-6 py-7 px-2 fixed h-screen left-0 transition duration-200 ease-in-out z-40 shadow-2xl overflow-y-auto">

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
            <a href="#" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 text-text-light font-medium border-l-4 border-transparent hover:border-accent-gold flex items-center space-x-2 bg-[#5c422f]">
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
            <h1 class="text-3xl font-extrabold text-accent-gold mb-6 border-b-2 border-secondary-gold pb-2">
                <i data-lucide="shopping-bag" class="w-8 h-8 inline mr-2"></i> Manajemen Produk & Kategori
            </h1>

            <?php
            // Display Feedback Message
            if ($feedback_message) {
                $bg_color = ($feedback_type === 'success') ? 'bg-green-600' : 'bg-red-600';
                $border_color = ($feedback_type === 'success') ? 'border-green-800' : 'border-red-800';
                echo "<div class='p-4 mb-4 text-white {$bg_color} border-l-4 {$border_color} rounded shadow-lg' role='alert'>
                        {$feedback_message}
                    </div>";
            }
            ?>

            <div class="mb-4 border-b border-gray-700">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="tab-controls" role="tablist">
                    <li class="mr-2" role="presentation">
                        <button id="products-tab" onclick="switchTab('products')" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg text-text-light hover:text-accent-gold hover:border-accent-gold active-tab" type="button" role="tab" aria-controls="products" aria-selected="true">Produk (Menus)</button>
                    </li>
                    <li class="mr-2" role="presentation">
                        <button id="categories-tab" onclick="switchTab('categories')" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg text-gray-400 hover:text-accent-gold hover:border-accent-gold" type="button" role="tab" aria-controls="categories" aria-selected="false">Kategori</button>
                    </li>
                </ul>
            </div>

            <div id="products-content" class="tab-content">
                <div class="flex justify-end mb-4">
                    <button onclick="openMenuModal('add')" class="bg-secondary-gold hover:bg-accent-gold text-primary-dark font-bold py-2 px-4 rounded-lg flex items-center shadow-lg transition duration-200">
                        <i data-lucide="plus-circle" class="w-5 h-5 mr-2"></i> Tambah Produk Baru
                    </button>
                </div>

                <div class="bg-primary-dark p-6 rounded-lg shadow-xl overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Nama Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Harga</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Stok</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Image</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800 text-text-light">
                            <?php if (empty($menus)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm font-medium">Belum ada data produk.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($menus as $menu): ?>
                                    <tr data-id="<?= $menu['id'] ?>"
                                        data-name="<?= htmlspecialchars($menu['name']) ?>"
                                        data-description="<?= htmlspecialchars($menu['description']) ?>"
                                        data-price="<?= $menu['price'] ?>"
                                        data-stock="<?= $menu['stock'] ?>"
                                        data-category-id="<?= $menu['category_id'] ?>">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><?= $menu['id'] ?></td>
                                        <td class="px-6 py-4 text-sm"><?= htmlspecialchars($menu['name']) ?></td>
                                        <td class="px-6 py-4 text-sm"><?= htmlspecialchars($menu['category_name']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">Rp. <?= number_format($menu['price'], 0, ',', '.') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?= $menu['stock'] ?></td>
                                        <td>
                                            <?php if (!empty($menu['image'])): ?>
                                                <img src="../public/images/menus/<?php echo htmlspecialchars($menu['image']); ?>"
                                                    alt="<?php echo htmlspecialchars($menu['name']); ?>"
                                                    style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                            <?php else: ?>
                                                <i class="fas fa-image" title="Tidak ada gambar"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="openMenuModal('edit', this)" class="text-secondary-gold hover:text-accent-gold mr-3">
                                                <i data-lucide="square-pen" class="w-5 h-5"></i>
                                            </button>
                                            <button onclick="confirmMenuDelete(<?= $menu['id'] ?>)" class="text-red-500 hover:text-red-700">
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
            <div id="categories-content" class="tab-content hidden">
                <div class="flex justify-end mb-4">
                    <button onclick="openCategoryModal('add')" class="bg-secondary-gold hover:bg-accent-gold text-primary-dark font-bold py-2 px-4 rounded-lg flex items-center shadow-lg transition duration-200">
                        <i data-lucide="plus-circle" class="w-5 h-5 mr-2"></i> Tambah Kategori
                    </button>
                </div>

                <div class="bg-primary-dark p-6 rounded-lg shadow-xl overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Nama Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Deskripsi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800 text-text-light">
                            <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm font-medium">Belum ada data kategori.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categories as $category): ?>
                                    <tr data-id="<?= $category['id'] ?>"
                                        data-name="<?= htmlspecialchars($category['name']) ?>"
                                        data-description="<?= htmlspecialchars($category['description']) ?>">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><?= $category['id'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?= htmlspecialchars($category['name']) ?></td>
                                        <td class="px-6 py-4 text-sm max-w-xs truncate"><?= htmlspecialchars($category['description']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $category['is_active'] ? 'bg-green-500 text-green-900' : 'bg-red-500 text-red-900' ?>">
                                                <?= $category['is_active'] ? 'Aktif' : 'Non-aktif' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="openCategoryModal('edit', this)" class="text-secondary-gold hover:text-accent-gold mr-3">
                                                <i data-lucide="square-pen" class="w-5 h-5"></i>
                                            </button>
                                            <button onclick="confirmCategoryDelete(<?= $category['id'] ?>)" class="text-red-500 hover:text-red-700">
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
        <hr>

        <div id="category-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-75">
            <div class="bg-primary-dark p-6 rounded-lg shadow-2xl w-full max-w-md mx-4 transition-all transform scale-95 duration-300">
                <div class="flex justify-between items-center border-b border-gray-700 pb-3 mb-4">
                    <h3 id="category-modal-title" class="text-xl font-bold text-accent-gold">Tambah Kategori</h3>
                    <button onclick="closeCategoryModal()" class="text-gray-400 hover:text-white">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <form method="POST" id="category-crud-form">
                    <input type="hidden" name="action" id="category-modal-action">
                    <input type="hidden" name="category_id" id="category-modal-id">

                    <div class="mb-4">
                        <label for="category-name" class="block text-sm font-medium text-text-light mb-1">Nama Kategori</label>
                        <input type="text" name="name" id="category-name" required class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-accent-gold">
                    </div>

                    <div class="mb-6">
                        <label for="category-description" class="block text-sm font-medium text-text-light mb-1">Deskripsi</label>
                        <textarea name="description" id="category-description" rows="3" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-accent-gold"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeCategoryModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-500 transition duration-200">Batal</button>
                        <button type="submit" id="category-submit-btn" class="px-4 py-2 bg-secondary-gold text-primary-dark font-semibold rounded-lg hover:bg-accent-gold transition duration-200">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="menu-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-75">
            <div class="bg-primary-dark p-6 rounded-lg shadow-2xl w-full max-w-lg mx-4 transition-all transform scale-95 duration-300">
                <div class="flex justify-between items-center border-b border-gray-700 pb-3 mb-4">
                    <h3 id="menu-modal-title" class="text-xl font-bold text-accent-gold">Tambah Produk</h3>
                    <button onclick="closeMenuModal()" class="text-gray-400 hover:text-white">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <form method="POST" id="menu-crud-form" action="products.php" enctype="multipart/form-data"> <input type="hidden" name="menu_action" id="menu-modal-action">
                    <input type="hidden" name="menu_id" id="menu-modal-id">
                    <input type="hidden" name="old_image" id="menu-old-image">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="menu-name" class="block text-sm font-medium text-text-light mb-1">Nama Produk</label>
                            <input type="text" name="menu_name" id="menu-name" required class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-accent-gold">
                        </div>

                        <div class="mb-4">
                            <label for="menu-category-id" class="block text-sm font-medium text-text-light mb-1">Kategori</label>
                            <select name="menu_category_id" id="menu-category-id" required class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-accent-gold">
                                <option value="">-- Pilih Kategori --</option>
                                <?php
                                // Loop untuk mengisi dropdown kategori
                                foreach ($categories as $cat) {
                                    echo '<option value="' . $cat['id'] . '">' . htmlspecialchars($cat['name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="menu-price" class="block text-sm font-medium text-text-light mb-1">Harga (Rp)</label>
                            <input type="number" name="menu_price" id="menu-price" required step="1000" min="0" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-accent-gold">
                        </div>

                        <div class="mb-4">
                            <label for="menu-stock" class="block text-sm font-medium text-text-light mb-1">Stok</label>
                            <input type="number" name="menu_stock" id="menu-stock" required min="0" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-accent-gold">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="menu-image" class="block text-sm font-medium text-text-light mb-1">Upload Gambar</label>
                        <input type="file" name="menu_image" id="menu-image" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-accent-gold" accept="image/*">
                        <div id="current-image-preview" class="mt-2 text-sm text-text-light hidden">
                            Gambar saat ini: <span id="image-filename"></span>
                            <img id="image-preview" src="" alt="Current Image" class="w-16 h-16 object-cover rounded-md mt-1">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="menu-description" class="block text-sm font-medium text-text-light mb-1">Deskripsi</label>
                        <textarea name="menu_description" id="menu-description" rows="3" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-accent-gold"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeMenuModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-500 transition duration-200">Batal</button>
                        <button type="submit" id="menu-submit-btn" class="px-4 py-2 bg-secondary-gold text-primary-dark font-semibold rounded-lg hover:bg-accent-gold transition duration-200">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

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

        // ====================
        // ... (Kode Sidebar JS Anda di sini) ...

        // ==========================================================
        // LOGIKA MODAL 1: CRUD KATEGORI
        // ==========================================================
        const categoryModal = document.getElementById('category-modal');
        // Pastikan semua ID elemen modal kategori Anda diganti dengan prefix 'category-'
        const categoryModalTitle = document.getElementById('category-modal-title');
        const categoryModalAction = document.getElementById('category-modal-action');
        const categoryModalId = document.getElementById('category-modal-id');
        const categoryName = document.getElementById('category-name');
        const categoryDescription = document.getElementById('category-description');
        const categorySubmitBtn = document.getElementById('category-submit-btn');

        function openCategoryModal(mode, buttonElement = null) {
            document.getElementById('category-crud-form').reset();
            categoryModalAction.value = mode;

            if (mode === 'add') {
                categoryModalTitle.textContent = 'Tambah Kategori Baru';
                categorySubmitBtn.textContent = 'Tambahkan';
            } else if (mode === 'edit' && buttonElement) {
                const row = buttonElement.closest('tr');
                const id = row.dataset.id;
                const name = row.dataset.name;
                const description = row.dataset.description;

                categoryModalTitle.textContent = 'Edit Kategori: ID ' + id;
                categorySubmitBtn.textContent = 'Perbarui';
                categoryModalId.value = id;
                categoryName.value = name;
                categoryDescription.value = description;
            }
            categoryModal.classList.remove('hidden');
            categoryModal.classList.add('flex');
        }

        function closeCategoryModal() {
            categoryModal.classList.add('hidden');
            categoryModal.classList.remove('flex');
        }

        function confirmCategoryDelete(id) {
            if (confirm("Apakah Anda yakin ingin menghapus Kategori ID " + id + "?")) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                form.innerHTML = `<input type="hidden" name="action" value="delete">
                                <input type="hidden" name="category_id" value="${id}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }
        // ==========================================================
        // LOGIKA MODAL 2: CRUD PRODUK (MENUS)
        // ==========================================================
        const menuModal = document.getElementById('menu-modal');
        const menuModalTitle = document.getElementById('menu-modal-title');
        const menuModalAction = document.getElementById('menu-modal-action');
        const menuModalId = document.getElementById('menu-modal-id');
        const menuName = document.getElementById('menu-name');
        const menuDescription = document.getElementById('menu-description');
        const menuPrice = document.getElementById('menu-price');
        const menuStock = document.getElementById('menu-stock');
        const menuCategoryId = document.getElementById('menu-category-id');
        const menuSubmitBtn = document.getElementById('menu-submit-btn');

        function openMenuModal(mode, buttonElement = null) {
            document.getElementById('menu-crud-form').reset();
            menuModalAction.value = mode;

            if (mode === 'add') {
                menuModalTitle.textContent = 'Tambah Produk Baru';
                menuSubmitBtn.textContent = 'Tambahkan';
            } else if (mode === 'edit' && buttonElement) {
                const row = buttonElement.closest('tr');
                const id = row.dataset.id;
                const name = row.dataset.name;
                const description = row.dataset.description;
                const price = row.dataset.price;
                const stock = row.dataset.stock;
                const categoryId = row.dataset.categoryId;

                menuModalTitle.textContent = 'Edit Produk: ID ' + id;
                menuSubmitBtn.textContent = 'Perbarui';
                menuModalId.value = id;
                menuName.value = name;
                menuDescription.value = description;
                menuPrice.value = price;
                menuStock.value = stock;
                menuCategoryId.value = categoryId; // Mengatur dropdown kategori
            }

            menuModal.classList.remove('hidden');
            menuModal.classList.add('flex');
        }

        function closeMenuModal() {
            menuModal.classList.add('hidden');
            menuModal.classList.remove('flex');
        }

        function confirmMenuDelete(id) {
            if (confirm("Apakah Anda yakin ingin menghapus Produk ID " + id + "?")) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                form.innerHTML = `<input type="hidden" name="menu_action" value="delete">
                                <input type="hidden" name="menu_id" value="${id}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }


        // ==========================================================
        // LOGIKA TAB
        // ==========================================================
        function switchTab(tabId) {
            const tabs = ['products', 'categories'];
            tabs.forEach(id => {
                document.getElementById(`${id}-content`).classList.add('hidden');
                document.getElementById(`${id}-tab`).classList.remove('active-tab', 'text-text-light');
                document.getElementById(`${id}-tab`).classList.add('text-gray-400');
            });

            document.getElementById(`${tabId}-content`).classList.remove('hidden');
            document.getElementById(`${tabId}-tab`).classList.add('active-tab', 'text-text-light');
            document.getElementById(`${tabId}-tab`).classList.remove('text-gray-400');

            // Set URL hash untuk mempertahankan tab saat refresh (opsional, tapi bagus)
            history.pushState(null, null, `#${tabId}`);
        }

        // Inisialisasi tab saat load
        document.addEventListener('DOMContentLoaded', () => {
            // Cari apakah ada hash di URL (misal: #categories)
            const urlHash = window.location.hash.substring(1);

            // Jika ada feedback message, gunakan JS untuk beralih ke tab produk jika aksi produk yang dijalankan
            const isProductAction = document.getElementById('menu-modal-action') && document.getElementById('menu-modal-action').value === 'add' || document.getElementById('menu-modal-action').value === 'edit' || document.getElementById('menu-modal-action').value === 'delete';

            if (isProductAction) {
                // Jika aksi CRUD Produk baru saja dijalankan, tetap di tab produk
                switchTab('products');
            } else if (urlHash === 'categories') {
                switchTab('categories');
            } else {
                // Default ke tab Produk
                switchTab('products');
            }
        });
        // Ambil form dan tombol submit berdasarkan ID
        const form = document.getElementById('menu-crud-form');
        const submitBtn = document.getElementById('menu-submit-btn');

        if (form && submitBtn) {
            // Tambahkan event listener saat form disubmit
            form.addEventListener('submit', function() {
                // Nonaktifkan tombol segera setelah submit pertama kali
                submitBtn.disabled = true;
                // Opsional: Ganti teks tombol untuk umpan balik visual
                submitBtn.textContent = 'Memproses...';
            });
        }
        // Pastikan lucide.createIcons() dipanggil sekali di akhir
        lucide.createIcons();
    </script>

</body>

</html>