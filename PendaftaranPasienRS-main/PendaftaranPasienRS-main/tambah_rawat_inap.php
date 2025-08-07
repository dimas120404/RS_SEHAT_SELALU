<?php
include 'koneksi.php';
$pasien = $_POST['pasien'];
$kamar = $_POST['kamar'];
$tanggal = $_POST['tanggal_masuk'];

mysqli_query($conn, "INSERT INTO rawat_inap (pasien, kamar, tanggal_masuk) VALUES ('$pasien', '$kamar', '$tanggal')");
header("Location: admin_rawat_inap.php");
?>