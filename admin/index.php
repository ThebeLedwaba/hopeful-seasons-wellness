<?php
session_start();
require_once '../forms/config.php';

// Simple Authentication (Hardcoded for MVP)
// In production, use database users and password hashing
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'hopeful2026'); // Change this!

// Handle Login
if (isset($_POST['login'])) {
    if ($_POST['username'] === ADMIN_USER && $_POST['password'] === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid credentials";
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Check Auth
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Read Log File
$logFile = '../forms/logs/activity.log'; // Path relative to admin/index.php? No, admin/ is dir. 
// config.php defines logActivity using __DIR__ . '/../logs/activity.log' relative to forms/.
// forms/../logs is same as logs/.
// So from admin/index.php, root is ../. logs is ../logs/activity.log?
// Let's check where config saves it.
// config path: forms/config.php. Log path: forms/../logs/ = logs/.
// So file is at root/logs/activity.log.
// Admin is at root/admin/index.php. So path is ../logs/activity.log.

$logs = [];
if ($isLoggedIn && file_exists('../logs/activity.log')) {
    $logs = file('../logs/activity.log');
    $logs = array_reverse($logs); // Newest first
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hopeful Seasons</title>
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: var(--font-body); background: #f4f4f4; margin:0; }
        .admin-container { max-width: 1000px; margin: 2rem auto; padding: 1rem; }
        .login-box { max-width: 400px; margin: 100px auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 1rem; }
        .log-entry { padding: 10px; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        .log-entry:last-child { border-bottom: none; }
        .timestamp { color: #888; font-size: 0.8rem; margin-right: 10px; }
        .btn { padding: 0.5rem 1rem; background: var(--primary-color); color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .btn:hover { opacity: 0.9; }
        input { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
    </style>
</head>
<body>

<?php if (!$isLoggedIn): ?>
    <div class="login-box">
        <h2 style="text-align: center; color: var(--primary-color);">Admin Login</h2>
        <?php if (isset($error)) echo "<p style='color:red; text-align:center'>$error</p>"; ?>
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <button type="submit" name="login" class="btn" style="width:100%">Login</button>
        </form>
    </div>
<?php else: ?>
    <div class="admin-container">
        <div class="dashboard-header">
            <h1>Dashboard</h1>
            <a href="?logout" class="btn" style="background: #e74c3c;">Logout</a>
        </div>

        <div class="card">
            <h3><i class="fas fa-history"></i> Recent Activity Log</h3>
            <div class="log-list">
                <?php if (empty($logs)): ?>
                    <p style="color: #666; font-style: italic;">No activity recorded yet.</p>
                <?php else: ?>
                    <?php foreach (array_slice($logs, 0, 50) as $line): ?>
                        <div class="log-entry">
                            <?php echo htmlspecialchars($line); ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

</body>
</html>
