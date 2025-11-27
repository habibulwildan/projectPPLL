<?php
// Data koneksi ke database
$host = "localhost";       // Nama host, biasanya localhost
$user = "root";            // Nama user database (sesuaikan dengan pengaturan Anda)
$pass = "";                // Password database (kosongkan jika tidak ada)
$db   = "kopi_senja";      // Nama database sesuai file kopi_senja.sql

// Membuat koneksi ke database menggunakan MySQLi
$koneksi = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($koneksi->connect_error) {
    // Jika gagal koneksi, hentikan script dan tampilkan pesan error
    die("Koneksi gagal: " . $koneksi->connect_error);
} 

// Set karakter yang digunakan agar mendukung UTF-8
$koneksi->set_charset("utf8");

// Koneksi berhasil, siap digunakan
?>