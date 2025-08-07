<?php
include 'koneksi.php';
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.html");
    exit();
}

$username = sanitize_input($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$two_factor_code = sanitize_input($_POST['2fa_code'] ?? '');
$csrf_token = $_POST['csrf_token'] ?? '';

// CSRF protection
if (!verify_csrf_token($csrf_token)) {
    die("Invalid CSRF token. <a href='index.html'>Try again</a>");
}

// Input validation
if (empty($username) || empty($password)) {
    echo "Username dan password harus diisi. <a href='index.html'>Coba lagi</a>";
    exit();
}

// Get client IP
$client_ip = get_client_ip();

// Rate limiting check
if (!$security->check_rate_limit($client_ip, $username)) {
    $security->log_security_event(null, 'RATE_LIMIT_EXCEEDED', "Too many login attempts from IP: $client_ip", $client_ip);
    die("Terlalu banyak percobaan login. Silakan coba lagi dalam 15 menit. <a href='index.html'>Kembali</a>");
}

try {
    // Get user data with prepared statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        // Check if account is locked
        if ($user['account_locked']) {
            $security->log_security_event($user['id'], 'LOGIN_ATTEMPT_LOCKED', "Login attempt on locked account", $client_ip);
            die("Akun Anda telah dikunci. Silakan hubungi administrator. <a href='index.html'>Kembali</a>");
        }
        
        // Verify password
        $password_valid = false;
        
        // Check if password is still MD5 (for backward compatibility)
        if (strlen($user['password']) == 32 && ctype_xdigit($user['password'])) {
            // Legacy MD5 password
            $password_valid = ($user['password'] === md5($password));
            
            // Upgrade to password_hash if login successful
            if ($password_valid) {
                $new_hash = $security->hash_password($password);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $new_hash, $user['id']);
                $update_stmt->execute();
                
                $security->log_security_event($user['id'], 'PASSWORD_UPGRADED', "Password upgraded from MD5 to bcrypt", $client_ip);
            }
        } else {
            // Modern password_hash
            $password_valid = $security->verify_password($password, $user['password']);
        }
        
        if ($password_valid) {
            // Check 2FA if enabled
            if ($user['two_factor_enabled']) {
                if (empty($two_factor_code)) {
                    // Redirect to 2FA input page
                    $_SESSION['pending_2fa_user'] = $user['id'];
                    header("Location: two_factor_input.php");
                    exit();
                } else {
                    // Verify 2FA code
                    if (!$security->verify_2fa_code($user['two_factor_secret'], $two_factor_code)) {
                        $security->log_login_attempt($client_ip, $username, false);
                        $security->log_security_event($user['id'], 'TWO_FACTOR_FAILED', "Invalid 2FA code", $client_ip);
                        die("Kode 2FA tidak valid. <a href='index.html'>Coba lagi</a>");
                    }
                }
            }
            
            // Successful login
            session_regenerate_id(true);
            
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['last_activity'] = time();
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            
            // Update last login
            $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW(), login_attempts = 0 WHERE id = ?");
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();
            
            // Log successful login
            $security->log_login_attempt($client_ip, $username, true);
            $security->log_security_event($user['id'], 'LOGIN_SUCCESS', "Successful login", $client_ip);
            
            // Create session record
            $session_id = session_id();
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $expires_at = date('Y-m-d H:i:s', time() + SESSION_TIMEOUT);
            
            $session_stmt = $conn->prepare("INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, ?)");
            $session_stmt->bind_param("issss", $user['id'], $session_id, $client_ip, $user_agent, $expires_at);
            $session_stmt->execute();
            
            // Redirect based on role
            if ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: pasien_dashboard.php");
            }
            exit();
        } else {
            // Invalid password
            $login_attempts = $user['login_attempts'] + 1;
            
            // Lock account after 5 failed attempts
            if ($login_attempts >= 5) {
                $lock_stmt = $conn->prepare("UPDATE users SET account_locked = 1, login_attempts = ? WHERE id = ?");
                $lock_stmt->bind_param("ii", $login_attempts, $user['id']);
                $lock_stmt->execute();
                
                $security->log_security_event($user['id'], 'ACCOUNT_LOCKED', "Account locked after 5 failed attempts", $client_ip);
                $message = "Akun Anda telah dikunci karena terlalu banyak percobaan login yang gagal. Silakan hubungi administrator.";
            } else {
                $attempt_stmt = $conn->prepare("UPDATE users SET login_attempts = ? WHERE id = ?");
                $attempt_stmt->bind_param("ii", $login_attempts, $user['id']);
                $attempt_stmt->execute();
                
                $remaining = 5 - $login_attempts;
                $message = "Login gagal. Username atau password salah. Percobaan tersisa: $remaining";
            }
            
            $security->log_login_attempt($client_ip, $username, false);
            $security->log_security_event($user['id'], 'LOGIN_FAILED', "Invalid password", $client_ip);
            
            echo "$message <a href='index.html'>Coba lagi</a>";
        }
    } else {
        // User not found
        $security->log_login_attempt($client_ip, $username, false);
        $security->log_security_event(null, 'LOGIN_FAILED', "User not found: $username", $client_ip);
        echo "Login gagal. Username atau password salah. <a href='index.html'>Coba lagi</a>";
    }
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    $security->log_security_event(null, 'LOGIN_ERROR', $e->getMessage(), $client_ip);
    echo "Terjadi kesalahan sistem. Silakan coba lagi. <a href='index.html'>Kembali</a>";
}
?>
