<?php
session_start();
require_once __DIR__ . '/config.php';

if (!isset($_POST['id'], $_POST['name'], $_POST['phone'])) {
    header("Location: profile.php");
    exit;
}

$mysqli = $conn ?? $mysqli ?? null;
$user_id = intval($_POST['id']);
$name = trim($_POST['name']);
$phone = trim($_POST['phone']);

$stmt = $mysqli->prepare("UPDATE users SET name=?, phone=?, updated_at=NOW() WHERE id=?");
$stmt->bind_param("ssi", $name, $phone, $user_id);

if ($stmt->execute()) {
    $_SESSION['user_name'] = $name;
    header("Location: profile.php?success=1");
} else {
    header("Location: profile.php?error=1");
}
$stmt->close();
exit;
