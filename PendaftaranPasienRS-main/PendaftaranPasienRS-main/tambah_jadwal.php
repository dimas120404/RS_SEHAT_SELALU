<?php
include 'koneksi.php';

$nama = $_POST['nama_dokter'];
$hari = $_POST['hari'];
$jam  = $_POST['jam'];

mysqli_query($conn, "INSERT INTO jadwal_dokter (nama_dokter, hari, jam) VALUES ('$nama', '$hari', '$jam')");
header("Location: dokter_jadwal.php");
?>