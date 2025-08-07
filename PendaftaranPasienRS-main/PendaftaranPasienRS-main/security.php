<?php
// Security utilities for RS Sehat Selalu
// Includes password hashing, rate limiting, and security functions

class SecurityManager {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Password hashing functions
    public function hash_password($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    public function verify_password($password, $hash) {
        return password_verify($password, $hash);
    }
    
    // Rate limiting functions
    public function check_rate_limit($ip, $username = null, $limit = 5, $window = 900) {
        $identifier = $username ? $username : $ip;
        $current_time = time();
        $window_start = $current_time - $window;
        
        // Clean old attempts
        $this->conn->query("DELETE FROM login_attempts WHERE timestamp < $window_start");
        
        // Count recent attempts
        $stmt = $this->conn->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE identifier = ? AND timestamp > ?");
        $stmt->bind_param("si", $identifier, $window_start);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['attempts'] < $limit;
    }
    
    public function log_login_attempt($ip, $username = null, $success = false) {
        $identifier = $username ? $username : $ip;
        $timestamp = time();
        
        $stmt = $this->conn->prepare("INSERT INTO login_attempts (identifier, ip_address, username, success, timestamp) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $identifier, $ip, $username, $success, $timestamp);
        $stmt->execute();
    }
    
    // Two-factor authentication functions
    public function generate_2fa_secret() {
        return bin2hex(random_bytes(16));
    }
    
    public function generate_2fa_code($secret) {
        // Simple TOTP implementation
        $time = floor(time() / 30);
        $code = hash_hmac('sha1', pack('N*', 0) . pack('N*', $time), $secret, true);
        $offset = ord($code[19]) & 0xf;
        $value = (
            ((ord($code[$offset + 0]) & 0x7f) << 24) |
            ((ord($code[$offset + 1]) & 0xff) << 16) |
            ((ord($code[$offset + 2]) & 0xff) << 8) |
            (ord($code[$offset + 3]) & 0xff)
        ) % 1000000;
        return str_pad($value, 6, '0', STR_PAD_LEFT);
    }
    
    public function verify_2fa_code($secret, $code) {
        $time = floor(time() / 30);
        // Check current time window and previous/next to account for clock drift
        for ($i = -1; $i <= 1; $i++) {
            $test_time = $time + $i;
            $test_code = hash_hmac('sha1', pack('N*', 0) . pack('N*', $test_time), $secret, true);
            $offset = ord($test_code[19]) & 0xf;
            $value = (
                ((ord($test_code[$offset + 0]) & 0x7f) << 24) |
                ((ord($test_code[$offset + 1]) & 0xff) << 16) |
                ((ord($test_code[$offset + 2]) & 0xff) << 8) |
                (ord($test_code[$offset + 3]) & 0xff)
            ) % 1000000;
            if (str_pad($value, 6, '0', STR_PAD_LEFT) === $code) {
                return true;
            }
        }
        return false;
    }
    
    public function setup_2fa($user_id, $secret) {
        $stmt = $this->conn->prepare("UPDATE users SET two_factor_secret = ?, two_factor_enabled = 1 WHERE id = ?");
        $stmt->bind_param("si", $secret, $user_id);
        return $stmt->execute();
    }
    
    public function disable_2fa($user_id) {
        $stmt = $this->conn->prepare("UPDATE users SET two_factor_secret = NULL, two_factor_enabled = 0 WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }
    
    // Security logging
    public function log_security_event($user_id, $event_type, $details, $ip_address) {
        $stmt = $this->conn->prepare("INSERT INTO security_logs (user_id, event_type, details, ip_address, timestamp) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("isss", $user_id, $event_type, $details, $ip_address);
        return $stmt->execute();
    }
    
    // HTTPS enforcement
    public function enforce_https() {
        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
            if (isset($_SERVER['HTTP_HOST'])) {
                $redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                header("Location: $redirect_url", true, 301);
                exit();
            }
        }
    }
    
    // Input validation and sanitization
    public function validate_input($input, $type = 'string', $max_length = 255) {
        $input = trim($input);
        
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_VALIDATE_EMAIL) !== false;
            case 'username':
                return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $input);
            case 'password':
                return strlen($input) >= 8 && preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $input);
            case 'phone':
                return preg_match('/^[0-9+\-\s()]{10,20}$/', $input);
            case 'string':
            default:
                return strlen($input) <= $max_length && !preg_match('/[<>"\']/', $input);
        }
    }
    
    public function sanitize_input($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    // Database encryption helpers
    public function encrypt_data($data, $key) {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    public function decrypt_data($encrypted_data, $key) {
        $data = base64_decode($encrypted_data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
}

// Initialize security manager
function init_security_manager($conn) {
    return new SecurityManager($conn);
}

// HTTPS enforcement helper
function force_https() {
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        if (isset($_SERVER['HTTP_HOST'])) {
            $redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location: $redirect_url", true, 301);
            exit();
        }
    }
}
?>
