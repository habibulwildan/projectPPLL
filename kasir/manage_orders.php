<?php
session_start();
// Sesuaikan path ke config.php
require_once __DIR__ . '/../config.php'; 

$mysqli = $conn ?? null;

// WAJIB LOGIN dan memiliki Role Kasir (Role ID = 2, berdasarkan kopi_senja.sql)
if (!isset($_SESSION['user_role_id']) || $_SESSION['user_role_id'] != 2) {
    header("Location: ../login.php");
    exit;
}

// ---------------------------------------------------------
// AMBIL SEMUA PESANAN AKTIF (BELUM SELESAI ATAU DIBATALKAN)
// ---------------------------------------------------------
$sql = "
    SELECT 
        o.id, 
        o.order_number, 
        o.total_amount, 
        o.status, 
        o.order_date, 
        o.customer_name,
        -- Menggunakan COALESCE: Jika customer_id ada (u.name tidak NULL), gunakan u.name.
        -- Jika customer_id NULL (pesanan umum), gunakan o.customer_name.
        COALESCE(u.name, o.customer_name) AS display_customer_name
    FROM orders o
    LEFT JOIN users u ON o.customer_id = u.id  -- Melakukan JOIN ke tabel users berdasarkan customer_id
    WHERE o.status NOT IN ('completed', 'cancelled')
    ORDER BY 
        CASE 
            WHEN o.status = 'pending' THEN 1
            WHEN o.status = 'confirmed' THEN 2
            WHEN o.status = 'preparing' THEN 3
            WHEN o.status = 'ready' THEN 4
            ELSE 5
        END,
    o.id DESC
";

$stmt = $mysqli->prepare($sql);
$stmt->execute();
$active_orders = $stmt->get_result();

$message = '';
if (isset($_GET['success'])) {
    $message = ['type' => 'success', 'text' => htmlspecialchars($_GET['success'])];
} elseif (isset($_GET['error'])) {
    $message = ['type' => 'danger', 'text' => htmlspecialchars($_GET['error'])];
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Pesanan – Kopi Senja POS</title>
    <link href="templates/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 1200px; margin-top: 30px; }
        .table thead th { background-color: #8b5e34; color: white; }
        .status-badge { padding: 6px 10px; border-radius: 5px; font-weight: 600; font-size: 0.85em; }
        /* Warna Badge Status (seperti di riwayat_pesanan.php) */
        .status-pending { background:#e6c48c; color:#5a4634; }
        .status-confirmed, .status-processing { background:#8f704b; color:white; }
        .status-ready { background:#5eba7d; color:white; }
        .status-cancelled { background:#d9534f; color:white; }
        .status-completed { background:#3c7dd9; color:white; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #4a321c;">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Kopi Senja POS</a>
        <div class="d-flex">
             <span class="navbar-text me-3 text-white">Kasir: <?= $_SESSION['user_name'] ?? 'Kasir' ?></span>
             <a class="btn btn-warning" href="../logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container">

    <h3 class="fw-bold mb-4 text-center">Manajemen Pesanan Aktif</h3>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
            <?= $message['text'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <a href="index.php" class="btn btn-secondary mb-3">Kembali ke Transaksi Baru</a>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Total Bayar</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php 
            $no = 1;
            if ($active_orders->num_rows > 0):
                while ($o = $active_orders->fetch_assoc()):
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($o['order_number']) ?></td>
                    <td><?= htmlspecialchars($o['display_customer_name'] ?: 'Umum') ?></td>
                    <td>Rp <?= number_format($o['total_amount'], 0, ',', '.') ?></td>
                    <td>
                        <?php
                            $status = strtolower($o['status']);
                            $status_text = match($status) {
                                'pending' => 'Menunggu Pembayaran',
                                'confirmed' => 'Dikonfirmasi',
                                'preparing' => 'Disiapkan',
                                'ready' => 'Siap Diambil',
                                default => 'Tidak Diketahui',
                            };
                        ?>
                        <span class="status-badge status-<?= $status ?>"><?= $status_text ?></span>
                    </td>
                    <td><?= date("d M Y H:i", strtotime($o['order_date'])) ?></td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            
                            <?php if ($status == 'pending'): ?>
                                <form method="POST" action="update_order_status.php" class="d-inline">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <input type="hidden" name="new_status" value="paid_to_processing"> 
                                    <button type="submit" class="btn btn-success btn-sm w-100">✔ Bayar & Proses</button>
                                </form>
                            <?php endif; ?>

                            <?php if (in_array($status, ['confirmed', 'preparing'])): ?>
                                <form method="POST" action="update_order_status.php" class="d-inline">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <input type="hidden" name="new_status" value="ready"> 
                                    <button type="submit" class="btn btn-info btn-sm w-100">Siap</button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($status == 'ready'): ?>
                                <form method="POST" action="update_order_status.php" class="d-inline">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <input type="hidden" name="new_status" value="completed"> 
                                    <button type="submit" class="btn btn-primary btn-sm w-100">Selesai</button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if (!in_array($status, ['completed', 'cancelled'])): ?>
                                <form method="POST" action="update_order_status.php" class="d-inline" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?');">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <input type="hidden" name="new_status" value="cancelled">
                                    <button type="submit" class="btn btn-danger btn-sm w-100">✘ Batalkan</button>
                                </form>
                            <?php endif; ?>

                        </div>
                    </td>
                </tr>

            <?php 
                endwhile;
            else:
            ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Tidak ada pesanan aktif saat ini.
                    </td>
                </tr>
            <?php endif; ?>

            </tbody>
        </table>
    </div>
</div>

<script src="templates/bootstrap.bundle.min.js"></script>
</body>
</html>