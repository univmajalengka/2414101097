<?php

include("koneksi.php");

// Cek apakah tombol daftar sudah diklik atau belum
if (isset($_POST['daftar'])) {

    // Ambil data dari formulir
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $jk = $_POST['jenis_kelamin'];
    $agama = $_POST['agama'];
    $sekolah = $_POST['sekolah_asal']; // PERBAIKAN: Tambahkan tanda '$' untuk variabel

    // Sanitasi data untuk mencegah SQL Injection (Best Practice)
    // Walaupun PreparedStatement lebih disarankan, kita perbaiki kode ini dengan mysqli_real_escape_string
    $nama = mysqli_real_escape_string($db, $nama);
    $alamat = mysqli_real_escape_string($db, $alamat);
    $jk = mysqli_real_escape_string($db, $jk);
    $agama = mysqli_real_escape_string($db, $agama);
    $sekolah = mysqli_real_escape_string($db, $sekolah);

    // Buat query (PERBAIKAN: Ganti 'VALUE' menjadi 'VALUES')
    $sql = "INSERT INTO calon_siswa (nama, alamat, jenis_kelamin, agama, sekolah_asal) VALUES ('$nama', '$alamat', '$jk', '$agama', '$sekolah')";
    $query = mysqli_query($db, $sql);

    // Cek apakah query simpan berhasil
    if ($query) {
        // Kalau berhasil alihkan ke halaman index.php dengan status=sukses
        header('Location: index.php?status=sukses');
        exit(); // Best Practice: Tambahkan exit() setelah header redirect
    } else {
        // Kalau gagal alihkan ke halaman index.php dengan status=gagal
        // Atau tampilkan error untuk debugging
        // echo "Gagal menyimpan data: " . mysqli_error($db); // Opsional untuk debugging
        header('Location: index.php?status=gagal');
        exit(); // Best Practice: Tambahkan exit() setelah header redirect
    }

} else {
    die("Akses dilarang...");
}

?>