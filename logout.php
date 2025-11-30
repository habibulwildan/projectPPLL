<?php
// logout.php - Aman untuk semua branch dan directory
session_start();

// 1. Hapus semua data session di server
$_SESSION = [];

// 2. Hapus session di server
session_destroy();

// 3. Hapus cookie session di browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], 
        $params["domain"], 
        $params["secure"], 
        $params["httponly"]
    );
}

// 4. Hapus cookie custom jika ada (opsional, aman)
if (isset($_COOKIE['auth_token'])) {
    setcookie('auth_token', '', time() - 42000, '/');
}

// 5. Regenerate session ID untuk keamanan tambahan
session_regenerate_id(true);

// 6. Redirect relatif ke login.php (tidak pakai path absolut)
header("Location: login.php");
exit();
?>