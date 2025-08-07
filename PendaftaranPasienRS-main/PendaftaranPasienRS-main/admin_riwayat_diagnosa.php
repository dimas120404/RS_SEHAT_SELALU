<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';

// Handle delete operation
if (isset($_POST['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_POST['delete_id']);
    $delete_query = "DELETE FROM pemeriksaan WHERE id = '$delete_id'";
    if (mysqli_query($conn, $delete_query)) {
        echo "<script>alert('Riwayat diagnosa berhasil dihapus!');</script>";
    } else {
        echo "<script>alert('Gagal menghapus riwayat diagnosa!');</script>";
    }
}

$query = mysqli_query($conn, "SELECT * FROM pemeriksaan ORDER BY tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Diagnosa | RS Sehat Selalu</title>
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

        .diagnosis-container {
            min-height: 100vh;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .diagnosis-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin-bottom: 30px;
        }

        .diagnosis-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .diagnosis-header h2 {
            color: #2c3e50;
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .diagnosis-header p {
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

        .patient-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .patient-info i {
            color: #4facfe;
        }

        .doctor-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .doctor-info i {
            color: #ff6b6b;
        }

        .diagnosis-text {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .medicine-text {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .delete-btn {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            color: white;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .delete-btn:hover {
            background: linear-gradient(135deg, #ee5a52 0%, #ff6b6b 100%);
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
            color: white;
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

        @media (max-width: 768px) {
            .diagnosis-container {
                padding: 15px;
            }

            .diagnosis-card {
                padding: 30px 20px;
            }

            .diagnosis-header h2 {
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

            .diagnosis-text {
                max-width: 150px;
            }

            .medicine-text {
                max-width: 100px;
            }

            .delete-btn {
                padding: 6px 10px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>

<div class="diagnosis-container">
    <div class="diagnosis-card">
        <div class="diagnosis-header">
            <h2><i class="bi bi-clipboard2-data"></i> Riwayat Diagnosa</h2>
            <p>Riwayat diagnosa dan pemeriksaan pasien</p>
        </div>

        <?php if (mysqli_num_rows($query) > 0): ?>
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> No</th>
                                <th><i class="bi bi-person"></i> Pasien</th>
                                <th><i class="bi bi-person-badge"></i> Dokter</th>
                                <th><i class="bi bi-clipboard2-pulse"></i> Diagnosa</th>
                                <th><i class="bi bi-capsule"></i> Obat</th>
                                <th><i class="bi bi-calendar"></i> Tanggal</th>
                                <th><i class="bi bi-trash"></i> Hapus</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $no = 1;
                        while ($data = mysqli_fetch_assoc($query)) {
                            echo "<tr>
                                    <td><strong>{$no}</strong></td>
                                    <td>
                                        <div class='patient-info'>
                                            <i class='bi bi-person-circle'></i>
                                            ".htmlspecialchars($data['pasien'])."
                                        </div>
                                    </td>
                                    <td>
                                        <div class='doctor-info'>
                                            <i class='bi bi-person-badge'></i>
                                            ".htmlspecialchars($data['dokter'])."
                                        </div>
                                    </td>
                                    <td>
                                        <div class='diagnosis-text' title='".htmlspecialchars($data['diagnosa'])."'>
                                            ".htmlspecialchars($data['diagnosa'])."
                                        </div>
                                    </td>
                                    <td>
                                        <div class='medicine-text' title='".htmlspecialchars($data['obat'])."'>
                                            ".htmlspecialchars($data['obat'])."
                                        </div>
                                    </td>
                                    <td><span class='badge bg-success'>".htmlspecialchars($data['tanggal'])."</span></td>
                                    <td>
                                        <form method='POST' onsubmit='return confirm(\"Apakah Anda yakin ingin menghapus riwayat ini?\");'>
                                            <input type='hidden' name='delete_id' value='".htmlspecialchars($data['id'])."'>
                                            <button type='submit' class='delete-btn'>
                                                <i class='bi bi-trash'></i>
                                            </button>
                                        </form>
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
                <h4>Belum Ada Riwayat Diagnosa</h4>
                <p>Tidak ada data riwayat diagnosa saat ini.</p>
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
