<?php
// Database configuration with security enhancements
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "rs_sehat_selalu";

// Security configurations
define('ENCRYPTION_KEY', 'your-32-character-secret-key-here'); // Change this in production
define('ENCRYPTION_METHOD', 'AES-256-CBC');

// Create connection with error handling and security
try {
    // SSL options for database connection (uncomment if using SSL)
    /*
    $ssl_options = [
        MYSQLI_OPT_SSL_VERIFY_SERVER_CERT => true,
        MYSQLI_OPT_SSL_CIPHER => 'AES256-SHA'
    ];
    */
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Koneksi database gagal: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
    // Set timezone
    $conn->query("SET time_zone = '+07:00'");
    
    // Enable SQL strict mode for better security
    $conn->query("SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
    
    // Set session timeout
    $conn->query("SET SESSION wait_timeout = 600");
    $conn->query("SET SESSION interactive_timeout = 600");
    
} catch (Exception $e) {
    // Log error securely
    error_log("Database connection error: " . $e->getMessage());
    
    // Display user-friendly error without revealing sensitive information
    die("Maaf, sistem sedang mengalami gangguan. Silakan coba lagi nanti.");
}

// Database encryption functions
function encrypt_db_value($value, $key = ENCRYPTION_KEY) {
    if (empty($value)) return $value;
    
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt($value, ENCRYPTION_METHOD, $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function decrypt_db_value($encrypted_value, $key = ENCRYPTION_KEY) {
    if (empty($encrypted_value)) return $encrypted_value;
    
    $data = base64_decode($encrypted_value);
    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    return openssl_decrypt($encrypted, ENCRYPTION_METHOD, $key, 0, $iv);
}

// Secure query execution with logging
function secure_query($conn, $query, $params = [], $log_query = false) {
    try {
        if ($log_query) {
            error_log("SQL Query: " . $query);
        }
        
        if (!empty($params)) {
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            $result = $stmt->execute();
            
            if (!$result) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            return $stmt;
        } else {
            $result = $conn->query($query);
            if (!$result) {
                throw new Exception("Query failed: " . $conn->error);
            }
            return $result;
        }
    } catch (Exception $e) {
        error_log("Database query error: " . $e->getMessage());
        throw $e;
    }
}

// Audit logging function
function log_data_change($conn, $table, $record_id, $action, $old_values = null, $new_values = null, $user_id = null) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $stmt = $conn->prepare("INSERT INTO audit_logs (table_name, record_id, action, old_values, new_values, user_id, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisssis", $table, $record_id, $action, $old_values, $new_values, $user_id, $ip_address);
    $stmt->execute();
}

// Input sanitization function
function sanitize_input($input) {
    if (is_array($input)) {
        return array_map('sanitize_input', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Get client IP address
function get_client_ip() {
    $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

// Initialize security manager
require_once 'security.php';
$security = init_security_manager($conn);

// Force HTTPS if not in development
if (!defined('DEVELOPMENT_MODE') || DEVELOPMENT_MODE === false) {
    force_https();
}
?>
