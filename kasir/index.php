<?php
// index.php (Full Code - Skema Warna Putih-Coklat)
session_start();
require_once __DIR__ . '/../config.php';

// Cek autentikasi dan redirect (Sama seperti sebelumnya)
if (!isset($_SESSION['user_role_id'])) {
    header ('Location: ../login.php');
    exit;
} else {
    switch($_SESSION['user_role_id']) {
        case 1:
            header('Location: ../');
            exit;
        case 3:
            header('Location: ../admin_index.php');
            exit;
    };
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir | Kopi Senja POS</title>
    
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'kopi': '#6F4E37', // Warna Coklat Kopi
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Menggunakan Tailwind colors: Green-500, Blue-500, Amber-500 */
        .border-start-success { border-left: 5px solid #10B981; } 
        .border-start-primary { border-left: 5px solid #3B82F6; } 
        .border-start-warning { border-left: 5px solid #F59E0B; } 
    </style>
</head>
<body class="bg-gray-100">

<nav class="bg-white shadow-md">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex justify-between items-center">
            <a class="text-xl font-bold text-gray-800" href="#">Kopi Senja POS</a>
            
            <div class="flex items-center space-x-3">
                <span class="text-gray-600 mr-3">Kasir: <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                
                <a class="px-3 py-2 text-sm font-medium text-kopi border border-kopi rounded-md hover:bg-kopi hover:text-white transition duration-150 ease-in-out" href="manage_orders.php">
                    Kelola Pesanan
                </a>
                
                <a class="px-3 py-2 text-sm font-medium text-white bg-kopi rounded-md hover:bg-opacity-90 transition duration-150 ease-in-out flex items-center space-x-1" type="button" href="../logout.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/><path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/></svg>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="w-full p-6 lg:p-10">
    <div class="mb-6">
        <h2 class="text-3xl font-extrabold text-gray-900">Dashboard Kasir</h2>
        <p class="text-gray-500 mt-1">Selamat datang <b class="font-semibold"><?= htmlspecialchars($_SESSION['user_name']) ?></b>. Ringkasan Pesanan Hari Ini (<?= date('d F Y') ?>).</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        
        <div>
            <div class="bg-white p-5 rounded-lg shadow-md border-start-success">
                <h5 class="text-sm font-medium text-green-600 uppercase tracking-wider">Total Penjualan</h5>
                <h1 class="text-3xl font-bold mt-1" id="realtime-sales">Menunggu...</h1>
                <p class="text-xs text-gray-500 mt-2">Total yang sudah diselesaikan hari ini.</p>
            </div>
        </div>

        <div>
            <div class="bg-white p-5 rounded-lg shadow-md border-start-primary">
                <h5 class="text-sm font-medium text-blue-600 uppercase tracking-wider">Jumlah Pesanan</h5>
                <h1 class="text-3xl font-bold mt-1" id="realtime-orders">Menunggu...</h1>
                <p class="text-xs text-gray-500 mt-2">Total pesanan yang masuk hari ini.</p>
            </div>
        </div>

        <div>
            <div class="bg-white p-5 rounded-lg shadow-md border-start-warning">
                <h5 class="text-sm font-medium text-amber-600 uppercase tracking-wider">Menunggu Pembayaran</h5>
                <h1 class="text-3xl font-bold mt-1" id="realtime-pending">Menunggu...</h1>
                <p class="text-xs text-gray-500 mt-2">Perlu ditindaklanjuti di Kelola Pesanan.</p>
            </div>
        </div>
    </div>
</main>

<script>
    // Fungsi format Rupiah
    function formatRupiah(number) {
        if (number === null || number === undefined || isNaN(number)) {
            return 'Rp 0';
        }
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
    }

    // Fungsi untuk mengambil dan memperbarui statistik
    async function updateStatistics() {
        try {
            const response = await fetch('get_statistics.php', {
                method: 'POST', 
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            });
            const result = await response.json();

            if (result.success) {
                const data = result.data;
                
                document.getElementById('realtime-sales').textContent = formatRupiah(data.total_sales);
                document.getElementById('realtime-orders').textContent = data.total_orders.toLocaleString('id-ID');
                document.getElementById('realtime-pending').textContent = data.pending_orders.toLocaleString('id-ID');

            } else {
                console.error("Gagal memuat statistik:", result.message);
            }
        } catch (error) {
            console.error("Kesalahan koneksi saat memuat statistik:", error);
        }
    }

    updateStatistics(); 
    setInterval(updateStatistics, 5000); 
</script>

</body>
</html>