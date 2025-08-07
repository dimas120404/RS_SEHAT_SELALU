<?php
include 'koneksi.php';
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$client_ip = get_client_ip();

// Get user info
$stmt = $conn->prepare("SELECT username, two_factor_enabled FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: index.html");
    exit();
}

$message = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'enable') {
        // Generate new secret
        $secret = $security->generate_2fa_secret();
        
        // Setup 2FA
        if ($security->setup_2fa($user_id, $secret)) {
            $security->log_security_event($user_id, 'TWO_FACTOR_ENABLED', "2FA enabled for user", $client_ip);
            $message = "Two-Factor Authentication telah diaktifkan! Simpan secret key ini dengan aman: <strong>$secret</strong>";
            $success = true;
            $user['two_factor_enabled'] = 1;
        } else {
            $message = "Gagal mengaktifkan Two-Factor Authentication.";
        }
    } elseif ($action === 'disable') {
        $code = $_POST['code'] ?? '';
        
        if (empty($code)) {
            $message = "Kode 2FA diperlukan untuk menonaktifkan.";
        } else {
            // Get current secret
            $stmt = $conn->prepare("SELECT two_factor_secret FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();
            
            if ($security->verify_2fa_code($user_data['two_factor_secret'], $code)) {
                if ($security->disable_2fa($user_id)) {
                    $security->log_security_event($user_id, 'TWO_FACTOR_DISABLED', "2FA disabled for user", $client_ip);
                    $message = "Two-Factor Authentication telah dinonaktifkan.";
                    $success = true;
                    $user['two_factor_enabled'] = 0;
                } else {
                    $message = "Gagal menonaktifkan Two-Factor Authentication.";
                }
            } else {
                $message = "Kode 2FA tidak valid.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Setup Two-Factor Authentication</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .status {
            padding: 10px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .danger {
            background-color: #dc3545;
        }
        .danger:hover {
            background-color: #c82333;
        }
        .info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #007bff;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Setup Two-Factor Authentication</h1>
            <p>User: <strong><?= htmlspecialchars($user['username']) ?></strong></p>
        </div>

        <?php if ($message): ?>
            <div class="status <?= $success ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="info">
            <h3>Tentang Two-Factor Authentication (2FA)</h3>
            <p>Two-Factor Authentication menambahkan lapisan keamanan ekstra pada akun Anda. Setelah diaktifkan, Anda akan diminta memasukkan kode 6 digit yang berubah setiap 30 detik selain username dan password.</p>
            <p><strong>Status saat ini:</strong> <?= $user['two_factor_enabled'] ? '<span style="color: green;">Aktif</span>' : '<span style="color: red;">Nonaktif</span>' ?></p>
        </div>

        <?php if (!$user['two_factor_enabled']): ?>
            <div class="form-group">
                <h3>Aktifkan Two-Factor Authentication</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="enable">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <p>Klik tombol di bawah untuk mengaktifkan 2FA. Anda akan mendapatkan secret key yang perlu disimpan di aplikasi authenticator (seperti Google Authenticator, Authy, dll.).</p>
                    <button type="submit">Aktifkan 2FA</button>
                </form>
            </div>
        <?php else: ?>
            <div class="form-group">
                <h3>Nonaktifkan Two-Factor Authentication</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="disable">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <div class="form-group">
                        <label for="code">Masukkan kode 2FA untuk menonaktifkan:</label>
                        <input type="text" id="code" name="code" maxlength="6" required>
                    </div>
                    <button type="submit" class="danger">Nonaktifkan 2FA</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="back-link">
            <a href="<?= $_SESSION['role'] == 'admin' ? 'admin_dashboard.php' : 'pasien_dashboard.php' ?>">← Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>
