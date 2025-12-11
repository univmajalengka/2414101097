<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['cart'])) { header('Location: index.php'); exit(); }
include 'includes/db.php';
 $nama_pemesan = $_POST['nama_pemesan']; $catatan = $_POST['catatan'] ?? null; $id_user = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null; $total_harga = 0;
foreach ($_SESSION['cart'] as $item) $total_harga += $item['price'] * $item['quantity'];
 $conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO pemesanan (nama_pemesan, id_user, total_harga, catatan) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sids", $nama_pemesan, $id_user, $total_harga, $catatan);
    $stmt->execute();
    $id_pemesanan = $conn->insert_id;
    foreach ($_SESSION['cart'] as $id_menu => $item) {
        $stmt_detail = $conn->prepare("INSERT INTO detail_pemesanan (id_pemesanan, id_menu, jumlah, harga_satuan) VALUES (?, ?, ?, ?)");
        $stmt_detail->bind_param("iiid", $id_pemesanan, $id_menu, $item['quantity'], $item['price']);
        $stmt_detail->execute();
    }
    $conn->commit();
    unset($_SESSION['cart']);
    header('Location: sukses.php?id=' . $id_pemesanan);
} catch (Exception $e) { $conn->rollback(); die("Error: " . $e->getMessage()); }