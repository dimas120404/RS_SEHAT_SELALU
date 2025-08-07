<?php
session_start();
if ($_SESSION['role'] != 'dokter') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pasien = trim($_POST['pasien'] ?? '');
    $diagnosa = trim($_POST['diagnosa'] ?? '');
    $obat = trim($_POST['obat'] ?? '');
    $dokter = $_SESSION['username'];
    $tgl = date("Y-m-d");
    
    // Validate input
    if (empty($pasien) || empty($diagnosa) || empty($obat)) {
        $message = "Semua field harus diisi.";
    } else {
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO pemeriksaan (pasien, dokter, diagnosa, obat, tanggal) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $pasien, $dokter, $diagnosa, $obat, $tgl);
        
        if ($stmt->execute()) {
            $message = "Berhasil dicatat.";
        } else {
            $message = "Gagal mencatat pemeriksaan.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Catat Pemeriksaan | RS Sehat Selalu</title>
    <style>
        body {
            margin: 0; 
            padding: 0;
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
            padding-top: 50px;
            overflow-x: hidden;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #1e3d59;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }
        input[type=text] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 20px;
            border: 2px solid #1e3d59;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }
        input[type=text]:focus {
            border-color: #163049;
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #1e3d59;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #163049;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #1e3d59;
            font-weight: 600;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
            font-weight: 700;
            color: green;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Catat Pemeriksaan</h2>

        <?php if (!empty($message)) : ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="pasien">Nama Pasien:</label>
            <input type="text" id="pasien" name="pasien" required>

            <label for="diagnosa">Diagnosa:</label>
            <input type="text" id="diagnosa" name="diagnosa" required>

            <label for="obat">Obat:</label>
            <input type="text" id="obat" name="obat" required>

            <button type="submit">Simpan</button>
        </form>

        <a class="back-link" href="dokter_dashboard.php">⟵ Kembali ke Dashboard</a>
    </div>
    
    <!-- 3D Particles -->
    <script src="particles.js"></script>
</body>
</html>
