<?php
include 'koneksi.php'; // Hubungkan ke database
check_login();

$message = "";

// 1. LOGIKA HAPUS DATA
if (isset($_GET['action']) && $_GET['action'] == 'hapus' && isset($_GET['id'])) {
    $id_hapus = (int)$_GET['id'];

    $stmt = $koneksi->prepare("DELETE FROM pesanan WHERE id_pesanan = ?");
    $stmt->bind_param("i", $id_hapus);

    if ($stmt->execute()) {
        $message = "<blockquote style='color: #004d40; background-color: #e0f2f1; border-left: 5px solid #00897b; padding: 10px; margin: 15px auto; max-width: 1000px;'>Data pesanan ID: $id_hapus berhasil dihapus.</blockquote>";
    } else {
        $message = "<blockquote style='color: #d84315; background-color: #fce4ec; border-left: 5px solid #d84315; padding: 10px; margin: 15px auto; max-width: 1000px;'>Error saat menghapus data: " . $stmt->error . "</blockquote>";
    }
    $stmt->close();
}

// 2. AMBIL SEMUA DATA PESANAN UNTUK DITAMPILKAN
$result = $koneksi->query("SELECT * FROM pesanan ORDER BY tanggal_pesan DESC");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifikasi Pesanan - Gunung Bromo</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Adaptasi Styling Tabel (FASE 4: Adaptasi Styling) */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            font-size: 0.9em;
        }
        .data-table th {
            background-color: #00695c; /* Sedikit lebih gelap dari header nav */
            color: white;
            text-transform: uppercase;
        }
        .data-table tr:nth-child(even) {
            background-color: #f4f4f4;
        }
        .data-table tr:hover {
            background-color: #e0f2f1; /* Warna hijau muda */
        }
        .action-btn {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-weight: bold;
            display: inline-block;
            margin: 2px 0;
            font-size: 0.8em;
        }
        .btn-edit { background-color: #ff9800; } /* Oranye */
        .btn-delete { background-color: #e53935; } /* Merah */
        .action-btn:hover { opacity: 0.9; }
    </style>
</head>
<body>

    <header class="main-header">
        <div class="header-content">
            <h1>Modifikasi Pesanan</h1>
            <p>Daftar & Pengelolaan Pemesanan</p>
        </div>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php#beranda">Beranda</a></li>
                <li><a href="pemesanan.php">Buat Pesanan Baru</a></li>
                <li><a href="logout.php" style="background-color: #d84315;">Logout</a></li>
            </ul>
        </nav>
    </header>
    
    <section class="content-section about-bromo">
        <h2>Daftar Pesanan Paket Wisata</h2>
        
        <?php echo $message; ?>

        <?php if ($result->num_rows > 0): ?>
            <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tgl Pesan</th>
                        <th>Nama/HP</th>
                        <th>Paket & Layanan</th>
                        <th>Durasi/Peserta</th>
                        <th>Harga Paket</th>
                        <th>Tagihan Akhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id_pesanan']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_pesan'])); ?></td>
                        <td>
                            **<?php echo htmlspecialchars($row['nama_pemesan']); ?>**<br>
                            <?php echo htmlspecialchars($row['no_hp']); ?>
                        </td>
                        <td>
                            **<?php echo htmlspecialchars($row['paket_wisata']); ?>**<br>
                            <small>Layanan: <?php echo htmlspecialchars($row['layanan_tambahan']); ?></small>
                        </td>
                        <td><?php echo $row['durasi_hari']; ?> Hari / <?php echo $row['jumlah_peserta']; ?> Orang</td>
                        <td>Rp <?php echo number_format($row['harga_paket'], 0, ',', '.'); ?></td>
                        <td>**Rp <?php echo number_format($row['jumlah_tagihan'], 0, ',', '.'); ?>**</td>
                        <td>
                            <a href="pemesanan.php?id=<?php echo $row['id_pesanan']; ?>" class="action-btn btn-edit">Edit</a>
                            <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $row['id_pesanan']; ?>)" class="action-btn btn-delete">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #555;">Belum ada data pesanan yang tersimpan.</p>
        <?php endif; ?>
        
    </section>

    <footer>
        <p>&copy; 2025 Wisata Gunung Bromo</p>
    </footer>

<script>
    // Konfirmasi Hapus Data
    function confirmDelete(id) {
        if (confirm("Apakah Anda yakin ingin menghapus data pesanan ID " + id + "?")) {
            window.location.href = 'modifikasi_pesanan.php?action=hapus&id=' + id;
        }
    }
</script>

</body>
</html>
<?php
$koneksi->close();
?>