<?php
include 'koneksi.php';
check_login();

$error_message = "";
$success_message = "";
$is_editing = false;
$id_edit = null;

// Variabel default untuk formulir
$nama = $email = $telpon = $tgl_perjalanan = $durasi_hari = $jumlah_peserta = '';
$selected_paket = isset($_GET['paket']) ? sanitize_input($_GET['paket']) : ''; 
$layanan_tambahan = [];

// === LOGIKA HARGA ===
$HARGA_PENGINAPAN = 1000000; 
$HARGA_TRANSPORTASI = 1200000; 
$HARGA_MAKAN = 500000; 

function hitung_harga_tagihan($durasi, $peserta, $layanan) {
    global $HARGA_PENGINAPAN, $HARGA_TRANSPORTASI, $HARGA_MAKAN;

    $harga_paket_perjalanan = 0;
    
    if (in_array('Penginapan', $layanan)) { $harga_paket_perjalanan += $HARGA_PENGINAPAN; }
    if (in_array('Transportasi', $layanan)) { $harga_paket_perjalanan += $HARGA_TRANSPORTASI; }
    if (in_array('Service/Makan', $layanan)) { $harga_paket_perjalanan += $HARGA_MAKAN; }
    
    $jumlah_tagihan = $durasi * $peserta * $harga_paket_perjalanan;
    
    return ['harga_paket' => $harga_paket_perjalanan, 'jumlah_tagihan' => $jumlah_tagihan];
}
// ===================

// --- A. MODE EDIT: Memuat Data Lama ---
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_edit = (int)$_GET['id'];
    $is_editing = true;

    $stmt = $koneksi->prepare("SELECT * FROM pesanan WHERE id_pesanan = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $data = $result->fetch_assoc();
        
        // Isi variabel dengan data lama
        $nama = htmlspecialchars($data['nama_pemesan']);
        $email = htmlspecialchars($data['email']);
        $telpon = htmlspecialchars($data['no_hp']);
        $tgl_perjalanan = htmlspecialchars($data['tgl_perjalanan']);
        $selected_paket = htmlspecialchars($data['paket_wisata']);
        $durasi_hari = $data['durasi_hari'];
        $jumlah_peserta = $data['jumlah_peserta'];
        
        // Konversi string layanan ke array untuk checkbox
        $layanan_tambahan = explode(', ', $data['layanan_tambahan']);

    } else {
        $error_message = "Data pesanan tidak ditemukan.";
        $is_editing = false; // Kembali ke mode insert jika ID tidak valid
    }
    $stmt->close();
}


