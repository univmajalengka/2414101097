<?php
include 'includes/header.php'; include 'includes/db.php';
 $error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama']; $email = $_POST['email']; $password = $_POST['password']; $password_confirm = $_POST['password_confirm']; $role = 'pelanggan';
    if (empty($nama) || empty($email) || empty($password)) $error = 'Semua field wajib diisi.';
    elseif ($password !== $password_confirm) $error = 'Password tidak cocok.';
    else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?"); $stmt->bind_param("s", $email); $stmt->execute(); $result = $stmt->get_result();
        if ($result->num_rows > 0) $error = 'Email sudah terdaftar.';
        else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (nama_lengkap, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nama, $email, $hashed_password, $role);
            if ($stmt->execute()) $success = 'Registrasi berhasil! Silakan login.';
            else $error = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    }
}
?>
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-center mb-6">Daftar Akun Baru</h2>
            <?php if ($error): ?><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert"><span class="block sm:inline"><?php echo $error; ?></span></div><?php endif; ?>
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert"><span class="block sm:inline"><?php echo $success; ?></span></div>
                <script>setTimeout(() => { window.location.href = 'login.php'; }, 2000);</script>
            <?php else: ?>
                <form action="register.php" method="POST">
                    <div class="mb-4"><label class="block text-gray-700 text-sm font-bold mb-2" for="nama">Nama Lengkap</label><input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500" id="nama" type="text" name="nama" required></div>
                    <div class="mb-4"><label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label><input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500" id="email" type="email" name="email" required></div>
                    <div class="mb-4"><label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label><input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500" id="password" type="password" name="password" required></div>
                    <div class="mb-6"><label class="block text-gray-700 text-sm font-bold mb-2" for="password_confirm">Konfirmasi Password</label><input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500" id="password_confirm" type="password" name="password_confirm" required></div>
                    <button class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">Daftar</button>
                </form>
                <p class="text-center text-gray-600 mt-4">Sudah punya akun? <a href="login.php" class="text-orange-500 hover:underline">Login di sini</a></p>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>