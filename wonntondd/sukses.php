<?php
session_start();
include 'includes/db.php';

// Pastikan ada ID pemesanan di URL
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id_pemesanan = intval($_GET['id']);

// Ambil data utama pesanan
$stmt = $conn->prepare("SELECT * FROM pemesanan WHERE id = ?");
$stmt->bind_param("i", $id_pemesanan);
$stmt->execute();
$pemesanan = $stmt->get_result()->fetch_assoc();

if (!$pemesanan) {
    echo "<h2>Pesanan tidak ditemukan.</h2>";
    exit();
}

// Ambil detail pesanan (JOIN ke tabel menu untuk ambil nama menu)
$query_detail = "
    SELECT m.nama_menu, dp.jumlah, dp.harga_satuan, (dp.jumlah * dp.harga_satuan) AS subtotal
    FROM detail_pemesanan dp
    JOIN menu m ON dp.id_menu = m.id
    WHERE dp.id_pemesanan = ?
";
$stmt_detail = $conn->prepare($query_detail);
$stmt_detail->bind_param("i", $id_pemesanan);
$stmt_detail->execute();
$detail_result = $stmt_detail->get_result();

include 'includes/header.php';
?>

<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="bg-white p-8 rounded-lg shadow-md max-w-3xl mx-auto">
            <h1 class="text-4xl font-bold text-center text-orange-500 mb-6">Pesanan Berhasil!</h1>

            <div class="text-center mb-8">
                <p class="text-lg text-gray-700">Terima kasih <strong><?php echo htmlspecialchars($pemesanan['nama_pemesan']); ?></strong>, pesananmu telah kami terima 🎉</p>
                <p class="text-gray-600 mt-2">Nomor Pesanan: <strong>#<?php echo $pemesanan['id']; ?></strong></p>
            </div>

            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-3">Detail Pesanan</h2>
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-100 border-b">
                            <th class="text-left p-3">Nama Menu</th>
                            <th class="text-center p-3">Jumlah</th>
                            <th class="text-right p-3">Harga Satuan</th>
                            <th class="text-right p-3">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = 0;
                        while ($row = $detail_result->fetch_assoc()): 
                            $total += $row['subtotal'];
                        ?>
                        <tr class="border-b">
                            <td class="p-3"><?php echo htmlspecialchars($row['nama_menu']); ?></td>
                            <td class="text-center p-3"><?php echo $row['jumlah']; ?></td>
                            <td class="text-right p-3">Rp <?php echo number_format($row['harga_satuan'], 0, ',', '.'); ?></td>
                            <td class="text-right p-3">Rp <?php echo number_format($row['subtotal'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right p-3 font-bold text-lg">Total:</td>
                            <td class="text-right p-3 font-bold text-orange-500 text-lg">
                                Rp <?php echo number_format($total, 0, ',', '.'); ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <?php if (!empty($pemesanan['catatan'])): ?>
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Catatan</h3>
                <p class="text-gray-700 bg-gray-50 p-3 rounded-md border"><?php echo nl2br(htmlspecialchars($pemesanan['catatan'])); ?></p>
            </div>
            <?php endif; ?>

            <div class="text-center mt-8">
                <a href="menu.php" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-full transition">
                    Kembali ke Menu
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
