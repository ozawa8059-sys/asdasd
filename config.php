<?php
session_start();
ob_start();

// ================= ENHANCED CONFIGURATION =================
$CONFIG = [
    'password' => 'Shuju@123',           // CHANGE THIS
    'username' => 'Shuju',          // CHANGE THIS
    'session_timeout' => 3600,
    'base_directory' => '/var/www/html',
    'allowed_ips' => [],
    'log_actions' => true,
    'max_upload_size' => 100 * 1024 * 1024,
    'allowed_commands' => ['ls', 'pwd', 'whoami', 'id', 'cat', 'tail', 'head', 'grep', 'find', 'ps', 'df', 'du', 'free', 'uname'],
    'blocked_commands' => ['rm -rf', 'mkfs', 'dd', 'chmod 777', 'passwd', '> /dev', 'nc -l', 'bash -i', 'wget', 'curl', 'python', 'perl', 'nc', 'netcat', 'sh', 'zsh', 'ksh'],
    'allowed_extensions' => ['txt', 'log', 'conf', 'json', 'xml', 'html', 'css', 'js', 'php', 'jpg', 'png', 'gif', 'pdf', 'zip', 'tar', 'gz'],
    'max_file_download_size' => 50 * 1024 * 1024
];
// ==========================================================

// Enhanced Security Functions
function checkAuth() {
    global $CONFIG;
    
    // IP Whitelist Check
    if (!empty($CONFIG['allowed_ips']) && !in_array($_SERVER['REMOTE_ADDR'], $CONFIG['allowed_ips'])) {
        logAction("BLOCKED_IP_ACCESS", $_SERVER['REMOTE_ADDR']);
        return false;
    }
    
    // Session Validation
    if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
        // Session Timeout Check
        if (time() - $_SESSION['login_time'] > $CONFIG['session_timeout']) {
            logAction("SESSION_TIMEOUT", $_SESSION['username']);
            session_destroy();
            return false;
        }
        
        // Regenerate Session ID periodically
        if (time() - $_SESSION['login_time'] > 1800) { // 30 minutes
            session_regenerate_id(true);
            $_SESSION['login_time'] = time();
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    return false;
}

function logAction($action, $details = '') {
    global $CONFIG;
    if (!$CONFIG['log_actions']) return;
    
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'UNAUTHENTICATED';
    $log = date('Y-m-d H:i:s') . " | " . $_SERVER['REMOTE_ADDR'] . " | $username | $action | $details\n";
    file_put_contents('admin_actions.log', $log, FILE_APPEND | LOCK_EX);
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
}

function isSafeCommand($command) {
    global $CONFIG;
    
    // Check against blocked commands
    foreach ($CONFIG['blocked_commands'] as $cmd) {
        if (stripos($command, $cmd) !== false) {
            logAction("BLOCKED_COMMAND", $command);
            return false;
        }
    }
    
    // If using allowed commands list, verify command is in the list
    if (!empty($CONFIG['allowed_commands'])) {
        $baseCmd = explode(' ', $command)[0];
        if (!in_array($baseCmd, $CONFIG['allowed_commands'])) {
            logAction("UNAUTHORIZED_COMMAND", $command);
            return false;
        }
    }
    
    return true;
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function getDirectoryContents($dir) {
    $items = [];
    if (!is_dir($dir)) return $items;
    
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        
        $fullPath = $dir . DIRECTORY_SEPARATOR . $file;
        $items[] = [
            'name' => $file,
            'path' => $fullPath,
            'is_dir' => is_dir($fullPath),
            'size' => is_dir($fullPath) ? null : filesize($fullPath),
            'perms' => substr(sprintf('%o', fileperms($fullPath)), -4),
            'modified' => filemtime($fullPath)
        ];
    }
    
    return $items;
}

// Authentication
if (!checkAuth()) {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        if ($_POST['username'] === $CONFIG['username'] && $_POST['password'] === $CONFIG['password']) {
            $_SESSION['authenticated'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['username'] = $_POST['username'];
            $_SESSION['last_activity'] = time();
            logAction("LOGIN_SUCCESS", $_POST['username']);
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            logAction("LOGIN_FAILED", $_POST['username']);
            $error = "Invalid credentials!";
        }
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Secure Admin Login</title>
        <style>
            body { 
                font-family: 'Segoe UI', Arial, sans-serif; 
                background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); 
                color: white; 
                margin: 0; 
                padding: 20px; 
                display: flex; 
                justify-content: center; 
                align-items: center; 
                min-height: 100vh; 
            }
            .login-box { 
                background: rgba(45, 45, 45, 0.9); 
                padding: 40px; 
                border-radius: 15px; 
                box-shadow: 0 10px 30px rgba(0,0,0,0.5); 
                width: 100%; 
                max-width: 400px; 
                backdrop-filter: blur(10px);
            }
            .logo { 
                text-align: center; 
                margin-bottom: 30px; 
            }
            .logo img {
                max-width: 180px;
                margin-bottom: 15px;
            }
            .logo h1 { 
                color: #00ff88; 
                margin: 10px 0; 
                font-size: 1.5rem;
            }
            .form-group { 
                margin-bottom: 20px; 
            }
            input[type="text"], input[type="password"] { 
                width: 100%; 
                padding: 12px 15px; 
                background: rgba(26, 26, 26, 0.8); 
                border: 1px solid #444; 
                color: white; 
                border-radius: 8px; 
                font-size: 1rem;
                transition: all 0.3s;
            }
            input[type="text"]:focus, input[type="password"]:focus {
                border-color: #00ff88;
                outline: none;
                box-shadow: 0 0 0 2px rgba(0, 255, 136, 0.2);
            }
            button { 
                width: 100%; 
                padding: 12px; 
                background: #00ff88; 
                color: black; 
                border: none; 
                border-radius: 8px; 
                cursor: pointer; 
                font-weight: bold; 
                font-size: 1rem;
                transition: all 0.3s;
            }
            button:hover {
                background: #00cc6a;
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 255, 136, 0.3);
            }
            .error { 
                background: rgba(255, 68, 68, 0.2); 
                color: #ff6b6b; 
                padding: 12px; 
                border-radius: 8px; 
                margin-bottom: 20px; 
                text-align: center; 
                border: 1px solid rgba(255, 68, 68, 0.3);
            }
            .footer {
                text-align: center;
                margin-top: 20px;
                font-size: 0.8rem;
                color: #888;
            }
        </style>
    </head>
    <body>
        <div class="login-box">
            <div class="logo">
                <img src="https://shuju.to/assets/logo.png" alt="Admin Portal Logo">
                <h1>ADMIN PORTAL</h1>
            </div>
            <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
            <form method="post">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required autocomplete="off">
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required autocomplete="off">
                </div>
                <button type="submit">Login</button>
            </form>
            <div class="footer">
                Secure Access Only
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Logout
if (isset($_GET['logout'])) {
    logAction("LOGOUT", $_SESSION['username']);
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Core Functions
function executeCommand($command) {
    if (!isSafeCommand($command)) {
        return "Error: Command blocked for security reasons";
    }
    
    $output = [];
    $return_var = 0;
    $descriptorspec = array(
        0 => array("pipe", "r"),  // stdin
        1 => array("pipe", "w"),  // stdout
        2 => array("pipe", "w")   // stderr
    );
    
    $process = proc_open($command, $descriptorspec, $pipes, getcwd());
    
    if (is_resource($process)) {
        fclose($pipes[0]); // Close stdin
        
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        
        $return_var = proc_close($process);
        
        if (!empty($stderr)) {
            return "Error: " . $stderr;
        }
        
        return $stdout;
    }
    
    return "Error: Failed to execute command";
}

function getSystemInfo() {
    global $CONFIG;
    
    // Memory usage
    $memory_usage = memory_get_usage(true);
    $memory_peak = memory_get_peak_usage(true);
    
    // Disk space
    $disk_free = disk_free_space("/");
    $disk_total = disk_total_space("/");
    $disk_used = $disk_total - $disk_free;
    
    // CPU info (Linux only)
    $cpu_info = 'N/A';
    if (is_readable('/proc/cpuinfo')) {
        $cpuinfo = file('/proc/cpuinfo');
        $cpucores = 0;
        foreach ($cpuinfo as $line) {
            if (preg_match('/^processor/', $line)) $cpucores++;
        }
        $cpu_info = $cpucores . ' cores';
    }
    
    // Load average (Linux only)
    $load = 'N/A';
    if (is_readable('/proc/loadavg')) {
        $load = file_get_contents('/proc/loadavg');
        $load = substr($load, 0, strpos($load, ' '));
    }
    
    return [
        'os' => php_uname(),
        'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
        'php_version' => phpversion(),
        'memory_usage' => $memory_usage,
        'memory_peak' => $memory_peak,
        'disk_free' => $disk_free,
        'disk_total' => $disk_total,
        'disk_used' => $disk_used,
        'cpu_info' => $cpu_info,
        'load_average' => $load,
        'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'N/A',
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
        'uptime' => @exec('uptime -p') ?: 'N/A'
    ];
}

// Process Actions
$output = '';
$current_dir = isset($_GET['dir']) ? realpath($_GET['dir']) : getcwd();
if ($current_dir === false || strpos($current_dir, $CONFIG['base_directory']) !== 0) {
    $current_dir = $CONFIG['base_directory'];
}

if ($_POST) {
    if (isset($_POST['command'])) {
        $command = sanitizeInput($_POST['command']);
        $output = executeCommand($command);
        logAction("COMMAND", $command);
    }
    elseif (isset($_POST['download'])) {
        $file = realpath($_POST['file_path']);
        if ($file && file_exists($file) && is_readable($file) && 
            strpos($file, $CONFIG['base_directory']) === 0 &&
            filesize($file) <= $CONFIG['max_file_download_size']) {
            
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if (in_array($extension, $CONFIG['allowed_extensions'])) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($file).'"');
                header('Content-Length: ' . filesize($file));
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                readfile($file);
                logAction("DOWNLOAD", $file);
                exit;
            } else {
                $output = "Error: File type not allowed";
            }
        } else {
            $output = "Error: File not accessible or too large";
        }
    }
    elseif (isset($_POST['delete_file'])) {
        $file = realpath($_POST['file_path']);
        if ($file && file_exists($file) && is_writable($file) && 
            strpos($file, $CONFIG['base_directory']) === 0 &&
            $file != __FILE__) {
            
            if (is_dir($file)) {
                if (rmdir($file)) {
                    $output = "Directory deleted successfully";
                    logAction("DELETE_DIR", $file);
                } else {
                    $output = "Error: Could not delete directory (may not be empty)";
                }
            } else {
                if (unlink($file)) {
                    $output = "File deleted successfully";
                    logAction("DELETE_FILE", $file);
                } else {
                    $output = "Error: Could not delete file";
                }
            }
        } else {
            $output = "Error: File not accessible or protected";
        }
    }
    elseif (isset($_POST['create_file'])) {
        $filename = sanitizeInput($_POST['filename']);
        $filepath = $current_dir . DIRECTORY_SEPARATOR . $filename;
        
        if (strpos(realpath($filepath), $CONFIG['base_directory']) === 0) {
            if (!file_exists($filepath)) {
                if (touch($filepath)) {
                    $output = "File created successfully";
                    logAction("CREATE_FILE", $filepath);
                } else {
                    $output = "Error: Could not create file";
                }
            } else {
                $output = "Error: File already exists";
            }
        } else {
            $output = "Error: Invalid file path";
        }
    }
    elseif (isset($_POST['create_dir'])) {
        $dirname = sanitizeInput($_POST['dirname']);
        $dirpath = $current_dir . DIRECTORY_SEPARATOR . $dirname;
        
        if (strpos(realpath($dirpath), $CONFIG['base_directory']) === 0) {
            if (!file_exists($dirpath)) {
                if (mkdir($dirpath, 0755)) {
                    $output = "Directory created successfully";
                    logAction("CREATE_DIR", $dirpath);
                } else {
                    $output = "Error: Could not create directory";
                }
            } else {
                $output = "Error: Directory already exists";
            }
        } else {
            $output = "Error: Invalid directory path";
        }
    }
}

