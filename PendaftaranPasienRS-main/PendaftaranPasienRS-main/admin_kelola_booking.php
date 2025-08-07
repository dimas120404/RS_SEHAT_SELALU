<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';

// Hapus booking dengan prepared statement
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $stmt = $conn->prepare("DELETE FROM booking WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: admin_kelola_booking.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Booking Pasien | RS Sehat Selalu</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        .booking-container {
            min-height: 100vh;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .booking-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin-bottom: 30px;
        }

        .booking-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .booking-header h2 {
            color: #2c3e50;
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .booking-header p {
            color: #7f8c8d;
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        .table-container {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border: none;
            padding: 15px;
            font-weight: 600;
            text-align: center;
        }

        .table tbody td {
            padding: 15px;
            border: none;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }

        .table tbody tr:nth-child(even) {
            background-color: rgba(79, 172, 254, 0.05);
        }

        .table tbody tr:hover {
            background-color: rgba(79, 172, 254, 0.1);
            transition: all 0.3s ease;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .btn-delete {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            border: none;
            border-radius: 8px;
            padding: 8px 15px;
            font-size: 0.9rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
            color: white;
            text-decoration: none;
        }

        .back-button {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
            color: white;
            text-decoration: none;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #bdc3c7;
        }

        .empty-state h4 {
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .patient-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .patient-info i {
            color: #4facfe;
        }

        .complaint-text {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            .booking-container {
                padding: 15px;
            }

            .booking-card {
                padding: 30px 20px;
            }

            .booking-header h2 {
                font-size: 2rem;
            }

            .table-responsive {
                border-radius: 15px;
            }

            .table thead th,
            .table tbody td {
                padding: 10px 8px;
                font-size: 0.9rem;
            }

            .complaint-text {
                max-width: 150px;
            }
        }
    </style>
</head>
<body>

<div class="booking-container">
    <div class="booking-card">
        <div class="booking-header">
            <h2><i class="bi bi-calendar-check"></i> Kelola Booking Pasien</h2>
            <p>Kelola dan atur jadwal booking pasien</p>
        </div>

        <?php
        $result = mysqli_query($conn, "SELECT * FROM booking ORDER BY id DESC");
        if (mysqli_num_rows($result) > 0):
        ?>
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> No</th>
                                <th><i class="bi bi-person"></i> Nama Pasien</th>
                                <th><i class="bi bi-person-badge"></i> Dokter</th>
                                <th><i class="bi bi-calendar"></i> Tanggal</th>
                                <th><i class="bi bi-calendar-week"></i> Hari</th>
                                <th><i class="bi bi-clock"></i> Jam</th>
                                <th><i class="bi bi-chat-text"></i> Keluhan</th>
                                <th><i class="bi bi-gear"></i> Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>
                                    <td><strong>{$no}</strong></td>
                                    <td>
                                        <div class='patient-info'>
                                            <i class='bi bi-person-circle'></i>
                                            ".htmlspecialchars($row['pasien'])."
                                        </div>
                                    </td>
                                    <td><i class='bi bi-person-badge'></i> ".htmlspecialchars($row['dokter'])."</td>
                                    <td>".htmlspecialchars($row['tanggal'])."</td>
                                    <td>".htmlspecialchars($row['hari'])."</td>
                                    <td><span class='badge bg-primary'>".htmlspecialchars($row['jam'])."</span></td>
                                    <td>
                                        <div class='complaint-text' title='".htmlspecialchars($row['keluhan'])."'>
                                            ".htmlspecialchars($row['keluhan'])."
                                        </div>
                                    </td>
                                    <td>
                                        <a href='admin_kelola_booking.php?hapus={$row['id']}' 
                                           class='btn-delete'
                                           onclick=\"return confirm('Yakin ingin menghapus booking ini?')\">
                                            <i class='bi bi-trash'></i> Hapus
                                        </a>
                                    </td>
                                  </tr>";
                            $no++;
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h4>Belum Ada Booking</h4>
                <p>Tidak ada data booking pasien saat ini.</p>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="admin_dashboard.php" class="back-button">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- 3D Particles -->
<script src="particles.js"></script>

</body>
</html>
