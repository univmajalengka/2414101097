<?php

$server = "localhost";
$user = "root";
// PERBAIKAN: Ganti password Anda di sini
// Jika Anda menggunakan XAMPP default, password-nya adalah string kosong ("")
$password = ""; 
$nama_database = "pendaftaran_siswa";

$db = mysqli_connect($server, $user, $password, $nama_database);

if( !$db ){
    // Pesan ini akan muncul jika ada masalah lain (misal: database belum dibuat)
    die("Gagal terhubung dengan database: " . mysqli_connect_error());
}

?>