<?php
session_start();
include 'koneksi.php';

if ($_SESSION['role'] != 'pasien') {
    header("Location: index.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pasien_booking.php");
    exit();
}

$pasien = $_SESSION['username'];
$dokter = trim($_POST['dokter'] ?? '');
$tanggal = trim($_POST['tanggal'] ?? '');
$hari = trim($_POST['hari'] ?? '');
$jam = trim($_POST['jam'] ?? '');
$keluhan = trim($_POST['keluhan'] ?? '');

// Validate input
if (empty($dokter) || empty($tanggal) || empty($hari) || empty($jam)) {
    header("Location: pasien_booking.php?error=missing_fields");
    exit();
}

// Check if booking already exists for this patient and doctor
$check_stmt = $conn->prepare("SELECT id FROM booking WHERE pasien = ? AND dokter = ? AND tanggal = ?");
$check_stmt->bind_param("sss", $pasien, $dokter, $tanggal);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    header("Location: pasien_booking.php?error=booking_exists");
    exit();
}
$check_stmt->close();

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("INSERT INTO booking (pasien, dokter, tanggal, hari, jam, keluhan) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $pasien, $dokter, $tanggal, $hari, $jam, $keluhan);

if ($stmt->execute()) {
    header("Location: pasien_riwayat.php?success=booking_created");
} else {
    header("Location: pasien_booking.php?error=booking_failed");
}

$stmt->close();
exit();
?>
