<?php
session_start();
if (empty($_SESSION['cart'])) header('Location: menu.php');
 $total_harga = 0; foreach ($_SESSION['cart'] as $item) $total_harga += $item['price'] * $item['quantity'];
include 'includes/header.php';
?>
<section class="py-16">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold text-center mb-8 text-gray-800">Checkout</h1>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-semibold mb-4">Ringkasan Pesanan</h2>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                <div class="flex justify-between mb-2"><span><?php echo $item['name']; ?> (x<?php echo $item['quantity']; ?>)</span><span>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></span></div>
                <?php endforeach; ?>
                <hr class="my-4">
                <div class="flex justify-between text-xl font-bold"><span>Total</span><span class="text-orange-500">Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></span></div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-semibold mb-4">Informasi Pemesan</h2>
                <?php if (isset($_SESSION['user'])): ?>
                    <p class="mb-4">Pesanan akan dibuat atas nama: <strong><?php echo htmlspecialchars($_SESSION['user']['nama_lengkap']); ?></strong></p>
                    <form action="proses_pesanan.php" method="POST"><input type="hidden" name="nama_pemesan" value="<?php echo htmlspecialchars($_SESSION['user']['nama_lengkap']); ?>"><button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition">Konfirmasi Pesanan</button></form>
                <?php else: ?>
                    <p class="mb-4 text-sm text-gray-600">Atau <a href="login.php" class="text-orange-500 hover:underline">login</a> untuk checkout lebih cepat.</p>
                    <form action="proses_pesanan.php" method="POST">
                        <div class="mb-4"><label class="block text-gray-700 text-sm font-bold mb-2" for="nama_pemesan">Nama Lengkap</label><input type="text" id="nama_pemesan" name="nama_pemesan" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500"></div>
                        <div class="mb-4"><label class="block text-gray-700 text-sm font-bold mb-2" for="catatan">Catatan (Opsional)</label><textarea id="catatan" name="catatan" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500"></textarea></div>
                        <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition">Konfirmasi Pesanan</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>