<?php include 'includes/header.php'; ?>
<section class="relative h-screen flex items-center justify-center text-white">
    <div class="absolute inset-0 bg-black opacity-50"></div>
    <img src="assets/images/banner2.jpg" alt="Hero" class="absolute inset-0 w-full h-full object-cover">
    <div class="relative z-10 text-center px-4 reveal">
        <h1 class="text-4xl md:text-6xl font-bold mb-4">Selamat Datang di Wonton DD</h1>
        <p class="text-lg md:text-xl mb-8 max-w-2xl mx-auto">Nikmati wonton dan pangsit terenak di kota, dibuat dengan bahan-bahan pilihan dan resep turun-temurun.</p>
        <a href="menu.php" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-full transition transform hover:scale-105">Lihat Menu</a>
    </div>
</section>
<section class="py-16 reveal">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">Informasi Usaha</h2>
        <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-md">
            <p class="mb-2"><strong>Nama:</strong> Wonton DD</p>
            <p class="mb-2"><strong>Deskripsi:</strong> Usaha kuliner yang fokus pada penyajian wonton dan pangsit berkualitas tinggi.</p>
            <p><strong>Kontak Delivery:</strong> 0851-6980-6161 (Tersedia di GoFood dan GrabFood)</p>
        </div>
    </div>
</section>
<style>.reveal { opacity: 0; transform: translateY(20px); transition: opacity 0.6s ease-out, transform 0.6s ease-out; } .reveal.revealed { opacity: 1; transform: translateY(0); }</style>
<?php include 'includes/footer.php'; ?>