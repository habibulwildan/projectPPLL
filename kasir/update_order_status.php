<?php
// update_order_status.php
session_start();
// Sesuaikan path ke config.php
require_once __DIR__ . '/../config.php'; 

$mysqli = $conn ?? null;

// WAJIB LOGIN dan memiliki Role Kasir (Role ID = 2)
if (!isset($_SESSION['user_role_id']) || $_SESSION['user_role_id'] != 2) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id']) && isset($_POST['new_status'])) {
    
    $order_id = (int)$_POST['order_id'];
    $new_status_action = $_POST['new_status']; 
    $target_status = '';

    // Tentukan status target berdasarkan aksi yang dikirimkan
    switch ($new_status_action) {
        case 'paid_to_processing':
            // Mengubah status menjadi 'confirmed' atau 'preparing' (sesuai ENUM yang valid)
            $target_status = 'confirmed'; 
            break;
        case 'ready':
            $target_status = 'ready';
            break;
        case 'completed':
            $target_status = 'completed';
            break;
        case 'cancelled':
            $target_status = 'cancelled';
            break;
        default:
            // --- PERBAIKAN INI PENTING ---
            // Jika status aksi tidak dikenal, hentikan eksekusi dan berikan error.
            header("Location: manage_orders.php?error=Status aksi '$new_status_action' tidak dikenal atau tidak valid.");
            exit; 
    }

    // Pastikan $target_status memiliki nilai yang valid sebelum dieksekusi
    if (empty($target_status)) {
        header("Location: manage_orders.php?error=Gagal menentukan status target.");
        exit;
    }

    // Eksekusi UPDATE
    $sql_update = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $mysqli->prepare($sql_update);
    // BARIS INI (47) yang sebelumnya error kini akan terhindar dari nilai kosong
    $stmt->bind_param("si", $target_status, $order_id); 

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: manage_orders.php?success=Pesanan $order_id berhasil diubah menjadi status '$target_status'.");
        exit;
    } else {
        $stmt->close();
        header("Location: manage_orders.php?error=Gagal mengubah status pesanan ID $order_id: " . $mysqli->error);
        exit;
    }
} else {
    header("Location: manage_orders.php?error=Permintaan tidak valid.");
    exit;
}
?>