<?php
// Configuration file for RS Sehat Selalu
// Security and system settings

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'rs_sehat_selalu');

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

// Application settings
define('APP_NAME', 'RS Sehat Selalu');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'Asia/Jakarta');

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// Error reporting (set to false in production)
define('DEBUG_MODE', true);

// Security functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_phone($phone) {
    return preg_match('/^[0-9+\-\s()]+$/', $phone);
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function check_session_timeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        header("Location: index.html?error=session_expired");
        exit();
    }
    $_SESSION['last_activity'] = time();
}

function log_activity($user, $action, $details = '') {
    $log_file = 'logs/activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] User: $user, Action: $action, Details: $details\n";
    
    if (!is_dir('logs')) {
        mkdir('logs', 0755, true);
    }
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Initialize session with security settings
function init_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
    
    check_session_timeout();
}

// Error handling
function handle_error($errno, $errstr, $errfile, $errline) {
    if (DEBUG_MODE) {
        echo "<b>Error:</b> [$errno] $errstr<br>";
        echo "Line $errline in $errfile<br>";
    } else {
        error_log("Error: [$errno] $errstr in $errfile on line $errline");
    }
    return true;
}

// Set error handler
set_error_handler('handle_error');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Initialize secure session
init_secure_session();
?> 