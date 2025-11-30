<?php
session_start();
require_once __DIR__.'/config.php';

if (!isset($_GET['order_id'])) {
    header("Location: menu.php");
    exit;
}

$order_id = intval($_GET['order_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Menunggu Konfirmasi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body {
    background: #d7c4a7; /* latte brown */
    background: linear-gradient(135deg, #c7b299, #d9cbb8, #bfa88c);
    min-height: 100vh;
    font-family: "Poppins", sans-serif;
}

/* CARD */
.wait-card {
    background: #f4eee0;     /* warm cream */
    padding: 35px 40px;
    border-radius: 20px;
    width: 100%;
    max-width: 600px;
    box-shadow: 0 10px 25px rgba(90, 60, 30, 0.3);
    animation: fadeIn 0.4s ease;
    border: 2px solid #b08c6d;
}

/* ICON */
.wait-icon {
    width: 80px;
    opacity: 0.9;
    animation: pulse 1.8s infinite ease-in-out;
}

/* ORDER BOX */
.order-id-box {
    background: #b08c6d;
    color: #fff;
    display: inline-block;
    padding: 12px 30px;
    border-radius: 14px;
    font-size: 22px;
    font-weight: bold;
    letter-spacing: 1px;
    box-shadow: 0 4px 10px rgba(80,50,20,0.2);
}

/* BUTTON */
.btn-coffee {
    background: #8b5e34;
    color: white;
    border-radius: 10px;
    padding: 10px 24px;
    border: none;
}
.btn-coffee:hover {
    background: #704728;
    color: #fff;
}

.btn-outline-coffee {
    border: 2px solid #8b5e34;
    color: #8b5e34;
    border-radius: 10px;
    padding: 10px 24px;
}
.btn-outline-coffee:hover {
    background: #8b5e34;
    color: #fff;
}

/* ANIMATIONS */
@keyframes pulse {
    0%   { transform: scale(1); opacity: 0.7; }
    50%  { transform: scale(1.14); opacity: 1; }
    100% { transform: scale(1); opacity: 0.7; }
}

@keyframes fadeIn {
    0%   { opacity: 0; transform: translateY(10px); }
    100% { opacity: 1; transform: translateY(0); }
}
</style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-center" style="min-height:100vh;">
    <div class="wait-card text-center">

        <!-- Gambar ikon kopi / loading -->
        <img src="img/kopi.png" class="wait-icon mb-3" alt="loading">

        <h3 class="fw-bold mb-2" style="color:#4a321c;">Pesanan Sedang Diproses</h3>
        <p class="text-muted mb-3" style="color:#5a4634!important;">
            Pesanan akan dikonfirmasi oleh kasir terlebih dahulu.
        </p>

        <p class="mt-3 fw-semibold" style="color:#4a321c;">ID Pesanan Kamu:</p>
        <div class="order-id-box mb-4">
            <?= $order_id ?>
        </div>

        <div class="d-flex justify-content-center gap-3 mt-4">
            <a href="riwayat_pesanan.php" class="btn btn-coffee">
                Lihat Status
            </a>
            <!-- <a href="menu.php" class="btn btn-outline-coffee">
                Kembali ke Menu -->
            </a>
        </div>

    </div>
</div>

</body>
</html>
