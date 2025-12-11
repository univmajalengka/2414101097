<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) header('Location: ../login.php');
include '../includes/db.php';

 $message = '';
 $message_type = 'success';

// --- PROSES TAMBAH & UPDATE MENU (Digabung untuk efisiensi) ---
if (isset($_POST['tambah_menu']) || isset($_POST['update_menu'])) {
    $id = $_POST['menu_id'] ?? null;
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $gambar = $_FILES['gambar']['name'];
    $is_update = isset($_POST['update_menu']);

    // Cek apakah ada gambar baru yang diupload (saat update)
    if ($is_update && empty($gambar)) {
        // Jika update dan tidak ada gambar baru, tidak perlu proses apapun untuk gambar
        $gambar_path = null;
    } else {
        $target_dir = "../assets/images/";
        $gambar_path = $target_dir . basename($gambar);
        if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $gambar_path)) {
            $message = "Gagal mengupload gambar.";
            $message_type = 'error';
            header("Location: kelola_menu.php?message=" . urlencode($message) . "&type=" . $message_type);
            exit();
        }
    }

    if ($is_update) {
        // Update menu
        $sql = $gambar_path ? "UPDATE menu SET nama_menu=?, deskripsi=?, harga=?, gambar=? WHERE id=?" : "UPDATE menu SET nama_menu=?, deskripsi=?, harga=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $params = $gambar_path ? "ssdsi" : "ssdi";
        $bind_params = $gambar_path ? [$nama, $deskripsi, $harga, basename($gambar_path), $id] : [$nama, $deskripsi, $harga, $id];
        $stmt->bind_param($params, ...$bind_params);
        $success_message = "Menu berhasil diperbarui!";
    } else {
        // Tambah menu
        $stmt = $conn->prepare("INSERT INTO menu (nama_menu, deskripsi, harga, gambar) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $nama, $deskripsi, $harga, basename($gambar_path));
        $success_message = "Menu berhasil ditambahkan!";
    }

    if ($stmt->execute()) {
        $message = $success_message;
    } else {
        $message = "Terjadi kesalahan pada database.";
        $message_type = 'error';
    }
    header("Location: kelola_menu.php?message=" . urlencode($message) . "&type=" . $message_type);
    exit();
}

