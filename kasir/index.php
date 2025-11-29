<?php
session_start();
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['user_role_id'])) {
	header ('Location: ../login.php');
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
include("templates/section_head.php");
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Kopi Senja POS</a>
            <div class="d-flex">
                <span class="navbar-text me-3">Kasir: <?= $_SESSION['user_name'] ?></span>
                <!--<button class="btn btn-outline-secondary me-2" type="button" data-bs-toggle="modal" data-bs-target="#customerModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-check-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L12.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z"/><path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg>
                    Pilih Pembeli
                </button>-->
				<a class="btn btn-outline-info me-2" href="manage_orders.php">
                    Kelola Pesanan
                </a>
                <a class="btn btn-kopi" type="button" href="../logout.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/><path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/></svg>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <main class="container-fluid pos-container">
        <div class="row p-4">
        <div class="col-12 mb-4">
            <h2 class="fw-bold">Dashboard Kasir</h2>
            <p class="text-muted">Selamat datang <b><?= $_SESSION['user_name'] ?></b>. Ringkasan Pesanan Hari Ini (<?= date('d F Y') ?>).</p>
        </div>

        <div class="col-12">
            <div class="row g-4">
                
                <div class="col-md-4 col-sm-6">
                    <div class="card shadow-sm border-0 border-start border-success border-5">
                        <div class="card-body">
                            <h5 class="card-title text-success">Total Penjualan</h5>
                            <h1 class="card-text fw-bold" id="realtime-sales">Menunggu...</h1>
                            <p class="text-muted mb-0">Total yang sudah diselesaikan hari ini.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="card shadow-sm border-0 border-start border-primary border-5">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Jumlah Pesanan</h5>
                            <h1 class="card-text fw-bold" id="realtime-orders">Menunggu...</h1>
                            <p class="text-muted mb-0">Total pesanan yang masuk hari ini.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="card shadow-sm border-0 border-start border-warning border-5">
                        <div class="card-body">
                            <h5 class="card-title text-warning">Menunggu Pembayaran</h5>
                            <h1 class="card-text fw-bold" id="realtime-pending">Menunggu...</h1>
                            <p class="text-muted mb-0">Perlu ditindaklanjuti di Kelola Pesanan.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        </div>
    </main>

    <!--<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalLabel">Pilih / Cari Pembeli</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control mb-3" placeholder="Cari nama atau nomor telepon pembeli">
                    <button class="btn btn-outline-success">Gunakan Pelanggan Baru/Umum</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Metode Pembayaran (Total: Rp 55.000)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        <button type="button" class="list-group-item list-group-item-action">Tunai</button>
                        <button type="button" class="list-group-item list-group-item-action">Debit/Kredit</button>
                        <button type="button" class="list-group-item list-group-item-action">QRIS (E-Wallet)</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-kopi">Bayar & Cetak Struk</button>
                </div>
            </div>
        </div>
    </div>-->

    <script src="/kasir/templates/bootstrap.bundle.min.js"></script>
	
	<script>
    // Fungsi format Rupiah (diambil dari jawaban sebelumnya)
    function formatRupiah(number) {
        // Jika angka null atau undefined, kembalikan Rp 0
        if (number === null || number === undefined || isNaN(number)) {
            return 'Rp 0';
        }
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
    }

    // Fungsi untuk mengambil dan memperbarui statistik
    async function updateStatistics() {
        try {
            const response = await fetch('get_statistics.php', {
                method: 'POST', // Menggunakan POST untuk konsistensi dengan AJAX lain
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            });
            const result = await response.json();

            if (result.success) {
                const data = result.data;
                
                // Perbarui tampilan dengan data terbaru
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

    // Panggil fungsi segera setelah halaman dimuat
    updateStatistics(); 

    // Panggil fungsi secara berkala setiap 5 detik (5000 milidetik)
    setInterval(updateStatistics, 5000); 
	</script>

<?php
include("templates/section_foot.php");