if (isset($_FILES['upload_file'])) {
    $upload = $_FILES['upload_file'];
    if ($upload['error'] === UPLOAD_ERR_OK) {
        if ($upload['size'] <= $CONFIG['max_upload_size']) {
            $target = $current_dir . '/' . basename($upload['name']);
            if (move_uploaded_file($upload['tmp_name'], $target)) {
                $output = "File uploaded successfully";
                logAction("UPLOAD", $target);
            } else {
                $output = "Error: Upload failed";
            }
        } else {
            $output = "Error: File too large";
        }
    } else {
        $output = "Error: Upload error " . $upload['error'];
    }
}

$system_info = getSystemInfo();
$directory_contents = getDirectoryContents($current_dir);
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
            --primary-dark: #00cc6a;
            --dark: #0a0a0a;
            --darker: #050505;
            --light: #1a1a1a;
            --lighter: #2a2a2a;
            --text: #ffffff;
            --text-muted: #aaaaaa;
            --danger: #ff4444;
            --warning: #ffaa00;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background: var(--dark); 
            color: var(--text); 
            margin: 0; 
            padding: 0; 
            line-height: 1.6;
        }
        
        .header { 
            background: var(--darker); 
            padding: 15px 0; 
            border-bottom: 2px solid var(--primary);
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .header-content { 
            max-width: 1400px; 
            margin: 0 auto; 
            display: flex; 
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-container img {
            height: 40px;
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .container { 
            max-width: 1400px; 
            margin: 20px auto; 
            padding: 0 20px; 
        }
        
        .tabs { 
            display: flex; 
            background: var(--light); 
            border-radius: 8px 8px 0 0; 
            overflow: hidden;
        }
        
        .tab { 
            padding: 15px 25px; 
            background: none; 
            border: none; 
            color: var(--text-muted); 
            cursor: pointer; 
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .tab:hover {
            background: var(--lighter);
            color: var(--text);
        }
        
        .tab.active { 
            background: var(--lighter); 
            color: var(--primary); 
            border-bottom: 2px solid var(--primary);
        }
        
        .tab-content { 
            display: none; 
            background: var(--light); 
            padding: 25px; 
            border-radius: 0 0 8px 8px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
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
            padding: 12px 15px; 
            background: var(--darker); 
            border: 1px solid #333; 
            color: white; 
            border-radius: 6px;
            font-family: 'Consolas', monospace;
            font-size: 0.95rem;
        }
        
        .command-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(0, 255, 136, 0.2);
        }
        
        .btn { 
            background: var(--primary); 
            color: black; 
            border: none; 
            padding: 12px 20px; 
            cursor: pointer; 
            font-weight: bold; 
            border-radius: 6px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.3);
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background: #cc3333;
        }
        
        .btn-warning {
            background: var(--warning);
            color: black;
        }
        
        .output { 
            background: var(--darker); 
            padding: 15px; 
            border-radius: 6px; 
            font-family: 'Consolas', monospace; 
            white-space: pre-wrap;
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #333;
        }
        
        .stats { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 15px; 
            margin-bottom: 25px; 
        }
        
        .stat-card { 
            background: var(--light); 
            padding: 20px; 
            border-radius: 8px; 
            border-left: 4px solid var(--primary);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            margin-top: 0;
            color: var(--primary);
            font-size: 1rem;
            margin-bottom: 10px;
        }
        
        .file-manager {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .file-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .file-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--darker);
            border-radius: 6px;
            overflow: hidden;
        }
        
        .file-table th, .file-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #333;
        }
        
        .file-table th {
            background: var(--lighter);
            color: var(--primary);
            font-weight: 600;
        }
        
        .file-table tr:hover {
            background: rgba(255,255,255,0.05);
        }
        
        .file-icon {
            margin-right: 8px;
        }
        
        .file-actions-cell {
            display: flex;
            gap: 5px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: var(--light);
            padding: 25px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-close {
            background: none;
            border: none;
            color: var(--text);
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-muted);
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            background: var(--darker);
            border: 1px solid #333;
            color: white;
            border-radius: 6px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid rgba(0, 255, 136, 0.3);
            color: var(--primary);
        }
        
        .alert-error {
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid rgba(255, 68, 68, 0.3);
            color: #ff6b6b;
        }
        
        .quick-commands {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .quick-command {
            background: var(--darker);
            border: 1px solid #333;
            color: var(--text);
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
        }
        
        .quick-command:hover {
            background: var(--lighter);
            border-color: var(--primary);
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 10px;
            }
            
            .tabs {
                flex-wrap: wrap;
            }
            
            .tab {
                flex: 1;
                min-width: 120px;
                text-align: center;
            }
            
            .command-form {
                flex-direction: column;
            }
            
            .file-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo-container">
                <img src="https://shuju.to/assets/logo.png" alt="CyberAdmin Pro Logo">
                <div class="logo-text">CyberAdmin Pro</div>
            </div>
            <div class="user-info">
                Welcome, <strong><?php echo $_SESSION['username']; ?></strong> | 
                <a href="?logout=1" style="color: var(--danger); text-decoration: none;">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <h3>System Info</h3>
                <div><?php echo $system_info['os']; ?></div>
            </div>
            <div class="stat-card">
                <h3>PHP Version</h3>
                <div><?php echo $system_info['php_version']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Memory Usage</h3>
                <div><?php echo formatBytes($system_info['memory_usage']); ?> / <?php echo formatBytes($system_info['memory_peak']); ?> peak</div>
            </div>
            <div class="stat-card">
                <h3>Disk Space</h3>
                <div><?php echo formatBytes($system_info['disk_used']); ?> / <?php echo formatBytes($system_info['disk_total']); ?></div>
            </div>
            <div class="stat-card">
                <h3>Server Load</h3>
                <div><?php echo $system_info['load_average']; ?> (<?php echo $system_info['cpu_info']; ?>)</div>
            </div>
            <div class="stat-card">
                <h3>Uptime</h3>
                <div><?php echo $system_info['uptime']; ?></div>
            </div>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="switchTab('terminal')">💻 Terminal</button>
            <button class="tab" onclick="switchTab('files')">📁 File Manager</button>
            <button class="tab" onclick="switchTab('phpinfo')">🐘 PHP Info</button>
            <button class="tab" onclick="switchTab('system')">🔧 System</button>
        </div>

        <div id="terminal" class="tab-content active">
            <h2>Command Terminal</h2>
            
            <div class="quick-commands">
                <div class="quick-command" onclick="insertCommand('pwd')">pwd</div>
                <div class="quick-command" onclick="insertCommand('ls -la')">ls -la</div>
                <div class="quick-command" onclick="insertCommand('whoami')">whoami</div>
                <div class="quick-command" onclick="insertCommand('id')">id</div>
                <div class="quick-command" onclick="insertCommand('df -h')">df -h</div>
                <div class="quick-command" onclick="insertCommand('free -h')">free -h</div>
                <div class="quick-command" onclick="insertCommand('ps aux')">ps aux</div>
                <div class="quick-command" onclick="insertCommand('uname -a')">uname -a</div>
            </div>
            
            <form method="post" class="command-form">
                <input type="text" name="command" class="command-input" placeholder="Enter command..." required id="commandInput">
                <button type="submit" class="btn">Execute</button>
            </form>
            
            <?php if (!empty($output)): ?>
                <div class="output"><?php echo sanitizeInput($output); ?></div>
            <?php endif; ?>
        </div>

        <div id="files" class="tab-content">
            <h2>File Manager</h2>
            
            <div class="file-manager">
                <div class="breadcrumb">
                    <a href="?dir=<?php echo urlencode($CONFIG['base_directory']); ?>">Root</a>
                    <?php
                    $dir_parts = explode('/', str_replace($CONFIG['base_directory'], '', $current_dir));
                    $current_path = $CONFIG['base_directory'];
                    foreach ($dir_parts as $part) {
                        if (!empty($part)) {
                            $current_path .= '/' . $part;
                            echo ' / <a href="?dir=' . urlencode($current_path) . '">' . $part . '</a>';
                        }
                    }
                    ?>
                </div>
                
                <div class="file-actions">
                    <button class="btn" onclick="showModal('uploadModal')">📤 Upload File</button>
                    <button class="btn" onclick="showModal('createFileModal')">📄 Create File</button>
                    <button class="btn" onclick="showModal('createDirModal')">📁 Create Directory</button>
                </div>
                
                <?php if (!empty($output)): ?>
                    <div class="alert <?php echo strpos($output, 'Error') !== false ? 'alert-error' : 'alert-success'; ?>">
                        <?php echo sanitizeInput($output); ?>
                    </div>
                <?php endif; ?>
                
                <table class="file-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Size</th>
                            <th>Permissions</th>
                            <th>Modified</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($current_dir != $CONFIG['base_directory']): ?>
                            <tr>
                                <td>
                                    <span class="file-icon">📁</span>
                                    <a href="?dir=<?php echo urlencode(dirname($current_dir)); ?>">..</a>
                                </td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php foreach ($directory_contents as $item): ?>
                            <tr>
                                <td>
                                    <span class="file-icon"><?php echo $item['is_dir'] ? '📁' : '📄'; ?></span>
                                    <?php if ($item['is_dir']): ?>
                                        <a href="?dir=<?php echo urlencode($item['path']); ?>"><?php echo $item['name']; ?></a>
                                    <?php else: ?>
                                        <?php echo $item['name']; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $item['is_dir'] ? '-' : formatBytes($item['size']); ?></td>
                                <td><?php echo $item['perms']; ?></td>
                                <td><?php echo date('Y-m-d H:i:s', $item['modified']); ?></td>
                                <td class="file-actions-cell">
                                    <?php if (!$item['is_dir']): ?>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="file_path" value="<?php echo $item['path']; ?>">
                                            <button type="submit" name="download" class="btn">Download</button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete <?php echo $item['name']; ?>?');">
                                        <input type="hidden" name="file_path" value="<?php echo $item['path']; ?>">
                                        <button type="submit" name="delete_file" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
        
        <div id="system" class="tab-content">
            <h2>System Information</h2>
            <div class="output">
                <strong>Operating System:</strong> <?php echo $system_info['os']; ?><br>
                <strong>Server Software:</strong> <?php echo $system_info['server']; ?><br>
                <strong>PHP Version:</strong> <?php echo $system_info['php_version']; ?><br>
                <strong>Server IP:</strong> <?php echo $system_info['server_ip']; ?><br>
                <strong>Client IP:</strong> <?php echo $system_info['client_ip']; ?><br>
                <strong>Uptime:</strong> <?php echo $system_info['uptime']; ?><br>
                <strong>CPU Info:</strong> <?php echo $system_info['cpu_info']; ?><br>
                <strong>Load Average:</strong> <?php echo $system_info['load_average']; ?><br>
                <strong>Memory Usage:</strong> <?php echo formatBytes($system_info['memory_usage']); ?> (Peak: <?php echo formatBytes($system_info['memory_peak']); ?>)<br>
                <strong>Disk Usage:</strong> <?php echo formatBytes($system_info['disk_used']); ?> of <?php echo formatBytes($system_info['disk_total']); ?> (<?php echo round(($system_info['disk_used'] / $system_info['disk_total']) * 100, 2); ?>%)<br>
                <strong>Free Disk Space:</strong> <?php echo formatBytes($system_info['disk_free']); ?><br>
            </div>
        </div>
    </div>

    <!-- Upload File Modal -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Upload File</h3>
                <button class="modal-close" onclick="hideModal('uploadModal')">&times;</button>
            </div>
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="upload_file">Select File:</label>
                    <input type="file" name="upload_file" id="upload_file" class="form-control" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn">Upload</button>
                    <button type="button" class="btn btn-danger" onclick="hideModal('uploadModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create File Modal -->
    <div id="createFileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create New File</h3>
                <button class="modal-close" onclick="hideModal('createFileModal')">&times;</button>
            </div>
            <form method="post">
                <input type="hidden" name="create_file" value="1">
                <div class="form-group">
                    <label for="filename">File Name:</label>
                    <input type="text" name="filename" id="filename" class="form-control" required placeholder="example.txt">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn">Create</button>
                    <button type="button" class="btn btn-danger" onclick="hideModal('createFileModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Directory Modal -->
    <div id="createDirModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create New Directory</h3>
                <button class="modal-close" onclick="hideModal('createDirModal')">&times;</button>
            </div>
            <form method="post">
                <input type="hidden" name="create_dir" value="1">
                <div class="form-group">
                    <label for="dirname">Directory Name:</label>
                    <input type="text" name="dirname" id="dirname" class="form-control" required placeholder="new_directory">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn">Create</button>
                    <button type="button" class="btn btn-danger" onclick="hideModal('createDirModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
        
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }
        
        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function insertCommand(cmd) {
            document.getElementById('commandInput').value = cmd;
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
<?php
ob_end_flush();
?>
