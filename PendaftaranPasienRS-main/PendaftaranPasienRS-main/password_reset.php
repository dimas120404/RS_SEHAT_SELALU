<?php
include 'koneksi.php';
include 'config.php';

$message = "";
$success = false;
$step = $_GET['step'] ?? 'request';
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_ip = get_client_ip();
    
    if ($step === 'request') {
        // Step 1: Request password reset
        $username = sanitize_input($_POST['username'] ?? '');
        $csrf_token = $_POST['csrf_token'] ?? '';
        
        if (!verify_csrf_token($csrf_token)) {
            $message = "Invalid CSRF token.";
        } elseif (empty($username)) {
            $message = "Username harus diisi.";
        } else {
            // Rate limiting for password reset requests
            if (!$security->check_rate_limit($client_ip, $username, 3, 3600)) {
                $message = "Terlalu banyak permintaan reset password. Silakan coba lagi dalam 1 jam.";
            } else {
                try {
                    // Check if user exists
                    $stmt = $conn->prepare("SELECT id, username FROM users WHERE username = ?");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();
                    
                    if ($user) {
                        // Generate secure token
                        $token = bin2hex(random_bytes(32));
                        $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour
                        
                        // Store token in database
                        $token_stmt = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                        $token_stmt->bind_param("iss", $user['id'], $token, $expires_at);
                        $token_stmt->execute();
                        
                        // Log security event
                        $security->log_security_event($user['id'], 'PASSWORD_RESET_REQUESTED', "Password reset requested for user: $username", $client_ip);
                        
                        // In production, send email with reset link
                        // For now, we'll show the link (remove this in production)
                        $reset_link = "http://localhost/rs_sehat_selalu/password_reset.php?step=reset&token=$token";
                        
                        $message = "Password reset link telah dikirim. <br><strong>Link reset:</strong> <a href='$reset_link'>$reset_link</a> <br><em>(Link berlaku 1 jam)</em>";
                        $success = true;
                    } else {
                        // Don't reveal if user exists or not
                        $message = "Jika username valid, link reset password akan dikirim.";
                        $success = true;
                    }
                    
                    // Log attempt regardless of user existence
                    $security->log_login_attempt($client_ip, $username, false);
                    
                } catch (Exception $e) {
                    error_log("Password reset error: " . $e->getMessage());
                    $message = "Terjadi kesalahan sistem. Silakan coba lagi.";
                }
            }
        }
    } elseif ($step === 'reset') {
        // Step 2: Reset password with token
        $new_password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $csrf_token = $_POST['csrf_token'] ?? '';
        
        if (!verify_csrf_token($csrf_token)) {
            $message = "Invalid CSRF token.";
        } elseif (empty($new_password) || empty($confirm_password)) {
            $message = "Semua field harus diisi.";
        } elseif ($new_password !== $confirm_password) {
            $message = "Password dan konfirmasi password tidak cocok.";
        } elseif (!$security->validate_input($new_password, 'password')) {
            $message = "Password harus minimal 8 karakter dengan kombinasi huruf besar, huruf kecil, dan angka.";
        } else {
            try {
                // Validate token
                $stmt = $conn->prepare("SELECT user_id FROM password_reset_tokens WHERE token = ? AND expires_at > NOW() AND used = 0");
                $stmt->bind_param("s", $token);
                $stmt->execute();
                $result = $stmt->get_result();
                $token_data = $result->fetch_assoc();
                
                if ($token_data) {
                    $user_id = $token_data['user_id'];
                    
                    // Hash new password
                    $hashed_password = $security->hash_password($new_password);
                    
                    // Update password
                    $update_stmt = $conn->prepare("UPDATE users SET password = ?, password_changed_at = NOW() WHERE id = ?");
                    $update_stmt->bind_param("si", $hashed_password, $user_id);
                    $update_stmt->execute();
                    
                    // Mark token as used
                    $used_stmt = $conn->prepare("UPDATE password_reset_tokens SET used = 1, used_at = NOW() WHERE token = ?");
                    $used_stmt->bind_param("s", $token);
                    $used_stmt->execute();
                    
                    // Log security event
                    $security->log_security_event($user_id, 'PASSWORD_RESET_COMPLETED', "Password reset completed", $client_ip);
                    
                    $message = "Password berhasil direset! Silakan login dengan password baru.";
                    $success = true;
                } else {
                    $message = "Token tidak valid atau sudah expired.";
                    $security->log_security_event(null, 'PASSWORD_RESET_INVALID_TOKEN', "Invalid token used: $token", $client_ip);
                }
                
            } catch (Exception $e) {
                error_log("Password reset completion error: " . $e->getMessage());
                $message = "Terjadi kesalahan sistem. Silakan coba lagi.";
            }
        }
    }
}

// For reset step, validate token first
if ($step === 'reset' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (empty($token)) {
        header("Location: password_reset.php");
        exit();
    }
    
    // Check token validity
    $stmt = $conn->prepare("SELECT user_id FROM password_reset_tokens WHERE token = ? AND expires_at > NOW() AND used = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result->fetch_assoc()) {
        $message = "Token tidak valid atau sudah expired.";
        $step = 'request';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - RS Sehat Selalu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.95);
            width: 400px;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
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
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #667eea;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .security-info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #495057;
        }
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reset Password</h1>
            <p>RS Sehat Selalu</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?= $success ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if ($step === 'request'): ?>
            <div class="security-info">
                <strong>Keamanan:</strong> Link reset password akan berlaku selama 1 jam dan hanya dapat digunakan sekali.
            </div>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <button type="submit" class="btn">Kirim Link Reset</button>
            </form>

        <?php elseif ($step === 'reset'): ?>
            <div class="security-info">
                <strong>Buat Password Baru:</strong> Password akan dienkripsi dengan keamanan tinggi.
            </div>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div class="form-group">
                    <label for="password">Password Baru:</label>
                    <input type="password" id="password" name="password" required>
                    <div class="password-requirements">
                        Minimal 8 karakter, kombinasi huruf besar, huruf kecil, dan angka
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn">Reset Password</button>
            </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="index.html">← Kembali ke Login</a>
        </div>
    </div>

    <script>
        // Password strength indicator
        document.getElementById('password')?.addEventListener('input', function(e) {
            const password = e.target.value;
            const requirements = document.querySelector('.password-requirements');
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            
            const colors = ['#e74c3c', '#f39c12', '#f1c40f', '#27ae60'];
            const texts = ['Lemah', 'Sedang', 'Baik', 'Kuat'];
            
            if (password.length > 0) {
                requirements.style.color = colors[strength - 1] || '#e74c3c';
                requirements.textContent = `Kekuatan password: ${texts[strength - 1] || 'Lemah'}`;
            } else {
                requirements.style.color = '#666';
                requirements.textContent = 'Minimal 8 karakter, kombinasi huruf besar, huruf kecil, dan angka';
            }
        });
    </script>
</body>
</html>
