<?php
session_start(); include 'includes/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['menu_id'])) {
    $menu_id = $_POST['menu_id']; $stmt = $conn->prepare("SELECT * FROM menu WHERE id = ?"); $stmt->bind_param("i", $menu_id); $stmt->execute(); $menu = $stmt->get_result()->fetch_assoc();
    if ($menu) { if (isset($_SESSION['cart'][$menu_id])) $_SESSION['cart'][$menu_id]['quantity']++; else $_SESSION['cart'][$menu_id] = ['name' => $menu['nama_menu'], 'price' => $menu['harga'], 'quantity' => 1]; echo json_encode(['status' => 'success', 'message' => 'Item ditambahkan!', 'cart_count' => count($_SESSION['cart'])]); } else echo json_encode(['status' => 'error', 'message' => 'Menu tidak ditemukan.']); exit();
}
if (isset($_GET['remove'])) { unset($_SESSION['cart'][$_GET['remove']]); echo json_encode(['status' => 'success', 'message' => 'Item dihapus.']); exit(); }
include 'includes/header.php'; $total_harga = 0;
?>
<section class="py-16">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold text-center mb-8 text-gray-800">Keranjang Belanja Anda</h1>
        <?php if (empty($_SESSION['cart'])): ?>
            <div class="text-center"><p class="text-xl text-gray-600">Keranjang Anda kosong.</p><a href="menu.php" class="mt-4 inline-block bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-full transition">Kembali ke Menu</a></div>
        <?php else: ?>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <table class="w-full">
                    <thead><tr class="border-b"><th class="text-left pb-4">Nama Menu</th><th class="text-left pb-4">Harga</th><th class="text-center pb-4">Jumlah</th><th class="text-right pb-4">Subtotal</th><th class="text-center pb-4">Aksi</th></tr></thead>
                    <tbody>
                        <?php foreach ($_SESSION['cart'] as $id => $item): $total_harga += $item['price'] * $item['quantity']; ?>
                        <tr class="border-b">
                            <td class="py-4"><?php echo $item['name']; ?></td>
                            <td class="py-4">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                            <td class="py-4 text-center"><?php echo $item['quantity']; ?></td>
                            <td class="py-4 text-right">Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                            <td class="py-4 text-center"><a href="cart.php?remove=<?php echo $id; ?>" class="text-red-500 hover:text-red-700 font-semibold" data-remove-from-cart="<?php echo $id; ?>">Hapus</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr><td colspan="3" class="pt-4 text-lg font-semibold text-right">Total:</td><td colspan="2" class="pt-4 text-lg font-bold text-orange-500">Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></td></tr></tfoot>
                </table>
                <div class="flex justify-between mt-6"><a href="menu.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-full transition">Lanjut Belanja</a><a href="checkout.php" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-full transition">Checkout</a></div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php include 'includes/footer.php'; ?>