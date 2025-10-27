<?php
session_start();
if (isset($_SESSION['user'])) header('Location: profil.php');
if (isset($_SESSION['admin_logged_in'])) header('Location: admin/index.php');
include 'includes/header.php';
 $error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
unset($_SESSION['login_error']);
?>
<section class="py-16 flex items-center justify-center" style="min-height: calc(100vh - 200px);">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-center mb-6">Masuk ke Akun Anda</h2>
            <?php if ($error): ?><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert"><span class="block sm:inline"><?php echo $error; ?></span></div><?php endif; ?>
            <form action="proses_login.php" method="POST">
                <div class="mb-4"><label class="block text-gray-700 text-sm font-bold mb-2" for="identifier">Email</label><input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500" id="identifier" type="text" name="identifier" required></div>
                <div class="mb-6"><label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label><input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500" id="password" type="password" name="password" required></div>
                <button class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">Masuk</button>
            </form>
            <p class="text-center text-gray-600 mt-4">Belum punya akun? <a href="register.php" class="text-orange-500 hover:underline">Daftar di sini</a></p>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>