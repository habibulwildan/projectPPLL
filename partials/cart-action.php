<?php
// /KopiSenja/cart-action.php
if (session_status() === PHP_SESSION_NONE) session_start();

// Jangan output apa-apa di sini sebelum redirect
require_once __DIR__ . '/config.php'; // sesuaikan path kalau perlu

$mysqli = $conn ?? $mysqli ?? null;
$user_id = $_SESSION['user_id'] ?? null;

// fallback: jika user_id belum di session, coba dari email
if (!$user_id && !empty($_SESSION['user_email']) && $mysqli) {
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $_SESSION['user_email']);
        $stmt->execute();
        if (method_exists($stmt, 'get_result')) {
            $r = $stmt->get_result();
            if ($r && $r->num_rows) {
                $u = $r->fetch_assoc();
                $user_id = (int)$u['id'];
            }
        } else {
            $stmt->bind_result($uid);
            if ($stmt->fetch()) $user_id = (int)$uid;
        }
        $stmt->close();
    }
}

// Pastikan method POST dan user logged in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cart_action']) && $user_id && $mysqli) {
    $action = $_POST['cart_action'];

    if ($action === 'remove' && !empty($_POST['cart_id'])) {
        $cid = (int)$_POST['cart_id'];
        $stmt = $mysqli->prepare("DELETE FROM carts WHERE id = ? AND user_id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $cid, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($action === 'clear') {
        $stmt = $mysqli->prepare("DELETE FROM carts WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Redirect kembali ke halaman asal (fallback ke /menu.php)
$redirect = $_SERVER['HTTP_REFERER'] ?? './menu.php';
header("Location: " . $redirect);
exit;
