<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_pembayaran.php");
    exit();
}

include 'koneksi.php';

$pasien = trim($_POST['pasien'] ?? '');
$jumlah = intval($_POST['jumlah'] ?? 0);
$keterangan = trim($_POST['keterangan'] ?? '');
$tanggal = date("Y-m-d");

// Validate input
if (empty($pasien)) {
    header("Location: admin_pembayaran.php?error=pasien_required");
    exit();
}

if ($jumlah <= 0) {
    header("Location: admin_pembayaran.php?error=invalid_amount");
    exit();
}

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("INSERT INTO pembayaran (pasien, jumlah, keterangan, tanggal) VALUES (?, ?, ?, ?)");
$stmt->bind_param("siss", $pasien, $jumlah, $keterangan, $tanggal);

if ($stmt->execute()) {
    header("Location: admin_pembayaran.php?success=payment_added");
} else {
    header("Location: admin_pembayaran.php?error=insert_failed");
}

$stmt->close();
exit();
?>