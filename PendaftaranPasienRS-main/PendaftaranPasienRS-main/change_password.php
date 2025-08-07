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
$stmt = $conn->prepare("SELECT username, password, password_changed_at FROM users WHERE id = ?");
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
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // CSRF protection
    if (!verify_csrf_token($csrf_token)) {
        $message = "Invalid CSRF token.";
    } elseif (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "Semua field harus diisi.";
    } elseif ($new_password !== $confirm_password) {
        $message = "Password baru dan konfirmasi password tidak cocok.";
    } elseif (!$security->validate_input($new_password, 'password')) {
        $message = "Password baru harus minimal 8 karakter dengan kombinasi huruf besar, huruf kecil, dan angka.";
    } else {
        // Rate limiting for password changes
        if (!$security->check_rate_limit($client_ip, $user['username'], 3, 3600)) {
            $message = "Terlalu banyak percobaan ganti password. Silakan coba lagi dalam 1 jam.";
        } else {
            try {
                // Verify current password
                $password_valid = false;
                
                // Check if current password is MD5 (backward compatibility)
                if (strlen($user['password']) == 32 && ctype_xdigit($user['password'])) {
                    $password_valid = ($user['password'] === md5($current_password));
                } else {
                    $password_valid = $security->verify_password($current_password, $user['password']);
                }
                
                if (!$password_valid) {
                    $message = "Password lama tidak benar.";
                    $security->log_security_event($user_id, 'PASSWORD_CHANGE_FAILED', "Invalid current password", $client_ip);
                } else {
                    // Check if new password is different from current
                    if ($current_password === $new_password) {
                        $message = "Password baru harus berbeda dari password lama.";
                    } else {
                        // Hash new password
                        $new_hashed_password = $security->hash_password($new_password);
                        
                        // Update password
                        $update_stmt = $conn->prepare("UPDATE users SET password = ?, password_changed_at = NOW() WHERE id = ?");
                        $update_stmt->bind_param("si", $new_hashed_password, $user_id);
                        
                        if ($update_stmt->execute()) {
                            // Log successful password change
                            $security->log_security_event($user_id, 'PASSWORD_CHANGED', "User changed password successfully", $client_ip);
                            
                            // Log data change
                            log_data_change($conn, 'users', $user_id, 'UPDATE', 
                                json_encode(['action' => 'password_change', 'timestamp' => date('Y-m-d H:i:s')]),
                                json_encode(['action' => 'password_change', 'timestamp' => date('Y-m-d H:i:s')]),
                                $user_id
                            );
                            
                            // Invalidate all existing sessions except current
                            $session_id = session_id();
                            $invalidate_stmt = $conn->prepare("DELETE FROM user_sessions WHERE user_id = ? AND session_id != ?");
                            $invalidate_stmt->bind_param("is", $user_id, $session_id);
                            $invalidate_stmt->execute();
                            
                            $message = "Password berhasil diubah! Sesi lain telah dilogout untuk keamanan.";
                            $success = true;
                        } else {
                            $message = "Gagal mengubah password. Silakan coba lagi.";
                        }
                    }
                }
                
                // Log attempt
                $security->log_login_attempt($client_ip, $user['username'], $success);
                
            } catch (Exception $e) {
                error_log("Password change error: " . $e->getMessage());
                $message = "Terjadi kesalahan sistem. Silakan coba lagi.";
            }
        }
    }
}

// Check if password is old (needs to be changed)
$password_age_days = 0;
if ($user['password_changed_at']) {
    $password_age = time() - strtotime($user['password_changed_at']);
    $password_age_days = floor($password_age / (24 * 60 * 60));
}

