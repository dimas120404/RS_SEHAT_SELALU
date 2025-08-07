<?php
session_start();
if ($_SESSION['role'] != 'dokter') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pasien = $_POST['pasien'];
    $dokter = $_SESSION['username'];
    $alasan = $_POST['alasan'];

    $query = "INSERT INTO rawat_inap (pasien, dokter, alasan) VALUES ('$pasien', '$dokter', '$alasan')";
    mysqli_query($conn, $query);
    echo "<script>alert('Permintaan rawat inap telah dikirim');</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Permintaan Rawat Inap | RS Sehat Selalu</title>
    <style>
        body {
            margin: 0; padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
                url('rumah sakit.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding-top: 60px;
            overflow-x: hidden;
        }
        .form-container {
            background: rgba(255,255,255,0.95);
            color: #333;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 480px;
        }
        h2 {
            text-align: center;
            color: #1e3d59;
            margin-bottom: 30px;
        }
        label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
        }
        input[type=text], textarea {
            width: 100%;
            padding: 10px 14px;
            font-size: 16px;
            border: 2px solid #1e3d59;
            border-radius: 10px;
            margin-bottom: 20px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        input[type=text]:focus, textarea:focus {
            border-color: #163049;
            outline: none;
        }
        button {
            width: 100%;
            padding: 14px;
            background-color: #1e3d59;
            color: white;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #163049;
        }
        a.back-link {
            display: block;
            margin-top: 25px;
            text-align: center;
            color: #1e3d59;
            font-weight: 600;
            text-decoration: none;
        }
        a.back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Form Permintaan Rawat Inap</h2>

    <form method="POST">
        <label for="pasien">Nama Pasien:</label>
        <input type="text" id="pasien" name="pasien" required>

        <label for="alasan">Alasan Rawat Inap:</label>
        <textarea id="alasan" name="alasan" rows="4" required></textarea>

        <button type="submit">Kirim Permintaan</button>
    </form>

    <a class="back-link" href="dokter_dashboard.php">⟵ Kembali ke Dashboard</a>
</div>

<!-- 3D Particles -->
<script src="particles.js"></script>

</body>
</html>