// --- PROSES HAPUS MENU ---
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("SELECT gambar FROM menu WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $menu = $stmt->get_result()->fetch_assoc();
    if ($menu && file_exists("../assets/images/" . $menu['gambar'])) {
        unlink("../assets/images/" . $menu['gambar']);
    }
    $stmt = $conn->prepare("DELETE FROM menu WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header('Location: kelola_menu.php');
    exit();
}

 $menu_result = $conn->query("SELECT * FROM menu ORDER BY nama_menu ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Menu - Wonton DD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary-color: #fb923c; --primary-dark: #ea580c; --sidebar-bg: #1f2937; --card-bg: #ffffff; --text-primary: #111827; --text-secondary: #6b7280; --border-color: #e5e7eb; }
        body { font-family: 'Inter', 'Poppins', sans-serif; }
        /* Custom File Input */
        .file-input-wrapper { position: relative; overflow: hidden; display: inline-block; }
        .file-input-wrapper input[type=file] { position: absolute; left: -9999px; }
        .file-input-label { cursor: pointer; }
        /* Animasi untuk kartu */
        .menu-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .menu-card:hover { transform: translateY(-8px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        /* Loading Spinner */
        .spinner { border: 3px solid #f3f3f3; border-top: 3px solid var(--primary-color); border-radius: 50%; width: 20px; height: 20px; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">
<div class="flex h-screen">
    <!-- SIDEBAR -->
    <aside class="w-64 bg-gradient-to-b from-gray-800 to-gray-900 text-white flex flex-col">
        <div class="p-6"><h2 class="text-2xl font-bold flex items-center"><i class="fas fa-bowl-food mr-3 text-orange-500"></i>Wonton DD</h2></div>
        <nav class="flex-1 mt-6">
            <a href="index.php" class="flex items-center py-3 px-4 hover:bg-gray-700 transition-colors"><i class="fas fa-chart-line w-5 mr-3"></i> Dashboard</a>
            <a href="kelola_pesanan.php" class="flex items-center py-3 px-4 hover:bg-gray-700 transition-colors"><i class="fas fa-receipt w-5 mr-3"></i> Kelola Pesanan</a>
            <a href="kelola_menu.php" class="flex items-center py-3 px-4 bg-gray-700 border-l-4 border-orange-500"><i class="fas fa-utensils w-5 mr-3"></i> Kelola Menu</a>
            <a href="logout.php" class="flex items-center py-3 px-4 hover:bg-gray-700 transition-colors mt-auto"><i class="fas fa-sign-out-alt w-5 mr-3"></i> Logout</a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-6 lg:p-8 overflow-auto bg-gray-100">
        <!-- Alert Message -->
        <?php if (isset($_GET['message'])): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $_GET['type'] === 'error' ? 'bg-red-100 border border-red-400 text-red-700' : 'bg-green-100 border border-green-400 text-green-700'; ?>" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_GET['message']); ?></span>
            </div>
        <?php endif; ?>

        <!-- FORM SECTION -->
        <div id="form-container" class="bg-white p-6 rounded-xl shadow-lg mb-8 border-l-4 border-transparent transition-all duration-300">
            <h2 id="formTitle" class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-plus-circle mr-3 text-orange-500"></i> Tambah Menu Baru
            </h2>
            <form id="menuForm" action="kelola_menu.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <input type="hidden" id="menuId" name="menu_id">
                
                <div class="md:col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="nama"><i class="fas fa-tag mr-1 text-gray-500"></i> Nama Menu</label>
                    <input type="text" id="nama" name="nama" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="deskripsi"><i class="fas fa-align-left mr-1 text-gray-500"></i> Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="3" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"></textarea>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="harga"><i class="fas fa-money-bill-wave mr-1 text-gray-500"></i> Harga</label>
                    <input type="number" id="harga" name="harga" step="0.01" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="gambar"><i class="fas fa-image mr-1 text-gray-500"></i> Gambar</label>
                    <div class="file-input-wrapper">
                        <label for="gambar" class="file-input-label block w-full px-4 py-3 border border-gray-300 rounded-lg cursor-pointer bg-white hover:bg-gray-50 text-center">
                            <i class="fas fa-cloud-upload-alt mr-2"></i> <span id="file-label-text">Pilih file gambar...</span>
                        </label>
                        <input type="file" id="gambar" name="gambar" accept="image/*" onchange="document.getElementById('file-label-text').textContent = this.files[0].name">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah gambar (saat edit).</p>
                </div>
                <div class="md:col-span-2 flex gap-4">
                    <button type="submit" id="submitBtn" name="tambah_menu" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline transition flex items-center">
                        <i class="fas fa-save mr-2"></i> <span id="submitBtnText">Tambah Menu</span>
                        <div id="submitSpinner" class="spinner ml-2 hidden"></div>
                    </button>
                    <button type="button" id="cancelBtn" class="hidden bg-gray-400 hover:bg-gray-500 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline transition">
                        <i class="fas fa-times mr-2"></i> Batal
                    </button>
                </div>
            </form>
        </div>

        <!-- MENU CARDS GRID -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php while($row = $menu_result->fetch_assoc()): ?>
            <div class="menu-card bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="relative">
                    <img src="../assets/images/<?php echo $row['gambar']; ?>" alt="<?php echo $row['nama_menu']; ?>" class="w-full h-48 object-cover">
                    <div class="absolute top-2 right-2 bg-white rounded-full p-2 shadow-md">
                        <span class="text-lg font-bold text-orange-500">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></span>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-lg text-gray-900 mb-1"><?php echo $row['nama_menu']; ?></h3>
                    <p class="text-sm text-gray-600 mb-4"><?php echo $row['deskripsi']; ?></p>
                    <div class="flex justify-between">
                        <button onclick="editMenu(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nama_menu']); ?>', '<?php echo addslashes($row['deskripsi']); ?>', <?php echo $row['harga']; ?>, '<?php echo $row['gambar']; ?>')" class="text-blue-500 hover:text-blue-700 font-semibold transition-colors">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <a href="kelola_menu.php?delete_id=<?php echo $row['id']; ?>" class="text-red-500 hover:text-red-700 font-semibold transition-colors" onclick="return confirm('Apakah Anda yakin ingin menghapus menu \"<?php echo addslashes($row['nama_menu']); ?>\"?');">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('menuForm');
    const formContainer = document.getElementById('form-container');
    const formTitle = document.getElementById('formTitle');
    const submitBtn = document.getElementById('submitBtn');
    const submitBtnText = document.getElementById('submitBtnText');
    const submitSpinner = document.getElementById('submitSpinner');
    const cancelBtn = document.getElementById('cancelBtn');
    const menuIdInput = document.getElementById('menuId');

    // Fungsi untuk mengisi form saat edit
    window.editMenu = function(id, nama, deskripsi, harga, gambar) {
        formContainer.classList.add('border-l-orange-500');
        formTitle.innerHTML = '<i class="fas fa-edit mr-3 text-blue-500"></i> Perbarui Menu';
        submitBtnText.textContent = 'Perbarui Menu';
        submitBtn.name = 'update_menu';
        cancelBtn.classList.remove('hidden');

        menuIdInput.value = id;
        document.getElementById('nama').value = nama;
        document.getElementById('deskripsi').value = deskripsi;
        document.getElementById('harga').value = harga;
        document.getElementById('file-label-text').textContent = gambar; // Tampilkan nama gambar lama
        
        // Scroll ke form
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };

    // Fungsi untuk reset form ke mode tambah
    function resetFormToAdd() {
        formContainer.classList.remove('border-l-orange-500');
        formTitle.innerHTML = '<i class="fas fa-plus-circle mr-3 text-orange-500"></i> Tambah Menu Baru';
        submitBtnText.textContent = 'Tambah Menu';
        submitBtn.name = 'tambah_menu';
        cancelBtn.classList.add('hidden');
        form.reset();
        document.getElementById('file-label-text').textContent = 'Pilih file gambar...';
        menuIdInput.value = '';
    }

    // Event listener untuk tombol batal
    cancelBtn.addEventListener('click', resetFormToAdd);

    // Event listener untuk submit form (menampilkan loading)
    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitSpinner.classList.remove('hidden');
    });
});
</script>
</body>
</html>