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

if (!$user || !$user['two_factor_enabled']) {
    header("Location: setup_2fa.php");
    exit();
}

$message = "";
$success = false;
$backup_codes = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $message = "Invalid CSRF token.";
    } else {
        switch ($action) {
            case 'generate':
                try {
                    // Delete existing backup codes
                    $delete_stmt = $conn->prepare("DELETE FROM backup_codes WHERE user_id = ?");
                    $delete_stmt->bind_param("i", $user_id);
                    $delete_stmt->execute();
                    
                    // Generate new backup codes
                    $backup_codes = [];
                    for ($i = 0; $i < 10; $i++) {
                        $code = strtoupper(bin2hex(random_bytes(4))); // 8 character code
                        $backup_codes[] = $code;
                        
                        // Insert into database
                        $insert_stmt = $conn->prepare("INSERT INTO backup_codes (user_id, code) VALUES (?, ?)");
                        $insert_stmt->bind_param("is", $user_id, $code);
                        $insert_stmt->execute();
                    }
                    
                    $security->log_security_event($user_id, 'BACKUP_CODES_GENERATED', "User generated new backup codes", $client_ip);
                    $message = "Backup codes berhasil dibuat! Simpan codes ini di tempat yang aman.";
                    $success = true;
                    
                } catch (Exception $e) {
                    error_log("Backup codes generation error: " . $e->getMessage());
                    $message = "Gagal membuat backup codes. Silakan coba lagi.";
                }
                break;
                
            case 'download':
                // Get existing backup codes
                $stmt = $conn->prepare("SELECT code FROM backup_codes WHERE user_id = ? AND used = 0");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $codes = [];
                while ($row = $result->fetch_assoc()) {
                    $codes[] = $row['code'];
                }
                
                if (!empty($codes)) {
                    // Create downloadable text file
                    header('Content-Type: text/plain');
                    header('Content-Disposition: attachment; filename="backup_codes_' . $user['username'] . '.txt"');
                    
                    echo "RS Sehat Selalu - Backup Codes\n";
                    echo "User: " . $user['username'] . "\n";
                    echo "Generated: " . date('Y-m-d H:i:s') . "\n";
                    echo "========================================\n\n";
                    echo "IMPORTANT: Store these codes securely!\n";
                    echo "Each code can only be used once.\n\n";
                    echo "Backup Codes:\n";
                    foreach ($codes as $code) {
                        echo "- " . $code . "\n";
                    }
                    echo "\n========================================\n";
                    echo "Keep these codes in a secure location.\n";
                    echo "They can be used to access your account if you lose your 2FA device.\n";
                    
                    $security->log_security_event($user_id, 'BACKUP_CODES_DOWNLOADED', "User downloaded backup codes", $client_ip);
                    exit();
                } else {
                    $message = "Tidak ada backup codes. Silakan generate terlebih dahulu.";
                }
                break;
        }
    }
}

// Get existing backup codes
$stmt = $conn->prepare("SELECT code, used, used_at FROM backup_codes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$existing_codes = [];
while ($row = $result->fetch_assoc()) {
    $existing_codes[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Backup Codes - RS Sehat Selalu</title>
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
        .header h1 {
            color: #333;
            margin-bottom: 10px;
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
        .warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .backup-codes {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .code-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        .code-item {
            background: white;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            font-family: monospace;
            font-size: 16px;
            font-weight: bold;
            border: 2px solid #28a745;
        }
        .code-item.used {
            background: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
            text-decoration: line-through;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
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
        .security-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .security-warning h3 {
            color: #856404;
            margin-top: 0;
        }
        .actions {
            text-align: center;
            margin: 20px 0;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #007bff;
        }
        .stat-label {
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔑 Backup Codes</h1>
            <p>User: <strong><?= htmlspecialchars($user['username']) ?></strong></p>
        </div>

        <?php if ($message): ?>
            <div class="status <?= $success ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="info">
            <h3>Tentang Backup Codes</h3>
            <p>Backup codes adalah kode sekali pakai yang dapat digunakan untuk login jika Anda kehilangan akses ke aplikasi 2FA. Setiap kode hanya dapat digunakan sekali.</p>
            <ul>
                <li>Simpan codes ini di tempat yang aman</li>
                <li>Jangan bagikan codes dengan orang lain</li>
                <li>Generate ulang codes jika diperlukan</li>
                <li>Gunakan codes hanya jika kehilangan akses 2FA</li>
            </ul>
        </div>

        <?php if (!empty($existing_codes)): ?>
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number"><?= count($existing_codes) ?></div>
                    <div class="stat-label">Total Codes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= count(array_filter($existing_codes, function($c) { return !$c['used']; })) ?></div>
                    <div class="stat-label">Available</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= count(array_filter($existing_codes, function($c) { return $c['used']; })) ?></div>
                    <div class="stat-label">Used</div>
                </div>
            </div>

            <div class="backup-codes">
                <h3>Your Backup Codes</h3>
                <div class="code-grid">
                    <?php foreach ($existing_codes as $code_data): ?>
                        <div class="code-item <?= $code_data['used'] ? 'used' : '' ?>">
                            <?= htmlspecialchars($code_data['code']) ?>
                            <?php if ($code_data['used']): ?>
                                <br><small>Used: <?= $code_data['used_at'] ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="actions">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="download">
                    <button type="submit" class="btn btn-secondary">📥 Download Codes</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if (!empty($backup_codes)): ?>
            <div class="security-warning">
                <h3>⚠️ Codes Baru Telah Dibuat</h3>
                <p>Backup codes berikut telah dibuat untuk Anda. Simpan di tempat yang aman dan jangan bagikan dengan orang lain!</p>
            </div>

            <div class="backup-codes">
                <h3>Your New Backup Codes</h3>
                <div class="code-grid">
                    <?php foreach ($backup_codes as $code): ?>
                        <div class="code-item">
                            <?= htmlspecialchars($code) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="actions">
            <form method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <input type="hidden" name="action" value="generate">
                <button type="submit" class="btn btn-danger" 
                        onclick="return confirm('Ini akan mengganti semua backup codes yang ada. Apakah Anda yakin?')">
                    🔄 Generate New Codes
                </button>
            </form>
        </div>

        <div class="security-warning">
            <h3>🔒 Security Reminders</h3>
            <ul>
                <li>Print atau simpan codes di tempat yang aman offline</li>
                <li>Jangan simpan codes di email atau cloud storage</li>
                <li>Setiap code hanya dapat digunakan sekali</li>
                <li>Generate codes baru jika ada yang hilang atau bocor</li>
                <li>Gunakan codes hanya saat kehilangan akses 2FA</li>
            </ul>
        </div>

        <div class="back-link">
            <a href="setup_2fa.php">← Back to 2FA Settings</a> |
            <a href="<?= $_SESSION['role'] == 'admin' ? 'admin_dashboard.php' : 'pasien_dashboard.php' ?>">Dashboard</a>
        </div>
    </div>
</body>
</html>
