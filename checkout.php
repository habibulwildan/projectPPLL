<?php
session_start();
require_once __DIR__ . '/config.php';

$mysqli = $conn ?? $mysqli ?? null;

// ========================================================
// 1. WAJIB LOGIN
// ========================================================
if (!isset($_SESSION['user_email'])) {
    $_SESSION['flash'] = "Silakan login terlebih dahulu.";
    $_SESSION['flash_type'] = "danger";
    header("Location: login.php");
    exit;
}

// Ambil user_id
$user_email = $_SESSION['user_email'];
$stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$user_id = (int)$user['id'];
$stmt->close();

// ========================================================
// 2. PROSES CHECKOUT (POST)
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? null;

    if (!$payment_method) {
        $_SESSION['flash'] = "Silakan pilih metode pembayaran.";
        $_SESSION['flash_type'] = "danger";
        header("Location: checkout.php");
        exit;
    }

    // Ambil cart
    $cart = [];
    $sql = "
        SELECT c.id, c.menu_id, c.quantity, c.price, m.name
        FROM carts c
        JOIN menus m ON m.id = c.menu_id
        WHERE c.user_id = ?
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $subtotal = 0;
    while ($row = $res->fetch_assoc()) {
        $row['total'] = $row['price'] * $row['quantity'];
        $subtotal += $row['total'];
        $cart[] = $row;
    }
    $stmt->close();

    if (empty($cart)) {
        $_SESSION['flash'] = "Keranjang kosong.";
        $_SESSION['flash_type'] = "danger";
        header("Location: menu.php");
        exit;
    }

    $total_amount = $subtotal;

    // TRANSAKSI
    $mysqli->begin_transaction();

    try {
        $order_number = "ORD" . time() . rand(100,999);

        $stmt = $mysqli->prepare("
            INSERT INTO orders 
            (order_number, customer_id, subtotal, total_amount, status, order_type, order_date, created_at)
            VALUES (?, ?, ?, ?, 'pending', 'online', NOW(), NOW())
        ");
        $stmt->bind_param("sidd", $order_number, $user_id, $subtotal, $total_amount);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        // Detail order
        $stmt = $mysqli->prepare("
            INSERT INTO detail_orders 
            (order_id, menu_id, menu_name, menu_price, quantity, subtotal, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        foreach ($cart as $item) {
            $stmt->bind_param(
                "iisdis",
                $order_id,
                $item['menu_id'],
                $item['name'],
                $item['price'],
                $item['quantity'],
                $item['total']
            );
            $stmt->execute();
        }
        $stmt->close();

        // Payment
        $payment_id = "PAY" . time() . rand(100,999);
        $stmt = $mysqli->prepare("
            INSERT INTO payments
            (order_id, payment_id, payment_method, amount, status, created_at)
            VALUES (?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->bind_param("issd", $order_id, $payment_id, $payment_method, $total_amount);
        $stmt->execute();
        $stmt->close();

        // Hapus cart
        $stmt = $mysqli->prepare("DELETE FROM carts WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        $mysqli->commit();

        $_SESSION['flash'] = "Pesanan berhasil dikirim ke kasir. Menunggu konfirmasi pembayaran.";
        $_SESSION['flash_type'] = "success";

        header("Location: order_waiting.php?order_id=$order_id");
        exit;

    } catch (Exception $e) {
        $mysqli->rollback();
        error_log("Checkout error: " . $e->getMessage());
        $_SESSION['flash'] = "Terjadi kesalahan saat checkout.";
        $_SESSION['flash_type'] = "danger";
        header("Location: checkout.php");
        exit;
    }
}

// ========================================================
// 3. TAMPILKAN HALAMAN CHECKOUT (GET)
// ========================================================
$cartItems = [];
$sql = "
    SELECT c.id, c.menu_id, c.quantity, c.price, m.name
    FROM carts c
    JOIN menus m ON m.id = c.menu_id
    WHERE c.user_id = ?
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$subtotal = 0;
while ($row = $res->fetch_assoc()) {
    $row['total'] = $row['price'] * $row['quantity'];
    $subtotal += $row['total'];
    $cartItems[] = $row;
}
$stmt->close();

$total = $subtotal;
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Checkout – Kopi Senja</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #c9b49b, #e9d7c3, #d2b89c);
    min-height: 100vh;
    font-family: "Poppins", sans-serif;
}
.checkout-box {
    background: #fdf8f3;
    padding: 35px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(80,50,20,0.25);
    border: 2px solid #b08c6d;
}
.payment-card {
    border: 2px solid #c9a786;
    border-radius: 15px;
    width: 150px;
    height: 150px;
    transition: 0.3s;
    padding: 15px;
    display: flex;
    flex-direction: column;
    justify-content: center;   
    align-items: center; 
    background: #fff6ec;
    box-shadow: 0 3px 10px rgba(70, 45, 20, 0.2);
}
.payment-card img {
    width: 70px;
    margin-bottom: 10px;
}
.payment-option input:checked + .payment-card {
    border-color: #8b5e34;
    box-shadow: 0 0 12px rgba(139, 94, 52, 0.7);
    background: #f4e5d6;
    transform: scale(1.08);
}
.payment-card:hover {
    transform: scale(1.04);
    cursor: pointer;
}
.table {
    background: #fffaf2;
    border-radius: 12px;
    overflow:hidden;
    box-shadow: 0 5px 15px rgba(80,50,20,0.15);
}
.table th {
    background-color: #8b5e34;
    color: white;
}
.btn-coffee {
    background: #8b5e34;
    color: white;
    border-radius: 12px;
    padding: 12px 25px;
    font-size: 18px;
    border: none;
}
.btn-coffee:hover {
    background: #6b4626;
    color: #fff;
}
h2,h3 {
    color:#4a321c;
}
</style>
</head>
<body>

<div class="container mt-5" style="max-width:900px;">
    <div class="checkout-box">
        <h2 class="mb-3 text-center fw-bold">Checkout Pesanan</h2>

        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Menu</th>
                    <th width="70">Qty</th>
                    <th>Harga</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($cartItems as $it): ?>
                <tr>
                    <td><?= htmlspecialchars($it['name']) ?></td>
                    <td><?= $it['quantity'] ?></td>
                    <td>Rp <?= number_format($it['price'],0,',','.') ?></td>
                    <td>Rp <?= number_format($it['total'],0,',','.') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h3 class="fw-bold text-end mt-4">
            Total Bayar: <span style="color:#8b5e34;">Rp <?= number_format($total,0,',','.') ?></span>
        </h3>

        <hr>

        <form method="POST" action="checkout.php">
            <label class="form-label fw-bold mb-3">Pilih Metode Pembayaran</label>

            <div class="d-flex justify-content-center flex-wrap flex-md-nowrap gap-3">
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="cash" class="d-none" required>
                    <div class="payment-card text-center">
                        <img src="img/cash4.png" alt="Cash">
                        <p class="mt-2 fw-bold" style="color:#5a3a1e;">Cash / Tunai</p>
                    </div>
                </label>

                <label class="payment-option">
                    <input type="radio" name="payment_method" value="qris" class="d-none">
                    <div class="payment-card text-center">
                        <img src="img/qris.png" alt="QRIS">
                        <p class="mt-2 fw-bold" style="color:#5a3a1e;">QRIS</p>
                    </div>
                </label>
            </div>

            <div class="text-center">
                <button type="submit" class="btn-coffee mt-4">Bayar Sekarang</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
