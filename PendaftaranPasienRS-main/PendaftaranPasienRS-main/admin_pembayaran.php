<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Pasien | RS Sehat Selalu</title>
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

        .payment-container {
            min-height: 100vh;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .payment-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin-bottom: 30px;
        }

        .payment-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .payment-header h2 {
            color: #2c3e50;
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .payment-header p {
            color: #7f8c8d;
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        .form-section {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .form-section h3 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-floating .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-floating .form-control:focus {
            border-color: #4facfe;
            box-shadow: 0 0 0 0.2rem rgba(79, 172, 254, 0.25);
        }

        .form-floating label {
            padding: 15px;
            color: #6c757d;
        }

        .btn-submit {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
            color: white;
        }

        .payment-info {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
        }

        .payment-info h3 {
            color: white;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .qris-section {
            display: flex;
            gap: 30px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .qris-image {
            flex: 0 0 200px;
        }

        .qris-image img {
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .bank-info {
            flex: 1;
            min-width: 300px;
        }

        .bank-card {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            backdrop-filter: blur(10px);
        }

        .bank-card:last-child {
            margin-bottom: 0;
        }

        .bank-card strong {
            display: block;
            margin-bottom: 5px;
            font-size: 1.1rem;
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

        .amount-badge {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
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

        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            border: none;
            border-radius: 8px;
            padding: 8px 15px;
            font-size: 0.9rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
            color: white;
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
        }

        .alert i {
            margin-right: 8px;
        }

        .btn-close {
            filter: invert(1);
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
            .payment-container {
                padding: 15px;
            }

            .payment-card {
                padding: 30px 20px;
            }

            .payment-header h2 {
                font-size: 2rem;
            }

            .qris-section {
                flex-direction: column;
            }

            .qris-image {
                flex: none;
                text-align: center;
            }

            .table-responsive {
                border-radius: 15px;
            }

            .table thead th,
            .table tbody td {
                padding: 10px 8px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<div class="payment-container">
    <div class="payment-card">
        <div class="payment-header">
            <h2><i class="bi bi-credit-card"></i> Pencatatan Pembayaran</h2>
            <p>Kelola pembayaran dan transaksi pasien</p>
        </div>

        <?php
        // Display success/error messages
        if (isset($_GET['success']) && $_GET['success'] == 'payment_deleted') {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> Pembayaran berhasil dihapus!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>';
        }
        
        if (isset($_GET['error'])) {
            $error_message = '';
            switch ($_GET['error']) {
                case 'invalid_id':
                    $error_message = 'ID pembayaran tidak valid!';
                    break;
                case 'payment_not_found':
                    $error_message = 'Pembayaran tidak ditemukan!';
                    break;
                case 'delete_failed':
                    $error_message = 'Gagal menghapus pembayaran!';
                    break;
                default:
                    $error_message = 'Terjadi kesalahan!';
            }
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> ' . $error_message . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>';
        }
        ?>

        <!-- Form Pembayaran -->
        <div class="form-section">
            <h3><i class="bi bi-plus-circle"></i> Tambah Pembayaran Baru</h3>
            <form action="tambah_pembayaran.php" method="POST">
                <div class="form-floating">
                    <input type="text" class="form-control" name="pasien" id="pasien" placeholder="Nama Pasien" required>
                    <label for="pasien"><i class="bi bi-person"></i> Nama Pasien</label>
                </div>

                <div class="form-floating">
                    <input type="number" class="form-control" name="jumlah" id="jumlah" placeholder="Jumlah" required>
                    <label for="jumlah"><i class="bi bi-currency-dollar"></i> Jumlah (Rp)</label>
                </div>

                <div class="form-floating">
                    <input type="text" class="form-control" name="keterangan" id="keterangan" placeholder="Keterangan">
                    <label for="keterangan"><i class="bi bi-chat-text"></i> Keterangan</label>
                </div>

                <button type="submit" class="btn btn-submit">
                    <i class="bi bi-check-circle"></i> Catat Pembayaran
                </button>
            </form>
        </div>

        <!-- Informasi Pembayaran -->
        <div class="payment-info">
            <h3><i class="bi bi-qr-code"></i> Informasi Pembayaran</h3>
            <div class="qris-section">
                <div class="qris-image">
                    <img src="qris pembayaran.png" alt="QRIS Pembayaran">
                </div>
                <div class="bank-info">
                    <div class="bank-card">
                        <strong><i class="bi bi-bank"></i> BCA</strong>
                        6341893478 a/n Rumah Sakit Sehat Selalu
                    </div>
                    <div class="bank-card">
                        <strong><i class="bi bi-bank"></i> BNI</strong>
                        3287435793 a/n Rumah Sakit Sehat Selalu
                    </div>
                    <div class="bank-card">
                        <strong><i class="bi bi-bank"></i> BRI</strong>
                        9764746835 a/n Rumah Sakit Sehat Selalu
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Pembayaran -->
        <div class="form-section">
            <h3><i class="bi bi-clock-history"></i> Riwayat Pembayaran</h3>
            <?php
            $data = mysqli_query($conn, "SELECT * FROM pembayaran ORDER BY tanggal DESC");
            if (mysqli_num_rows($data) > 0):
            ?>
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><i class="bi bi-person"></i> Pasien</th>
                                    <th><i class="bi bi-currency-dollar"></i> Jumlah</th>
                                    <th><i class="bi bi-chat-text"></i> Keterangan</th>
                                    <th><i class="bi bi-calendar"></i> Tanggal</th>
                                    <th><i class="bi bi-trash"></i> Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            while ($r = mysqli_fetch_assoc($data)) {
                                echo "<tr>
                                        <td><i class='bi bi-person-circle'></i> ".htmlspecialchars($r['pasien'])."</td>
                                        <td><span class='amount-badge'>Rp " . number_format($r['jumlah'], 0, ',', '.') . "</span></td>
                                        <td>".htmlspecialchars($r['keterangan'])."</td>
                                        <td><span class='badge bg-primary'>".htmlspecialchars($r['tanggal'])."</span></td>
                                        <td>
                                            <button class='btn btn-danger btn-sm' onclick='deletePayment(".$r['id'].")'>
                                                <i class='bi bi-trash'></i> Hapus
                                            </button>
                                        </td>
                                      </tr>";
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h4>Belum Ada Riwayat Pembayaran</h4>
                    <p>Tidak ada data pembayaran saat ini.</p>
                </div>
            <?php endif; ?>
        </div>

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

<script>
function deletePayment(id) {
    if (confirm('Apakah Anda yakin ingin menghapus pembayaran ini? Tindakan ini tidak dapat dibatalkan.')) {
        window.location.href = 'hapus_pembayaran.php?id=' + id;
    }
}
</script>

</body>
</html>
