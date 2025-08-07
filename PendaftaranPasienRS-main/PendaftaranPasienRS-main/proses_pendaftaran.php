<?php
include 'koneksi.php';
include 'config.php';

// CSRF protection
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die("Invalid CSRF token. <a href='daftar.html'>Try again</a>");
}

$username = sanitize_input($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = 'pasien';

$status = "";
$link = "";

// Input validation
if (empty($username) || empty($password) || empty($confirm_password)) {
    $status = "Semua field harus diisi.";
    $link = "<a href='daftar.html'>Coba lagi</a>";
} elseif ($password !== $confirm_password) {
    $status = "Password dan konfirmasi password tidak cocok.";
    $link = "<a href='daftar.html'>Coba lagi</a>";
} elseif (!$security->validate_input($username, 'username')) {
    $status = "Username harus 3-20 karakter dan hanya boleh mengandung huruf, angka, dan underscore.";
    $link = "<a href='daftar.html'>Coba lagi</a>";
} elseif (!$security->validate_input($password, 'password')) {
    $status = "Password harus minimal 8 karakter dengan kombinasi huruf besar, huruf kecil, dan angka.";
    $link = "<a href='daftar.html'>Coba lagi</a>";
} else {
    try {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $status = "Username <strong>$username</strong> sudah digunakan.";
            $link = "<a href='daftar.html'>Coba lagi</a>";
        } else {
            // Hash password
            $hashed_password = $security->hash_password($password);
            
            // Insert new user
            $insert_stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $username, $hashed_password, $role);
            
            if ($insert_stmt->execute()) {
                $user_id = $conn->insert_id;
                
                // Log the registration
                $client_ip = get_client_ip();
                $security->log_security_event($user_id, 'USER_REGISTERED', "New user registered: $username", $client_ip);
                
                // Log data change
                log_data_change($conn, 'users', $user_id, 'INSERT', null, json_encode(['username' => $username, 'role' => $role]), $user_id);
                
                $status = "Pendaftaran berhasil! Akun Anda telah dibuat dengan keamanan tinggi.";
                $link = "<a href='index.html'>Login sekarang</a>";
            } else {
                $status = "Pendaftaran gagal. Silakan coba lagi.";
                $link = "<a href='daftar.html'>Kembali</a>";
            }
        }
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        $status = "Terjadi kesalahan sistem. Silakan coba lagi.";
        $link = "<a href='daftar.html'>Kembali</a>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hasil Pendaftaran</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: url('rumah sakit.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.9);
            width: 400px;
            margin: 100px auto;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 0 10px gray;
        }
        a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Informasi Pendaftaran</h2>
    <p><?= $status ?></p>
    <p><?= $link ?></p>
</div>

</body>
</html>
