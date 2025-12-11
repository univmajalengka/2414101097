<?php
// PASTIKAN SESI DIMULAI SEBELUM ADA OUTPUT HTML
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi koneksi ke database MariaDB
$host = "localhost"; // Sesuaikan dengan host Anda
$user = "root";      // Sesuaikan dengan user database Anda
$pass = "";          // Sesuaikan dengan password database Anda
$db = "db_wisata_bromo"; // Nama database yang sudah dibuat di schema.sql

// Membuat koneksi
$koneksi = new mysqli($host, $user, $pass, $db);

// Memeriksa koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Opsional: Set character set
$koneksi->set_charset("utf8mb4");

// Fungsi untuk membersihkan input
function sanitize_input($data) {
    global $koneksi;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $koneksi->real_escape_string($data);
    return $data;
}

// --- FUNGSI AUTENTIKASI ---
function check_login() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: login.php");
        exit();
    }
}
?>