$show_password_age_warning = $password_age_days > 90; // 90 days
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password - RS Sehat Selalu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        input[type="password"]:focus {
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
        .warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .password-requirements {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .password-strength {
            margin-top: 10px;
            padding: 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .strength-weak {
            background-color: #f8d7da;
            color: #721c24;
        }
        .strength-medium {
            background-color: #fff3cd;
            color: #856404;
        }
        .strength-strong {
            background-color: #d4edda;
            color: #155724;
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
        }
        .security-info h3 {
            margin-top: 0;
            color: #333;
        }
        .password-age {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }
        .show-password {
            display: inline-block;
            margin-top: 5px;
            cursor: pointer;
            color: #667eea;
            font-size: 12px;
        }
        .show-password:hover {
            text-decoration: underline;
        }
        .password-input-group {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔒 Change Password</h1>
            <p>User: <strong><?= htmlspecialchars($user['username']) ?></strong></p>
        </div>

        <?php if ($message): ?>
            <div class="message <?= $success ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($show_password_age_warning): ?>
            <div class="message warning">
                <strong>⚠️ Password Lama Terdeteksi!</strong><br>
                Password Anda sudah berumur <?= $password_age_days ?> hari. 
                Disarankan untuk mengubah password secara berkala untuk keamanan.
            </div>
        <?php endif; ?>

        <div class="security-info">
            <h3>Password Security Requirements</h3>
            <ul>
                <li>Minimal 8 karakter</li>
                <li>Kombinasi huruf besar dan kecil</li>
                <li>Minimal 1 angka</li>
                <li>Hindari menggunakan informasi pribadi</li>
                <li>Jangan gunakan password yang sama dengan akun lain</li>
            </ul>
            <div class="password-age">
                <strong>Password terakhir diubah:</strong> 
                <?= $user['password_changed_at'] ? date('d M Y H:i', strtotime($user['password_changed_at'])) : 'Tidak diketahui' ?>
                (<?= $password_age_days ?> hari yang lalu)
            </div>
        </div>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            
            <div class="form-group">
                <label for="current_password">Password Lama:</label>
                <div class="password-input-group">
                    <input type="password" id="current_password" name="current_password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('current_password')">👁️</button>
                </div>
            </div>

            <div class="form-group">
                <label for="new_password">Password Baru:</label>
                <div class="password-input-group">
                    <input type="password" id="new_password" name="new_password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('new_password')">👁️</button>
                </div>
                <div class="password-requirements">
                    Minimal 8 karakter, kombinasi huruf besar, huruf kecil, dan angka
                </div>
                <div id="password-strength" class="password-strength" style="display: none;"></div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password Baru:</label>
                <div class="password-input-group">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">👁️</button>
                </div>
            </div>

            <button type="submit" class="btn">Ubah Password</button>
        </form>

        <div class="info">
            <h3>🔐 Keamanan Setelah Mengubah Password</h3>
            <p>Setelah password berhasil diubah:</p>
            <ul>
                <li>Semua sesi login lain akan otomatis logout</li>
                <li>Anda tetap login di sesi ini</li>
                <li>Aktivitas akan dicatat dalam log keamanan</li>
                <li>Notifikasi keamanan akan dikirim (jika aktif)</li>
            </ul>
        </div>

        <div class="back-link">
            <a href="<?= $_SESSION['role'] == 'admin' ? 'admin_dashboard.php' : 'pasien_dashboard.php' ?>">← Back to Dashboard</a>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
        }

        // Password strength checker
        document.getElementById('new_password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthDiv = document.getElementById('password-strength');
            
            if (password.length === 0) {
                strengthDiv.style.display = 'none';
                return;
            }
            
            let strength = 0;
            let feedback = [];
            
            // Length check
            if (password.length >= 8) {
                strength += 1;
            } else {
                feedback.push('Minimal 8 karakter');
            }
            
            // Lowercase check
            if (/[a-z]/.test(password)) {
                strength += 1;
            } else {
                feedback.push('Perlu huruf kecil');
            }
            
            // Uppercase check
            if (/[A-Z]/.test(password)) {
                strength += 1;
            } else {
                feedback.push('Perlu huruf besar');
            }
            
            // Number check
            if (/[0-9]/.test(password)) {
                strength += 1;
            } else {
                feedback.push('Perlu angka');
            }
            
            // Special character bonus
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                strength += 1;
            }
            
            // Display strength
            strengthDiv.style.display = 'block';
            let strengthClass = 'strength-weak';
            let strengthText = 'Lemah';
            
            if (strength >= 4) {
                strengthClass = 'strength-strong';
                strengthText = 'Kuat';
            } else if (strength >= 2) {
                strengthClass = 'strength-medium';
                strengthText = 'Sedang';
            }
            
            strengthDiv.className = 'password-strength ' + strengthClass;
            strengthDiv.innerHTML = `Kekuatan: ${strengthText}` + 
                (feedback.length > 0 ? ` - ${feedback.join(', ')}` : '');
        });

        // Confirm password match checker
        document.getElementById('confirm_password').addEventListener('input', function(e) {
            const password = document.getElementById('new_password').value;
            const confirmPassword = e.target.value;
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    e.target.style.borderColor = '#28a745';
                } else {
                    e.target.style.borderColor = '#dc3545';
                }
            } else {
                e.target.style.borderColor = '#ddd';
            }
        });
    </script>
</body>
</html>
