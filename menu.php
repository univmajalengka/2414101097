<?php
include 'includes/header.php';
include 'includes/db.php';
 $result = $conn->query("SELECT * FROM menu ORDER BY nama_menu ASC");
?>
<section class="py-16">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold text-center mb-12 text-gray-800">Menu Kami</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php while($row = $result->fetch_assoc()): ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition duration-300 hover:scale-105 reveal">
                <img src="assets/images/<?php echo $row['gambar']; ?>" alt="<?php echo $row['nama_menu']; ?>" class="w-full h-48 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-2"><?php echo $row['nama_menu']; ?></h3>
                    <p class="text-gray-600 mb-4"><?php echo $row['deskripsi']; ?></p>
                    <h4 class="text-2xl font-bold text-orange-500 mb-4">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></h4>
                    <form action="cart.php" method="POST" data-add-to-cart>
                        <input type="hidden" name="menu_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 rounded transition">Tambah ke Keranjang</button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<style>.reveal { opacity: 0; transform: translateY(20px); transition: opacity 0.6s ease-out, transform 0.6s ease-out; } .reveal.revealed { opacity: 1; transform: translateY(0); }</style>
<?php include 'includes/footer.php'; ?>