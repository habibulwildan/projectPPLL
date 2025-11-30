<?php
// get_statistics.php
session_start();
require_once __DIR__ . '/../config.php';

$mysqli = $conn ?? null;
$response = ['success' => false, 'data' => []];

// Pastikan user adalah Kasir (Role ID = 2)
if (!isset($_SESSION['user_role_id']) || $_SESSION['user_role_id'] != 2) {
    $response['message'] = 'Akses ditolak.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$today = date('Y-m-d');

// Fungsi untuk eksekusi query
function execute_statistic_query($mysqli, $sql, $date) {
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }
    return null;
}

// 1. Total Penjualan Hari Ini (Confirmed/Completed)
$sql_sales = "
    SELECT SUM(total_amount) as total_sales
    FROM orders
    WHERE status IN ('confirmed', 'completed', 'ready') 
    AND DATE(order_date) = ?
";
$result_sales = execute_statistic_query($mysqli, $sql_sales, $today);
$total_sales_today = (float)($result_sales['total_sales'] ?? 0);


// 2. Jumlah Pesanan Hari Ini (Total)
$sql_orders_count = "
    SELECT COUNT(id) as total_orders
    FROM orders
    WHERE DATE(order_date) = ?
";
$result_count = execute_statistic_query($mysqli, $sql_orders_count, $today);
$total_orders_today = (int)($result_count['total_orders'] ?? 0);


// 3. Pesanan Menunggu Pembayaran (Pending)
$sql_pending = "
    SELECT COUNT(id) as pending_orders
    FROM orders
    WHERE status = 'pending' 
    AND DATE(order_date) = ?
";
$result_pending = execute_statistic_query($mysqli, $sql_pending, $today);
$pending_orders_today = (int)($result_pending['pending_orders'] ?? 0);


// Format respons JSON
$response['success'] = true;
$response['data'] = [
    'total_sales' => $total_sales_today,
    'total_orders' => $total_orders_today,
    'pending_orders' => $pending_orders_today,
];

header('Content-Type: application/json');
echo json_encode($response);
?>