<?php
session_start();

// 🔒 Cek apakah admin sudah login
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/db.php';

// ✅ Tangani konfirmasi pesanan (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_id'])) {
    $confirmId = intval($_POST['confirm_id']);

    // Pastikan ID valid dan pesanan masih menunggu konfirmasi
    $check = $conn->prepare("SELECT id FROM pemesanan WHERE id = ? AND status = 'Menunggu Konfirmasi'");
    $check->bind_param("i", $confirmId);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $update = $conn->prepare("UPDATE pemesanan SET status = 'Dikonfirmasi' WHERE id = ?");
        $update->bind_param("i", $confirmId);
        $update->execute();
    }

    header('Location: kelola_pesanan.php');
    exit();
}

// 🧾 Ambil semua data pesanan
$query = "
    SELECT 
        p.id, 
        COALESCE(u.nama_lengkap, p.nama_pemesan) AS nama_pemesan,
        p.total_harga, 
        p.status, 
        p.tanggal_pesan
    FROM pemesanan p
    LEFT JOIN users u ON p.id_user = u.id
    ORDER BY p.tanggal_pesan DESC
";
$pesanan_result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pesanan - Wonton DD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-100">

<div class="flex h-screen">
    <!-- SIDEBAR -->
    <aside class="w-64 bg-gradient-to-b from-gray-800 to-gray-900 text-white">
        <div class="p-6">
            <h2 class="text-2xl font-bold flex items-center">
                <i class="fas fa-bowl-food mr-2 text-orange-500"></i>Wonton DD Admin
            </h2>
        </div>
        <nav class="mt-4">
            <a href="index.php" class="flex items-center py-3 px-4 hover:bg-gray-700 transition"><i class="fas fa-chart-line w-5 mr-3"></i>Dashboard</a>
            <a href="kelola_pesanan.php" class="flex items-center py-3 px-4 bg-gray-700 hover:bg-gray-600 transition"><i class="fas fa-receipt w-5 mr-3"></i>Kelola Pesanan</a>
            <a href="kelola_menu.php" class="flex items-center py-3 px-4 hover:bg-gray-700 transition"><i class="fas fa-utensils w-5 mr-3"></i>Kelola Menu</a>
            <a href="logout.php" class="flex items-center py-3 px-4 hover:bg-gray-700 transition"><i class="fas fa-sign-out-alt w-5 mr-3"></i>Logout</a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-8 bg-gray-50 overflow-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Kelola Pesanan</h1>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-3 border-b-2 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">ID Pesanan</th>
                        <th class="px-5 py-3 border-b-2 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Pemesan</th>
                        <th class="px-5 py-3 border-b-2 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                        <th class="px-5 py-3 border-b-2 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Total</th>
                        <th class="px-5 py-3 border-b-2 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-5 py-3 border-b-2 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pesanan_result && $pesanan_result->num_rows > 0): ?>
                        <?php while ($row = $pesanan_result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-5 border-b bg-white text-sm font-medium">#<?= $row['id']; ?></td>
                                <td class="px-5 py-5 border-b bg-white text-sm"><?= htmlspecialchars($row['nama_pemesan']); ?></td>
                                <td class="px-5 py-5 border-b bg-white text-sm"><?= date('d M Y, H:i', strtotime($row['tanggal_pesan'])); ?></td>
                                <td class="px-5 py-5 border-b bg-white text-sm font-semibold">Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                <td class="px-5 py-5 border-b bg-white text-sm">
                                    <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full
                                        <?= $row['status'] === 'Menunggu Konfirmasi' ? 'bg-yellow-100 text-yellow-800' : 
                                            ($row['status'] === 'Dikonfirmasi' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'); ?>">
                                        <?= htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td class="px-5 py-5 border-b bg-white text-sm">
                                    <?php if ($row['status'] === 'Menunggu Konfirmasi'): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="confirm_id" value="<?= $row['id']; ?>">
                                            <button type="submit" class="text-green-600 hover:text-green-900 font-semibold">
                                                <i class="fas fa-check-circle"></i> Konfirmasi
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <button class="text-blue-600 hover:text-blue-900 ml-2 font-semibold detail-btn"
                                        data-order-id="<?= $row['id']; ?>">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="px-5 py-5 text-center text-gray-500">Belum ada pesanan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- MODAL DETAIL -->
<div id="detailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Detail Pesanan</h3>
            <button id="closeModalBtn" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <div class="mt-2 px-7 py-3">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2">Menu</th>
                            <th class="text-center py-2">Qty</th>
                            <th class="text-right py-2">Harga</th>
                            <th class="text-right py-2">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="modal-body-content"></tbody>
                </table>
            </div>
            <div id="order-note-container" class="mt-4 hidden">
                <strong>Catatan:</strong>
                <p id="order-note-text" class="text-gray-700 bg-gray-100 p-2 rounded"></p>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT DETAIL MODAL -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('detailModal');
    const modalBody = document.getElementById('modal-body-content');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const noteContainer = document.getElementById('order-note-container');
    const noteText = document.getElementById('order-note-text');

    // 🔹 Tombol Detail
    document.querySelectorAll('.detail-btn').forEach(button => {
        button.addEventListener('click', async () => {
            const orderId = button.dataset.orderId;
            modalBody.innerHTML = `
                <tr><td colspan="4" class="py-4 text-center">
                    <i class="fas fa-spinner fa-spin"></i> Memuat data pesanan...
                </td></tr>
            `;
            noteContainer.classList.add('hidden');
            modal.classList.remove('hidden');

            try {
                const response = await fetch(`get_order_details.php?id=${orderId}`);
                const data = await response.json();

                if (!response.ok || data.error) throw new Error(data.error || 'Gagal memuat data.');

                modalBody.innerHTML = '';
                let total = 0;

                if (Array.isArray(data.details) && data.details.length > 0) {
                    data.details.forEach(item => {
                        const subtotal = item.jumlah * item.harga_satuan;
                        total += subtotal;
                        modalBody.innerHTML += `
                            <tr class="border-b">
                                <td class="py-2">${item.nama_menu || 'Menu Tidak Diketahui'}</td>
                                <td class="py-2 text-center">${item.jumlah}</td>
                                <td class="py-2 text-right">Rp ${Number(item.harga_satuan).toLocaleString('id-ID')}</td>
                                <td class="py-2 text-right">Rp ${subtotal.toLocaleString('id-ID')}</td>
                            </tr>`;
                    });

                    modalBody.innerHTML += `
                        <tr>
                            <td colspan="3" class="text-right font-bold py-2">Total:</td>
                            <td class="text-right font-bold py-2 text-green-700">Rp ${total.toLocaleString('id-ID')}</td>
                        </tr>`;
                } else {
                    modalBody.innerHTML = `
                        <tr><td colspan="4" class="py-3 text-center text-gray-500">
                            Tidak ada detail menu untuk pesanan ini.
                        </td></tr>`;
                }

                if (data.catatan && data.catatan.trim() !== '') {
                    noteText.textContent = data.catatan;
                    noteContainer.classList.remove('hidden');
                }
            } catch (err) {
                modalBody.innerHTML = `<tr><td colspan="4" class="py-3 text-center text-red-500">${err.message}</td></tr>`;
            }
        });
    });

    // 🔹 Tutup modal
    const closeModal = () => modal.classList.add('hidden');
    closeModalBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
});
</script>

</body>
</html>
