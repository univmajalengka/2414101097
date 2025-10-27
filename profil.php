<?php
session_start();
if (!isset($_SESSION['user'])) header('Location: login.php');
include 'includes/header.php';
include 'includes/db.php';
 $user_id = $_SESSION['user']['id'];
 $stmt = $conn->prepare("SELECT * FROM pemesanan WHERE id_user = ? ORDER BY tanggal_pesan DESC");
 $stmt->bind_param("i", $user_id); $stmt->execute(); $pesanan_result = $stmt->get_result();
?>
<section class="py-16">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold text-center mb-8">Profil Saya</h1>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1"><div class="bg-white p-6 rounded-lg shadow-md"><h2 class="text-xl font-semibold mb-4">Informasi Akun</h2><p><strong>Nama:</strong> <?php echo htmlspecialchars($_SESSION['user']['nama_lengkap']); ?></p><p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['user']['email']); ?></p></div></div>
            <div class="lg:col-span-2">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Riwayat Pesanan</h2>
                    <?php if ($pesanan_result->num_rows > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead><tr class="border-b"><th class="text-left py-2">ID Pesanan</th><th class="text-left py-2">Tanggal</th><th class="text-left py-2">Total</th><th class="text-left py-2">Status</th></tr></thead>
                                <tbody>
                                    <?php while($row = $pesanan_result->fetch_assoc()): ?>
                                    <tr class="border-b">
                                        <td class="py-2">#<?php echo $row['id']; ?></td>
                                        <td class="py-2"><?php echo date('d M Y', strtotime($row['tanggal_pesan'])); ?></td>
                                        <td class="py-2">Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                        <td class="py-2"><span class="px-2 py-1 text-xs rounded-full <?php echo $row['status'] == 'Menunggu Konfirmasi' ? 'bg-yellow-100 text-yellow-800' : ($row['status'] == 'Dikonfirmasi' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'); ?>"><?php echo $row['status']; ?></span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-600">Anda belum memiliki riwayat pesanan.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>