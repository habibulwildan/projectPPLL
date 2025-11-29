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
$stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$user_id = (int)$user['id'];
$stmt->close();

// =========================================================
// AMBIL SEMUA PESANAN USER SESUAI STATUS DI DATABASE
// =========================================================
// pending tetap berada paling atas
$sql = "
    SELECT id, order_number, total_amount, status, order_date
    FROM orders
    WHERE customer_id = ?
    ORDER BY 
        CASE 
            WHEN status = 'pending' THEN 1
            WHEN status = 'processing' THEN 2
            WHEN status = 'paid' THEN 3
            WHEN status = 'completed' THEN 4
            ELSE 5
        END,
    id DESC
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();

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
.status-paid { background:#5eba7d; color:white; }
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
            ?>

                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $o['order_number'] ?></td>
                    <td>Rp <?= number_format($o['total_amount'],0,',','.') ?></td>

                    <td>
                        <?php
                            $status = strtolower($o['status']);
                            switch($status){
                                case "pending": 
                                    echo "<span class='status-badge status-pending'>Pending</span>"; 
                                    break;
                                case "processing": 
                                    echo "<span class='status-badge status-processing'>Diproses</span>"; 
                                    break;
                                case "paid": 
                                    echo "<span class='status-badge status-paid'>Dibayar</span>"; 
                                    break;
                                case "completed": 
                                    echo "<span class='status-badge status-completed'>Selesai</span>"; 
                                    break;
                                default:
                                    echo "<span class='status-badge status-cancelled'>Dibatalkan</span>";
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
            <a href="menu.php" class="btn btn-secondary px-4">
                Kembali ke Menu
            </a>
        </div>

    </div>

</div>

</body>
</html>
