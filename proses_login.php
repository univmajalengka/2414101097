<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['identifier'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            if ($user['role'] === 'admin') {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user'] = ['id' => $user['id'], 'nama' => $user['nama_lengkap'], 'email' => $user['email']];
                header('Location: admin/index.php');
            } else {
                $_SESSION['user'] = ['id' => $user['id'], 'nama_lengkap' => $user['nama_lengkap'], 'email' => $user['email']];
                header('Location: profil.php');
            }
            exit();
        }
    }
    $_SESSION['login_error'] = 'Email atau Password salah.';
    header('Location: login.php');
} else header('Location: login.php');