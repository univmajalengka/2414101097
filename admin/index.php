<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../includes/db.php';

// ==== AJAX HANDLER ====
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    if ($_GET['action'] === 'sales_data') {
        $labels = [];
        $values = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('d M', strtotime($date));
            $stmt = $conn->prepare("SELECT SUM(total_harga) AS total FROM pemesanan WHERE DATE(tanggal_pesan) = ?");
            $stmt->bind_param("s", $date);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $values[] = $res['total'] ?? 0;
        }
        echo json_encode(['labels' => $labels, 'values' => $values]);
        exit;
    }

    if ($_GET['action'] === 'order_details' && isset($_GET['id'])) {
        $order_id = intval($_GET['id']);
        $stmt = $conn->prepare("
            SELECT dp.jumlah, dp.harga_satuan, m.nama_menu 
            FROM detail_pemesanan dp 
            JOIN menu m ON dp.id_menu = m.id 
            WHERE dp.id_pemesanan = ?
        ");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $details = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmt_note = $conn->prepare("SELECT catatan FROM pemesanan WHERE id = ?");
        $stmt_note->bind_param("i", $order_id);
        $stmt_note->execute();
        $note = $stmt_note->get_result()->fetch_assoc();

        echo json_encode(['details' => $details, 'catatan' => $note['catatan'] ?? null]);
        exit;
    }

    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// ==== STATISTIK ====
 $total_pesanan = $conn->query("SELECT COUNT(*) AS count FROM pemesanan")->fetch_assoc()['count'];
 $pesanan_menunggu = $conn->query("SELECT COUNT(*) AS count FROM pemesanan WHERE status = 'Menunggu Konfirmasi'")->fetch_assoc()['count'];
 $total_menu = $conn->query("SELECT COUNT(*) AS count FROM menu")->fetch_assoc()['count'];

// ==== PESANAN TERBARU ====
 $recent_orders = $conn->query("
    SELECT p.id, COALESCE(u.nama_lengkap, p.nama_pemesan) AS nama_pemesan, 
           p.total_harga, p.status, p.tanggal_pesan
    FROM pemesanan p
    LEFT JOIN users u ON p.id_user = u.id
    ORDER BY p.tanggal_pesan DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Wonton DD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #fb923c;
            --primary-dark: #ea580c;
            --sidebar-bg: #1f2937;
            --card-bg: #ffffff;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
        }
        body { font-family: 'Inter', 'Poppins', sans-serif; }
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #555; }
        /* Stat Card Hover */
        .stat-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .stat-card:hover { transform: translateY(-8px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        /* Table Hover */
        .table-row-hover { transition: background-color 0.2s ease-in-out; }
        /* Modal */
        .modal-backdrop { backdrop-filter: blur(5px); }
        .modal-content { animation: modalFadeIn 0.3s ease-out; }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

<div class="flex h-screen">
    <!-- SIDEBAR -->
    <aside class="w-64 bg-gradient-to-b from-gray-800 to-gray-900 text-white flex flex-col">
        <div class="p-6">
            <h2 class="text-2xl font-bold flex items-center">
                <i class="fas fa-bowl-food mr-3 text-orange-500"></i>Wonton DD
            </h2>
            <p class="text-xs text-gray-400 mt-1">Admin Panel</p>
        </div>
        <nav class="flex-1 mt-6">
            <a href="index.php" class="flex items-center py-3 px-4 bg-gray-700 border-l-4 border-orange-500">
                <i class="fas fa-chart-line w-5 mr-3"></i> Dashboard
            </a>
            <a href="kelola_pesanan.php" class="flex items-center py-3 px-4 hover:bg-gray-700 transition-colors">
                <i class="fas fa-receipt w-5 mr-3"></i> Kelola Pesanan
            </a>
            <a href="kelola_menu.php" class="flex items-center py-3 px-4 hover:bg-gray-700 transition-colors">
                <i class="fas fa-utensils w-5 mr-3"></i> Kelola Menu
            </a>
            <a href="logout.php" class="flex items-center py-3 px-4 hover:bg-gray-700 transition-colors mt-auto">
                <i class="fas fa-sign-out-alt w-5 mr-3"></i> Logout
            </a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-6 lg:p-8 overflow-auto bg-gray-100">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-600">Selamat datang kembali, <span class="font-semibold text-orange-500"><?= htmlspecialchars($_SESSION['admin_user']['nama']); ?></span>!</p>
        </div>

        <!-- STATISTIK KARTU -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="stat-card bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Pesanan</p>
                        <h2 class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($total_pesanan, 0, ',', '.'); ?></h2>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-shopping-bag text-blue-500 text-2xl"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card bg-white p-6 rounded-xl shadow-lg border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Menunggu Konfirmasi</p>
                        <h2 class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($pesanan_menunggu, 0, ',', '.'); ?></h2>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-clock text-yellow-500 text-2xl"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Menu</p>
                        <h2 class="text-3xl font-bold text-gray-900 mt-1"><?= number_format($total_menu, 0, ',', '.'); ?></h2>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-utensils text-green-500 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- GRAFIK PENJUALAN -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h2 class="text-lg font-semibold mb-4 text-gray-900">Penjualan 7 Hari Terakhir</h2>
                <div class="relative h-64">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- TABEL PESANAN TERBARU -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h2 class="text-lg font-semibold mb-4 text-gray-900">Pesanan Terbaru</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="py-3 text-left font-semibold text-gray-700">ID</th>
                                <th class="py-3 text-left font-semibold text-gray-700">Pemesan</th>
                                <th class="py-3 text-left font-semibold text-gray-700">Total</th>
                                <th class="py-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="py-3 text-center font-semibold text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($recent_orders->num_rows > 0): ?>
                            <?php while ($row = $recent_orders->fetch_assoc()): ?>
                            <tr class="border-b border-gray-100 table-row-hover">
                                <td class="py-3">#<?= $row['id']; ?></td>
                                <td class="py-3 font-medium"><?= htmlspecialchars($row['nama_pemesan']); ?></td>
                                <td class="py-3">Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                <td class="py-3">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        <?= $row['status'] == 'Menunggu Konfirmasi' ? 'bg-yellow-100 text-yellow-800' :
                                           ($row['status'] == 'Dikonfirmasi' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'); ?>">
                                        <?= $row['status']; ?>
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    <button onclick="viewDetails(<?= $row['id']; ?>)" class="text-orange-500 hover:text-orange-700 font-semibold transition-colors">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-500">Belum ada pesanan.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- MODAL DETAIL PESANAN -->
<div id="orderModal" class="modal-backdrop fixed inset-0 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="modal-content bg-white rounded-xl shadow-2xl w-full max-w-md">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Detail Pesanan</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="orderContent" class="p-4 space-y-3 text-sm text-gray-700">
                <!-- Content will be loaded here by JS -->
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Initialize Chart ---
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    fetch('?action=sales_data')
        .then(res => res.json())
        .then(data => {
            new Chart(salesCtx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Penjualan',
                        data: data.values,
                        backgroundColor: 'rgba(251, 146, 60, 0.6)',
                        borderColor: 'rgba(251, 146, 60, 1)',
                        borderWidth: 1,
                        borderRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        });

    // --- Modal Functions ---
    window.viewDetails = function(id) {
        const modal = document.getElementById('orderModal');
        const content = document.getElementById('orderContent');
        
        // Show loading state
        content.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-2xl text-orange-500"></i></div>';
        modal.classList.remove('hidden');

        fetch(`?action=order_details&id=${id}`)
            .then(res => res.json())
            .then(data => {
                let detailsHtml = data.details.map(d => `
                    <div class="flex justify-between items-center py-2 border-b last:border-b-0">
                        <span class="font-medium">${d.nama_menu}</span>
                        <span class="text-gray-600">${d.jumlah} x Rp ${parseInt(d.harga_satuan).toLocaleString('id-ID')}</span>
                    </div>`).join('');
                
                if (data.catatan) {
                    detailsHtml += `<div class="mt-4 p-3 bg-gray-100 rounded-lg italic text-gray-600"><strong>Catatan:</strong> ${data.catatan}</div>`;
                }
                content.innerHTML = detailsHtml;
            })
            .catch(error => {
                console.error('Error fetching order details:', error);
                content.innerHTML = '<p class="text-center text-red-500">Gagal memuat detail.</p>';
            });
    };

    window.closeModal = function() {
        document.getElementById('orderModal').classList.add('hidden');
    };
});
</script>

</body>
</html>