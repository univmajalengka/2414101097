
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wonton DD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { 'poppins': ['Poppins', 'sans-serif'] } } } }
    </script>
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-gray-50">
    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-y-full transition-transform duration-300 z-50">
        <span id="toast-message">Item berhasil ditambahkan!</span>
    </div>

    <header class="bg-white shadow-md sticky top-0 z-40">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-orange-500">Wonton DD</a>
            
            <nav class="hidden md:flex space-x-6 items-center">
                <a href="index.php" class="text-gray-700 hover:text-orange-500 transition">Beranda</a>
                <a href="menu.php" class="text-gray-700 hover:text-orange-500 transition">Menu</a>
                <a href="cart.php" class="bg-orange-500 text-white px-4 py-2 rounded-full hover:bg-orange-600 transition flex items-center">
                    Keranjang
                    <span class="ml-2 bg-white text-orange-500 rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold" id="cart-count"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
                </a>

                <?php if (isset($_SESSION['user'])): ?>
                    <div class="relative group">
                        <button class="flex items-center text-gray-700 hover:text-orange-500 transition">
                            <svg class="w-6 h-6 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <?php echo htmlspecialchars($_SESSION['user']['nama_lengkap']); ?>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 invisible group-hover:visible">
                            <a href="profil.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil Saya</a>
                            <a href="logout_user.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="text-gray-700 hover:text-orange-500 transition">Login</a>
                    <a href="register.php" class="bg-orange-500 text-white px-4 py-2 rounded-full hover:bg-orange-600 transition">Daftar</a>
                <?php endif; ?>
            </nav>

            <button id="mobile-menu-button" class="md:hidden focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
            </button>
        </div>
        <nav id="mobile-menu" class="hidden md:hidden bg-white border-t">
            <a href="index.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Beranda</a>
            <a href="menu.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Menu</a>
            <a href="cart.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Keranjang</a>
            <?php if (isset($_SESSION['user'])): ?>
                <a href="profil.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profil Saya</a>
                <a href="logout_user.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
            <?php else: ?>
                <a href="login.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Login</a>
                <a href="register.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Daftar</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>