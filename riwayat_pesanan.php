<?php
session_start();
require_once __DIR__ . '/config.php';

$mysqli = $conn ?? $mysqli ?? null;

// WAJIB LOGIN
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit;
}

// Ambil user dari session
$user_email = $_SESSION['user_email'];
$stmt = $mysqli->prepare("SELECT id, name FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$user_id = (int)$user['id'];
$user_name = $user['name'] ?? 'User';
$stmt->close();

// =========================================================
// AMBIL SEMUA PESANAN USER SESUAI STATUS DI DATABASE
// =========================================================
$sql = "
    SELECT 
        o.id,
        o.order_number,
        o.total_amount,
        o.status,
        o.order_date,
        COALESCE(u.name, o.customer_name) AS display_customer_name
    FROM orders o
    LEFT JOIN users u ON o.customer_id = u.id
    WHERE o.customer_id = ?
      AND o.status NOT IN ('completed')   -- jangan tampilkan pesanan selesai
    ORDER BY 
        CASE 
            WHEN status = 'pending' THEN 1
            WHEN status = 'confirmed' THEN 2
            WHEN status = 'processing' THEN 3
            WHEN status = 'ready' THEN 4
            WHEN status = 'cancelled' THEN 5
            ELSE 6
        END,
        id DESC
";


$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Pesanan – Kopi Senja</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg, #c7b299, #d9cbb8, #bfa88c);
    min-height: 100vh;
    font-family: "Poppins", sans-serif;
}
.history-box {
    background: #fdf8f3;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(50,30,10,0.2);
    border: 2px solid #b08c6d;
}
.table thead {
    background: #8b5e34;
    color: white;
}
.status-badge {
    padding: 6px 14px;
    border-radius: 10px;
    font-weight: 600;
}
.status-pending { background:#e6c48c; color:#5a4634; }
.status-processing { background:#8f704b; color:white; }
.status-ready { background:#5eba7d; color:white; }
.status-completed { background:#3c7dd9; color:white; }
.status-cancelled { background:#d9534f; color:white; }
</style>
</head>
<body>

<div class="container mt-5">

    <h2 class="fw-bold text-center mb-4" style="color:#4a321c;">Riwayat Pesanan Kamu</h2>

    <div class="history-box">

        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No Pesanan</th>
                    <th>Total Bayar</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>

            <tbody>
            <?php 
            $no = 1;
            if ($orders->num_rows > 0):
                while ($o = $orders->fetch_assoc()):
                    $status = strtolower($o['status'] ?? '');
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($o['order_number']) ?></td>
                    <td>Rp <?= number_format($o['total_amount'],0,',','.') ?></td>
                    <td>
                        <?php
                        switch($status){
                            case "pending": 
                                echo "<span class='status-badge status-pending'>Menunggu Pembayaran</span>"; 
                                break;
                            case "confirmed": 
                                echo "<span class='status-badge status-processing'>Dikonfirmasi</span>"; 
                                break;
                            case "processing": 
                                echo "<span class='status-badge status-processing'>Diproses</span>"; 
                                break;
                            case "ready": 
                                echo "<span class='status-badge status-ready'>Siap Diambil</span>"; 
                                break;
                            case "completed": 
                                echo "<span class='status-badge status-completed'>Selesai</span>"; 
                                break;
                            case "cancelled": 
                                echo "<span class='status-badge status-cancelled'>Dibatalkan</span>"; 
                                break;
                            default:
                                echo "<span class='status-badge status-cancelled'>Tidak Diketahui</span>";
                        }
                        ?>
                    </td>
                    <td><?= date("d M Y H:i", strtotime($o['order_date'])) ?></td>
                </tr>
            <?php 
                endwhile;
            else:
            ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        Tidak ada riwayat pesanan.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <div class="text-center mt-4">
            <a href="menu.php" class="btn btn-secondary px-4">Kembali ke Menu</a>
        </div>

    </div>

</div>

</body>
</html>
