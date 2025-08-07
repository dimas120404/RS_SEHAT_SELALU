<?php
include 'koneksi.php';
include 'config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.html");
    exit();
}

$client_ip = get_client_ip();
$admin_id = $_SESSION['user_id'];

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $message = "Invalid CSRF token.";
    } else {
        switch ($action) {
            case 'unlock_account':
                $user_id = (int)$_POST['user_id'];
                $stmt = $conn->prepare("UPDATE users SET account_locked = 0, login_attempts = 0 WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                if ($stmt->execute()) {
                    $security->log_security_event($admin_id, 'ACCOUNT_UNLOCKED', "Admin unlocked user account ID: $user_id", $client_ip);
                    $message = "Account unlocked successfully.";
                }
                break;
                
            case 'clear_login_attempts':
                $conn->query("DELETE FROM login_attempts WHERE timestamp < UNIX_TIMESTAMP() - 3600");
                $security->log_security_event($admin_id, 'LOGIN_ATTEMPTS_CLEARED', "Admin cleared old login attempts", $client_ip);
                $message = "Login attempts cleared.";
                break;
                
            case 'disable_2fa':
                $user_id = (int)$_POST['user_id'];
                $stmt = $conn->prepare("UPDATE users SET two_factor_enabled = 0, two_factor_secret = NULL WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                if ($stmt->execute()) {
                    $security->log_security_event($admin_id, 'ADMIN_2FA_DISABLED', "Admin disabled 2FA for user ID: $user_id", $client_ip);
                    $message = "2FA disabled for user.";
                }
                break;
        }
    }
}

// Get security statistics
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $result->fetch_assoc()['total'];

// Locked accounts
$result = $conn->query("SELECT COUNT(*) as locked FROM users WHERE account_locked = 1");
$stats['locked_accounts'] = $result->fetch_assoc()['locked'];

// Users with 2FA enabled
$result = $conn->query("SELECT COUNT(*) as with_2fa FROM users WHERE two_factor_enabled = 1");
$stats['users_with_2fa'] = $result->fetch_assoc()['with_2fa'];

// Recent login attempts (last 24 hours)
$result = $conn->query("SELECT COUNT(*) as recent_attempts FROM login_attempts WHERE timestamp > UNIX_TIMESTAMP() - 86400");
$stats['recent_attempts'] = $result->fetch_assoc()['recent_attempts'];

// Failed login attempts (last 24 hours)
$result = $conn->query("SELECT COUNT(*) as failed_attempts FROM login_attempts WHERE timestamp > UNIX_TIMESTAMP() - 86400 AND success = 0");
$stats['failed_attempts'] = $result->fetch_assoc()['failed_attempts'];

// Get recent security events
$security_events = [];
$result = $conn->query("SELECT * FROM security_logs ORDER BY timestamp DESC LIMIT 20");
while ($row = $result->fetch_assoc()) {
    $security_events[] = $row;
}

// Get locked accounts
$locked_accounts = [];
$result = $conn->query("SELECT id, username, login_attempts, last_login FROM users WHERE account_locked = 1");
while ($row = $result->fetch_assoc()) {
    $locked_accounts[] = $row;
}

// Get users with 2FA
$users_2fa = [];
$result = $conn->query("SELECT id, username, two_factor_enabled, last_login FROM users WHERE two_factor_enabled = 1");
while ($row = $result->fetch_assoc()) {
    $users_2fa[] = $row;
}