// --- B. PROSES FORM SUBMISSION (Insert atau Update) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Ambil input hidden ID untuk menentukan apakah ini Update
    $post_id_edit = isset($_POST['id_edit']) ? (int)sanitize_input($_POST['id_edit']) : null;

    // Ambil dan bersihkan data dari formulir
    $nama = sanitize_input($_POST['nama']);
    $email = sanitize_input($_POST['email']);
    $telpon = sanitize_input($_POST['telepon']);
    $tgl_perjalanan = sanitize_input($_POST['tanggal']);
    $paket = sanitize_input($_POST['paket']);
    $durasi_hari = (int)sanitize_input($_POST['durasi_hari']);
    $jumlah_peserta = (int)sanitize_input($_POST['jumlah_peserta']);
    $layanan_tambahan = isset($_POST['layanan_tambahan']) ? $_POST['layanan_tambahan'] : [];
    $layanan_db = implode(', ', $layanan_tambahan);
    
    // Validasi Dasar (PHP)
    if (empty($nama) || empty($email) || empty($telpon) || empty($tgl_perjalanan) || empty($paket) || $durasi_hari <= 0 || $jumlah_peserta <= 0) {
        $error_message = "Semua field bertanda bintang (*) harus diisi dengan benar.";
        
        // Jika validasi gagal saat edit, pastikan mode edit tetap aktif
        if ($post_id_edit) {
             $is_editing = true;
             $id_edit = $post_id_edit;
        }

    } else {
        // Lakukan Perhitungan Otomatis
        $perhitungan = hitung_harga_tagihan($durasi_hari, $jumlah_peserta, $layanan_tambahan);
        $harga_paket = $perhitungan['harga_paket'];
        $jumlah_tagihan = $perhitungan['jumlah_tagihan'];

        if ($post_id_edit) {
            // Logika UPDATE Data
            $stmt = $koneksi->prepare("UPDATE pesanan SET nama_pemesan=?, no_hp=?, email=?, tgl_perjalanan=?, paket_wisata=?, layanan_tambahan=?, durasi_hari=?, jumlah_peserta=?, harga_paket=?, jumlah_tagihan=? WHERE id_pesanan=?");
            $stmt->bind_param("ssssssiiddi", $nama, $telpon, $email, $tgl_perjalanan, $paket, $layanan_db, $durasi_hari, $jumlah_peserta, $harga_paket, $jumlah_tagihan, $post_id_edit);

            if ($stmt->execute()) {
                $success_message = "Perubahan pesanan ID: $post_id_edit berhasil disimpan!";
            } else {
                $error_message = "Terjadi kesalahan saat mengupdate data: " . $stmt->error;
            }
            $stmt->close();
            
            // Setelah update sukses, arahkan ke halaman modifikasi
            if (!isset($error_message)) {
                 header("Location: modifikasi_pesanan.php?status=updated");
                 exit();
            }

        } else {
            // Logika INSERT Data (Sama seperti sebelumnya)
            $stmt = $koneksi->prepare("INSERT INTO pesanan (nama_pemesan, no_hp, email, tgl_perjalanan, paket_wisata, layanan_tambahan, durasi_hari, jumlah_peserta, harga_paket, jumlah_tagihan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssiidd", $nama, $telpon, $email, $tgl_perjalanan, $paket, $layanan_db, $durasi_hari, $jumlah_peserta, $harga_paket, $jumlah_tagihan);
            
            if ($stmt->execute()) {
                $success_message = "Pemesanan berhasil disimpan! Total Tagihan: Rp " . number_format($jumlah_tagihan, 0, ',', '.') . ".";
                // Reset form input setelah sukses
                $nama = $email = $telpon = $tgl_perjalanan = $durasi_hari = $jumlah_peserta = '';
                $layanan_tambahan = [];
                $selected_paket = '';
            } else {
                $error_message = "Terjadi kesalahan saat menyimpan data: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_editing ? 'Edit Pesanan' : 'Form Pemesanan'; ?> - Gunung Bromo</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <header class="main-header">
        <div class="header-content">
            <h1><?php echo $is_editing ? 'Edit Pesanan ID: ' . $id_edit : 'Form Pemesanan'; ?></h1>
            <p>Aplikasi Wisata Gunung Bromo</p>
        </div>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php#beranda">Beranda</a></li>
                <li><a href="modifikasi_pesanan.php">Modifikasi Pesanan</a></li>
                <li><a href="logout.php" style="background-color: #d84315;">Logout</a></li>
            </ul>
        </nav>
    </header>
    
    <section class="content-section pemesanan-section">
        <h2><?php echo $is_editing ? 'Ubah Rincian Pesanan' : 'Pesan Paket Perjalanan Anda Sekarang!'; ?></h2>
        <p class="section-description">Isi/ubah formulir di bawah ini. Harga paket akan dihitung otomatis!</p>
        
        <?php if ($error_message): ?>
            <blockquote style="color: #d84315; background-color: #fce4ec; border-left: 5px solid #d84315; padding: 10px; margin: 15px auto; max-width: 600px;"><?php echo $error_message; ?></blockquote>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <blockquote style="color: #004d40; background-color: #e0f2f1; border-left: 5px solid #00897b; padding: 10px; margin: 15px auto; max-width: 600px;"><?php echo $success_message; ?></blockquote>
        <?php endif; ?>

        <div class="form-container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="pemesanan-form" onsubmit="return validateAndCalculate()"> 
                
                <?php if ($is_editing): ?>
                    <input type="hidden" name="id_edit" value="<?php echo $id_edit; ?>">
                <?php endif; ?>

                <h3>Data Pemesan</h3>
                <div class="form-group">
                    <label for="nama">Nama Lengkap *</label>
                    <input type="text" id="nama" name="nama" placeholder="Masukkan nama Anda" value="<?php echo $nama; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" placeholder="contoh@email.com" value="<?php echo $email; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="telepon">Nomor Telepon/WhatsApp *</label>
                    <input type="tel" id="telepon" name="telepon" placeholder="Contoh: 0812XXXXXXXX" value="<?php echo $telpon; ?>" required>
                </div>

                <h3>Detail Perjalanan</h3>

                <div class="form-group">
                    <label for="paket">Pilihan Paket Wisata *</label>
                    <select id="paket" name="paket" required>
                        <option value="" disabled>Pilih salah satu paket</option>
                        <option value="Bromo Sunrise & Kawah Adventure" <?php echo ($selected_paket == 'Bromo Sunrise & Kawah Adventure') ? 'selected' : ''; ?>>Bromo Sunrise & Kawah Adventure</option>
                        <option value="Bromo Midnight Tour" <?php echo ($selected_paket == 'Bromo Midnight Tour') ? 'selected' : ''; ?>>Bromo Midnight Tour</option>
                        <option value="Paket Custom" <?php echo ($selected_paket == 'Paket Custom') ? 'selected' : ''; ?>>Paket Custom</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tanggal">Rencana Tanggal Kunjungan *</label>
                    <input type="date" id="tanggal" name="tanggal" value="<?php echo $tgl_perjalanan; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="durasi_hari">Durasi Waktu Perjalanan (Hari) *</label>
                    <input type="number" id="durasi_hari" name="durasi_hari" min="1" value="<?php echo $durasi_hari > 0 ? $durasi_hari : 1; ?>" required onchange="calculatePrice()">
                </div>

                <div class="form-group">
                    <label for="jumlah_peserta">Jumlah Peserta *</label>
                    <input type="number" id="jumlah_peserta" name="jumlah_peserta" min="1" value="<?php echo $jumlah_peserta > 0 ? $jumlah_peserta : 1; ?>" required onchange="calculatePrice()">
                </div>
                
                <h3>Layanan Tambahan (Wajib pilih salah satu atau lebih) *</h3>
                <div class="form-group" style="text-align: left;">
                    <label>
                        <input type="checkbox" name="layanan_tambahan[]" value="Penginapan" id="layanan_penginapan" onchange="calculatePrice()" <?php echo in_array('Penginapan', $layanan_tambahan) ? 'checked' : ''; ?>>
                        Penginapan (Rp 1.000.000 / Hari)
                    </label><br>
                    <label>
                        <input type="checkbox" name="layanan_tambahan[]" value="Transportasi" id="layanan_transportasi" onchange="calculatePrice()" <?php echo in_array('Transportasi', $layanan_tambahan) ? 'checked' : ''; ?>>
                        Transportasi (Rp 1.200.000 / Hari)
                    </label><br>
                    <label>
                        <input type="checkbox" name="layanan_tambahan[]" value="Service/Makan" id="layanan_makan" onchange="calculatePrice()" <?php echo in_array('Service/Makan', $layanan_tambahan) ? 'checked' : ''; ?>>
                        Service/Makan (Rp 500.000 / Hari)
                    </label>
                    <small id="layanan-error" style="color: #d84315; display: none;">* Minimal pilih satu layanan tambahan.</small>
                </div>
                
                <div class="fact-box">
                    <p>💰 **Harga Paket Perjalanan:** <span id="harga_paket_display">Rp 0</span></p>
                    <p>💸 **Total Tagihan:** <span id="total_tagihan_display">Rp 0</span></p>
                    <input type="hidden" id="harga_paket_input" name="harga_paket_input">
                    <input type="hidden" id="jumlah_tagihan_input" name="jumlah_tagihan_input">
                </div>

                <button type="submit" class="btn-submit"><?php echo $is_editing ? 'Simpan Perubahan' : 'Proses Pemesanan'; ?></button>
            </form>
        </div>
        
    </section>

    <footer>
        <p>&copy; 2025 Wisata Gunung Bromo</p>
    </footer>

<script>
    // Konstanta Harga Layanan (JS)
    const PENGINAPAN = 1000000;
    const TRANSPORTASI = 1200000;
    const MAKAN = 500000;

    // Fungsi Perhitungan Otomatis
    function calculatePrice() {
        // Ambil nilai dari input
        const durasi = parseInt(document.getElementById('durasi_hari').value) || 0;
        const peserta = parseInt(document.getElementById('jumlah_peserta').value) || 0;
        
        // Cek layanan tambahan yang dipilih
        const checkPenginapan = document.getElementById('layanan_penginapan').checked;
        const checkTransportasi = document.getElementById('layanan_transportasi').checked;
        const checkMakan = document.getElementById('layanan_makan').checked;

        // Hitung Harga Paket Perjalanan
        let hargaPaketPerjalanan = 0;
        if (checkPenginapan) hargaPaketPerjalanan += PENGINAPAN;
        if (checkTransportasi) hargaPaketPerjalanan += TRANSPORTASI;
        if (checkMakan) hargaPaketPerjalanan += MAKAN;
        
        // Hitung Jumlah Tagihan
        const jumlahTagihan = durasi * peserta * hargaPaketPerjalanan;

        // Format angka ke Rupiah
        const formatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        });

        // Tampilkan hasil
        document.getElementById('harga_paket_display').innerText = formatter.format(hargaPaketPerjalanan);
        document.getElementById('total_tagihan_display').innerText = formatter.format(jumlahTagihan);

        // Validasi minimal 1 layanan tambahan
        const layananError = document.getElementById('layanan-error');
        if (hargaPaketPerjalanan === 0) {
            layananError.style.display = 'block';
        } else {
            layananError.style.display = 'none';
        }

        return hargaPaketPerjalanan > 0; // Return status validasi
    }

    // Panggil saat halaman dimuat untuk inisialisasi
    document.addEventListener('DOMContentLoaded', function() {
        calculatePrice();
        // Set tanggal minimum menjadi hari ini
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('tanggal').setAttribute('min', today);
    });

    // Validasi Formulir Sebelum Submit (JavaScript)
    function validateAndCalculate() {
        // ... (Logika validasi yang sama seperti sebelumnya) ...
        const form = document.querySelector('.pemesanan-form');
        let isValid = form.checkValidity();
        
        if (!isValid) {
             alert("Mohon lengkapi semua field yang wajib diisi (*).");
             return false;
        }

        const isLayananValid = calculatePrice();
        if (!isLayananValid) {
            alert("Mohon pilih minimal satu Layanan Tambahan (Penginapan/Transportasi/Makan).");
            return false;
        }

        return true;
    }
</script>

</body>
</html>