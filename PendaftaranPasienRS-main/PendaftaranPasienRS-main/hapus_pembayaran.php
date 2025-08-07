<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_pembayaran.php?error=invalid_id");
    exit();
}

include 'koneksi.php';

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Verify the payment exists
$check_query = "SELECT * FROM pembayaran WHERE id = '$id'";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    header("Location: admin_pembayaran.php?error=payment_not_found");
    exit();
}

// Delete the payment
$delete_query = "DELETE FROM pembayaran WHERE id = '$id'";
$delete_result = mysqli_query($conn, $delete_query);

if ($delete_result) {
    header("Location: admin_pembayaran.php?success=payment_deleted");
} else {
    header("Location: admin_pembayaran.php?error=delete_failed");
}

exit();
?> 