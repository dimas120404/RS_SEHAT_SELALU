<?php
include 'koneksi.php';
include 'config.php';

// Check if user is in pending 2FA state
if (!isset($_SESSION['pending_2fa_user'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['pending_2fa_user'];
$client_ip = get_client_ip();

// Get user info
$stmt = $conn->prepare("SELECT username, two_factor_secret FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !$user['two_factor_secret']) {
    header("Location: index.html");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = sanitize_input($_POST['code'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // CSRF protection
    if (!verify_csrf_token($csrf_token)) {
        $error = "Invalid CSRF token.";
    } elseif (empty($code)) {
        $error = "Kode 2FA diperlukan.";
    } elseif (!$security->verify_2fa_code($user['two_factor_secret'], $code)) {
        $security->log_login_attempt($client_ip, $user['username'], false);
        $security->log_security_event($user_id, 'TWO_FACTOR_FAILED', "Invalid 2FA code", $client_ip);
        $error = "Kode 2FA tidak valid.";
    } else {
        // 2FA verified, complete login
        session_regenerate_id(true);
        
        // Get full user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_full = $result->fetch_assoc();
        
        $_SESSION['username'] = $user_full['username'];
        $_SESSION['role'] = $user_full['role'];
        $_SESSION['user_id'] = $user_full['id'];
        $_SESSION['last_activity'] = time();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // Clear pending 2FA
        unset($_SESSION['pending_2fa_user']);
        
        // Update last login
        $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW(), login_attempts = 0 WHERE id = ?");
        $update_stmt->bind_param("i", $user_id);
        $update_stmt->execute();
        
        // Log successful login
        $security->log_login_attempt($client_ip, $user['username'], true);
        $security->log_security_event($user_id, 'LOGIN_SUCCESS', "Successful login with 2FA", $client_ip);
        
        // Create session record
        $session_id = session_id();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $expires_at = date('Y-m-d H:i:s', time() + SESSION_TIMEOUT);
        
        $session_stmt = $conn->prepare("INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, ?)");
        $session_stmt->bind_param("issss", $user_id, $session_id, $client_ip, $user_agent, $expires_at);
        $session_stmt->execute();
        
        // Redirect based on role
        if ($user_full['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: pasien_dashboard.php");
        }
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Two-Factor Authentication</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.95);
            width: 400px;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        .header {
            margin-bottom: 30px;
        }
        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            margin: 0;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            text-align: center;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn-submit {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
            width: 100%;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #495057;
        }
        .back-link {
            margin-top: 20px;
        }
        .back-link a {
            color: #667eea;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .security-icon {
            font-size: 48px;
            margin-bottom: 20px;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="security-icon">🔐</div>
            <h1>Two-Factor Authentication</h1>
            <p>User: <strong><?= htmlspecialchars($user['username']) ?></strong></p>
        </div>

        <div class="info">
            <p>Untuk menyelesaikan login, masukkan kode 6 digit dari aplikasi authenticator Anda.</p>
        </div>

        <?php if ($error): ?>
            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            
            <div class="form-group">
                <label for="code">Kode 2FA:</label>
                <input type="text" id="code" name="code" maxlength="6" pattern="[0-9]{6}" required autofocus>
            </div>

            <button type="submit" class="btn-submit">Verifikasi</button>
        </form>

        <div class="back-link">
            <a href="index.html">← Kembali ke Login</a>
        </div>
    </div>

    <script>
        // Auto-submit when 6 digits are entered
        document.getElementById('code').addEventListener('input', function(e) {
            if (e.target.value.length === 6) {
                // Small delay to allow user to see the complete code
                setTimeout(() => {
                    document.querySelector('form').submit();
                }, 500);
            }
        });
    </script>
</body>
</html>
