<?php
include 'koneksi.php'; // Memulai sesi

$error_message = "";

// Cek apakah form login disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize_input($_POST['username']);
    $password_input = $_POST['password']; // Ambil password input (belum di-sanitize atau di-hash)
    
    // Hash password input menggunakan MD5 (harus sesuai dengan hashing di database)
    $hashed_input = md5($password_input);

    // 1. Persiapkan Query
    $stmt = $koneksi->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // 2. Verifikasi Password
        // Membandingkan hash MD5 dari input pengguna dengan hash di database
        if ($hashed_input === $user['password_hash']) {
            // Login Sukses
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // Simpan role pengguna

            // Redirect ke halaman pemesanan 
            header("Location: pemesanan.php");
            exit();
        } else {
            // Password salah
            $error_message = "Password salah. Coba lagi.";
        }
    } else {
        // Username tidak ditemukan
        $error_message = "Username tidak ditemukan.";
    }
    
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Wisata Bromo</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Adaptasi Styling Login */
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            border-top: 5px solid #00897b;
            text-align: center;
        }
        .login-container h2 {
            margin-bottom: 25px;
            color: #004d40;
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }
        .login-container button {
            width: 100%;
            background-color: #00897b;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
        }
        .login-container button:hover {
            background-color: #00695c;
        }
        .error-message {
            color: #d84315;
            background-color: #fce4ec;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-content">
            <h1>Login Area</h1>
            <p>Aplikasi Pengelolaan Pemesanan Paket Wisata</p>
        </div>
    </header>

    <div class="login-container">
        <h2>Silakan Login</h2>
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>

    <footer>
        <p>&copy; 2025 Wisata Gunung Bromo</p>
    </footer>
</body>
</html>