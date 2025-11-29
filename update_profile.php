<?php
session_start();
require_once __DIR__ . '/config.php';

if (!isset($_POST['id'])) {
    header("Location: profile.php");
    exit;
}

$user_id = $_POST['id'];
$name = trim($_POST['name']);
$phone = trim($_POST['phone']);

$stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, updated_at = NOW() WHERE id = ?");
$stmt->bind_param("ssi", $name, $phone, $user_id);

if ($stmt->execute()) {
    $_SESSION['user_name'] = $name; // update navbar
    header("Location: profile.php?success=1");
} else {
    header("Location: profile.php?error=1");
}

$stmt->close();
