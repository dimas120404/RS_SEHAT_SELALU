<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';

// Proses setujui/tolak/hapus permintaan rawat inap
if (isset($_GET['id']) && isset($_GET['aksi'])) {
    $id = (int)$_GET['id'];
    $aksi = $_GET['aksi'];

    if ($aksi === 'setujui') {
        $status = 'Disetujui';
        $stmt = $conn->prepare("UPDATE rawat_inap SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
    } elseif ($aksi === 'tolak') {
        $status = 'Ditolak';
        $stmt = $conn->prepare("UPDATE rawat_inap SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
    } elseif ($aksi === 'hapus') {
        $stmt = $conn->prepare("DELETE FROM rawat_inap WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    header("Location: admin_rawat_inap.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM rawat_inap ORDER BY tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rawat Inap | RS Sehat Selalu</title>
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

        .inpatient-container {
            min-height: 100vh;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .inpatient-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin-bottom: 30px;
        }

        .inpatient-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .inpatient-header h2 {
            color: #2c3e50;
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .inpatient-header p {
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

        .reason-text {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-waiting {
            background: linear-gradient(135deg, #ffa726 0%, #ff7043 100%);
            color: white;
        }

        .status-approved {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .status-rejected {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
        }

        .btn-approve {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            border: none;
            border-radius: 8px;
            padding: 8px 15px;
            font-size: 0.9rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            text-decoration: none;
            margin-right: 5px;
        }

        .btn-approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 233, 123, 0.3);
            color: white;
            text-decoration: none;
        }

        .btn-reject {
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

        .btn-reject:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
            color: white;
            text-decoration: none;
        }

        .btn-delete {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            border: none;
            border-radius: 8px;
            padding: 8px 15px;
            font-size: 0.9rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            text-decoration: none;
            margin-left: 5px;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
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

        @media (max-width: 768px) {
            .inpatient-container {
                padding: 15px;
            }

            .inpatient-card {
                padding: 30px 20px;
            }

            .inpatient-header h2 {
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

            .reason-text {
                max-width: 150px;
            }
        }
    </style>
</head>
<body>

<div class="inpatient-container">
    <div class="inpatient-card">
        <div class="inpatient-header">
            <h2><i class="bi bi-hospital"></i> Daftar Permintaan Rawat Inap</h2>
            <p>Kelola permintaan rawat inap pasien</p>
        </div>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> No</th>
                                <th><i class="bi bi-person"></i> Pasien</th>
                                <th><i class="bi bi-person-badge"></i> Dokter</th>
                                <th><i class="bi bi-chat-text"></i> Alasan</th>
                                <th><i class="bi bi-info-circle"></i> Status</th>
                                <th><i class="bi bi-calendar"></i> Tanggal</th>
                                <th><i class="bi bi-gear"></i> Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
                            $statusClass = '';
                            switch ($row['status']) {
                                case 'Menunggu':
                                    $statusClass = 'status-waiting';
                                    break;
                                case 'Disetujui':
                                    $statusClass = 'status-approved';
                                    break;
                                case 'Ditolak':
                                    $statusClass = 'status-rejected';
                                    break;
                                }

                                     echo "<tr>
                                    <td><strong>{$no}</strong></td>
                                    <td>
                                        <div class='patient-info'>
                                            <i class='bi bi-person-circle'></i>
                                            ".htmlspecialchars($row['pasien'])."
                                        </div>
                                    </td>
                                    <td>
                                        <div class='doctor-info'>
                                            <i class='bi bi-person-badge'></i>
                                            ".htmlspecialchars($row['dokter'])."
                                        </div>
                                    </td>
                                    <td>
                                        <div class='reason-text' title='".htmlspecialchars($row['alasan'])."'>
                                            ".htmlspecialchars($row['alasan'])."
                                        </div>
                                    </td>
                                    <td><span class='status-badge {$statusClass}'>".htmlspecialchars($row['status'])."</span></td>
                                    <td><span class='badge bg-secondary'>".htmlspecialchars($row['tanggal'])."</span></td>
                                    <td>";
                            
                            if ($row['status'] === 'Menunggu') {
                                echo "<a href='admin_rawat_inap.php?id={$row['id']}&aksi=setujui' class='btn-approve'>
                                        <i class='bi bi-check-circle'></i> Setujui
                                      </a>
                                      <a href='admin_rawat_inap.php?id={$row['id']}&aksi=tolak' 
                                         class='btn-reject'
                                         onclick=\"return confirm('Yakin ingin menolak permintaan ini?')\">
                                        <i class='bi bi-x-circle'></i> Tolak
                                      </a>";
                            }
                            
                            // Tombol hapus tersedia untuk semua status
                            echo "<a href='admin_rawat_inap.php?id={$row['id']}&aksi=hapus' 
                                   class='btn-delete'
                                   onclick=\"return confirm('Yakin ingin menghapus permintaan ini? Data yang dihapus tidak dapat dikembalikan.')\">
                                  <i class='bi bi-trash'></i> Hapus
                                </a>";
                            
                            echo "</td></tr>";
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
                <h4>Belum Ada Permintaan Rawat Inap</h4>
                <p>Tidak ada data permintaan rawat inap saat ini.</p>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- 3D Particles -->
<script src="particles.js"></script>

</body>
</html>