// Get recent login attempts with details
$recent_attempts = [];
$result = $conn->query("SELECT * FROM login_attempts WHERE timestamp > UNIX_TIMESTAMP() - 86400 ORDER BY timestamp DESC LIMIT 50");
while ($row = $result->fetch_assoc()) {
    $recent_attempts[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Security Dashboard - RS Sehat Selalu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            color: #666;
            margin-top: 10px;
        }
        .danger {
            color: #e74c3c !important;
        }
        .success {
            color: #27ae60 !important;
        }
        .section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .section h2 {
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
        }
        .btn:hover {
            background: #5a67d8;
        }
        .btn-danger {
            background: #e74c3c;
        }
        .btn-danger:hover {
            background: #c0392b;
        }
        .btn-success {
            background: #27ae60;
        }
        .btn-success:hover {
            background: #229954;
        }
        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
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
            font-weight: bold;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .log-entry {
            margin-bottom: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .log-timestamp {
            font-size: 12px;
            color: #666;
        }
        .log-event {
            font-weight: bold;
            color: #333;
        }
        .log-details {
            color: #666;
            margin-top: 5px;
        }
        .tabs {
            display: flex;
            border-bottom: 2px solid #667eea;
            margin-bottom: 20px;
        }
        .tab {
            background: #f8f9fa;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px 4px 0 0;
        }
        .tab.active {
            background: #667eea;
            color: white;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔐 Security Dashboard</h1>
        <p>RS Sehat Selalu - Admin Security Control Panel</p>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $stats['total_users'] ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-number <?= $stats['locked_accounts'] > 0 ? 'danger' : 'success' ?>">
                <?= $stats['locked_accounts'] ?>
            </div>
            <div class="stat-label">Locked Accounts</div>
        </div>
        <div class="stat-card">
            <div class="stat-number success"><?= $stats['users_with_2fa'] ?></div>
            <div class="stat-label">Users with 2FA</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stats['recent_attempts'] ?></div>
            <div class="stat-label">Login Attempts (24h)</div>
        </div>
        <div class="stat-card">
            <div class="stat-number <?= $stats['failed_attempts'] > 10 ? 'danger' : '' ?>">
                <?= $stats['failed_attempts'] ?>
            </div>
            <div class="stat-label">Failed Attempts (24h)</div>
        </div>
    </div>

    <div class="section">
        <h2>Security Management</h2>
        <form method="POST" style="display: inline;">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="clear_login_attempts">
            <button type="submit" class="btn btn-success">Clear Old Login Attempts</button>
        </form>
    </div>

    <div class="section">
        <div class="tabs">
            <button class="tab active" onclick="showTab('events')">Security Events</button>
            <button class="tab" onclick="showTab('locked')">Locked Accounts</button>
            <button class="tab" onclick="showTab('2fa')">2FA Users</button>
            <button class="tab" onclick="showTab('attempts')">Login Attempts</button>
        </div>

        <div id="events" class="tab-content active">
            <h3>Recent Security Events</h3>
            <?php foreach ($security_events as $event): ?>
                <div class="log-entry">
                    <div class="log-timestamp"><?= $event['timestamp'] ?></div>
                    <div class="log-event"><?= htmlspecialchars($event['event_type']) ?></div>
                    <div class="log-details">
                        User ID: <?= $event['user_id'] ?: 'N/A' ?> | 
                        IP: <?= htmlspecialchars($event['ip_address']) ?> | 
                        Details: <?= htmlspecialchars($event['details']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="locked" class="tab-content">
            <h3>Locked Accounts</h3>
            <?php if (empty($locked_accounts)): ?>
                <p>No locked accounts.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Failed Attempts</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($locked_accounts as $account): ?>
                            <tr>
                                <td><?= $account['id'] ?></td>
                                <td><?= htmlspecialchars($account['username']) ?></td>
                                <td><?= $account['login_attempts'] ?></td>
                                <td><?= $account['last_login'] ?: 'Never' ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="unlock_account">
                                        <input type="hidden" name="user_id" value="<?= $account['id'] ?>">
                                        <button type="submit" class="btn btn-success">Unlock</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div id="2fa" class="tab-content">
            <h3>Users with 2FA Enabled</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>2FA Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users_2fa as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><span class="success">Enabled</span></td>
                            <td><?= $user['last_login'] ?: 'Never' ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="disable_2fa">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to disable 2FA for this user?')">Disable 2FA</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="attempts" class="tab-content">
            <h3>Recent Login Attempts (24h)</h3>
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Username</th>
                        <th>IP Address</th>
                        <th>Success</th>
                        <th>User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_attempts as $attempt): ?>
                        <tr>
                            <td><?= date('Y-m-d H:i:s', $attempt['timestamp']) ?></td>
                            <td><?= htmlspecialchars($attempt['username'] ?: 'N/A') ?></td>
                            <td><?= htmlspecialchars($attempt['ip_address']) ?></td>
                            <td>
                                <span class="<?= $attempt['success'] ? 'success' : 'danger' ?>">
                                    <?= $attempt['success'] ? 'Success' : 'Failed' ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars(substr($attempt['user_agent'] ?: 'N/A', 0, 50)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="back-link">
        <a href="admin_dashboard.php">← Back to Admin Dashboard</a>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }

        // Auto-refresh every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
