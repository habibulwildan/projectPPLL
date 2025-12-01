<?php
function loadEnv($path)
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, '"\'');
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Load .env hanya jika file ada (untuk development lokal)
if (file_exists(__DIR__ . '/.env')) {
    loadEnv(__DIR__ . '/.env');
}

// Railway akan menggunakan getenv(), development lokal menggunakan $_ENV
$host = getenv('MYSQLHOST') ?: ($_ENV['MYSQLHOST'] ?? 'localhost');
$username = getenv('MYSQLUSER') ?: ($_ENV['MYSQLUSER'] ?? 'root');
$password = getenv('MYSQLPASSWORD') ?: ($_ENV['MYSQLPASSWORD'] ?? '');
$database = getenv('MYSQLDATABASE') ?: ($_ENV['MYSQLDATABASE'] ?? 'kopi_senja');
$port = intval(getenv('MYSQLPORT') ?: ($_ENV['MYSQLPORT'] ?? 3306));

try {
    $conn = new mysqli($host, $username, $password, $database, $port);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());

    // Untuk debugging di development
    if (getenv('APP_ENV') !== 'production') {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("Database connection failed. Please try again later.");
    }
}
