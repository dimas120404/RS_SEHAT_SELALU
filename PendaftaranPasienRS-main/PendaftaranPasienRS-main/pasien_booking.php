<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pasien') {
    header("Location: index.html");
    exit();
}
include 'koneksi.php';

// Ambil data dokter dan jadwal
$dokter_result = mysqli_query($conn, "SELECT DISTINCT dokter FROM dokter_jadwal");
$dokter_jadwal_result = mysqli_query($conn, "SELECT * FROM dokter_jadwal");

// Simpan jadwal dokter dalam array
$dokter_jadwal = [];
while ($row = mysqli_fetch_assoc($dokter_jadwal_result)) {
    $dokter = $row['dokter'];
    $dokter_jadwal[$dokter][] = [
        'hari' => $row['hari'],
        'jam' => $row['jam']
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Janji Temu | RS Sehat Selalu</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
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
            max-width: 500px;
            width: 100%;
        }

        .booking-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .booking-header h2 {
            color: #2c3e50;
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 10px;
        }

        .booking-header p {
            color: #7f8c8d;
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-floating .form-control,
        .form-floating .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-floating .form-control:focus,
        .form-floating .form-select:focus {
            border-color: #4facfe;
            box-shadow: 0 0 0 0.2rem rgba(79, 172, 254, 0.25);
        }

        .form-floating label {
            padding: 15px;
            color: #6c757d;
        }

        .form-floating textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .schedule-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
        }

        .schedule-info h6 {
            margin-bottom: 10px;
            font-weight: 600;
        }

        .schedule-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .schedule-list li {
            padding: 5px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .schedule-list li:last-child {
            border-bottom: none;
        }

        .btn-booking {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }

        .btn-booking:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
            color: white;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #4facfe;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            color: #00f2fe;
            text-decoration: underline;
        }

        .back-link i {
            margin-right: 8px;
        }

        @media (max-width: 576px) {
            .booking-container {
                padding: 15px;
            }

            .booking-card {
                padding: 30px 20px;
            }

            .booking-header h2 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>

<div class="booking-container">
    <div class="booking-card">
        <div class="booking-header">
            <h2><i class="bi bi-calendar-check"></i> Booking Janji Temu</h2>
            <p>Pilih dokter dan jadwal yang sesuai</p>
        </div>

        <form action="proses_booking.php" method="POST" onsubmit="return validasiBooking();">
            <div class="form-floating">
                <select class="form-select" name="dokter" id="dokter" onchange="tampilkanJadwal()" required>
                    <option value="">-- Pilih Dokter --</option>
                    <?php while ($dok = mysqli_fetch_assoc($dokter_result)): ?>
                        <option value="<?= htmlspecialchars($dok['dokter']) ?>"><?= htmlspecialchars($dok['dokter']) ?></option>
                    <?php endwhile; ?>
                </select>
                <label for="dokter"><i class="bi bi-person-badge"></i> Dokter</label>
            </div>

            <div id="jadwal_dokter" class="schedule-info">
                <h6><i class="bi bi-clock"></i> Jadwal Dokter:</h6>
                <ul id="list_jadwal" class="schedule-list"></ul>
            </div>

            <div class="form-floating">
                <input type="date" class="form-control" name="tanggal" id="tanggal" onchange="setHariDariTanggal()" required>
                <label for="tanggal"><i class="bi bi-calendar"></i> Tanggal</label>
            </div>

            <div class="form-floating">
                <input type="text" class="form-control" name="hari" id="hari" readonly required>
                <label for="hari"><i class="bi bi-calendar-week"></i> Hari</label>
            </div>

            <div class="form-floating">
                <input type="text" class="form-control" name="jam" id="jam" placeholder="Contoh: 09:00 - 10:00" required>
                <label for="jam"><i class="bi bi-clock"></i> Jam</label>
            </div>

            <div class="form-floating">
                <textarea class="form-control" name="keluhan" id="keluhan" rows="4" placeholder="Tuliskan keluhan Anda di sini..." required></textarea>
                <label for="keluhan"><i class="bi bi-chat-text"></i> Keluhan</label>
            </div>

            <button type="submit" class="btn btn-booking">
                <i class="bi bi-check-circle"></i> Booking Sekarang
            </button>
        </form>

        <div class="back-link">
            <a href="pasien_dashboard.php">
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
    const dokterJadwal = <?= json_encode($dokter_jadwal) ?>;

    function tampilkanJadwal() {
        const dokter = document.getElementById('dokter').value;
        const list = document.getElementById('list_jadwal');
        const jadwalBox = document.getElementById('jadwal_dokter');
        list.innerHTML = '';
        if (dokter && dokterJadwal[dokter]) {
            jadwalBox.style.display = 'block';
            dokterJadwal[dokter].forEach(j => {
                const li = document.createElement('li');
                li.innerHTML = `<i class="bi bi-clock"></i> ${j.hari} - ${j.jam}`;
                list.appendChild(li);
            });
        } else {
            jadwalBox.style.display = 'none';
        }
    }

    function setHariDariTanggal() {
        const tanggalInput = document.getElementById('tanggal').value;
        const hariInput = document.getElementById('hari');

        if (tanggalInput) {
            const date = new Date(tanggalInput);
            const hariList = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const hari = hariList[date.getDay()];
            hariInput.value = hari;
        } else {
            hariInput.value = '';
        }
    }

    function validasiBooking() {
        const dokter = document.getElementById('dokter').value;
        const hari = document.getElementById('hari').value.trim().toLowerCase();
        const jam = document.getElementById('jam').value.trim();
        const tanggal = document.getElementById('tanggal').value;

        if (!tanggal) {
            alert("Tanggal harus diisi!");
            return false;
        }

        const cocok = dokterJadwal[dokter]?.some(j => j.hari.toLowerCase() === hari && j.jam === jam);
        if (!cocok) {
            alert("Jadwal tidak sesuai dengan jadwal dokter!");
            return false;
        }
        return true;
    }
</script>

</body>
</html>
