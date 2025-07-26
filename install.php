<?php
/**
 * Installation Script
 * Helps users set up the admin panel system
 */

// Check if already installed
if (file_exists('admin/config.php') && strpos(file_get_contents('admin/config.php'), 'localhost') !== false) {
    $config_content = file_get_contents('admin/config.php');
    if (strpos($config_content, "define('DB_USER', 'root')") !== false && 
        strpos($config_content, "define('DB_PASS', '')") !== false) {
        // Default config detected, continue with installation
    } else {
        die('<h2>🎉 Admin Panel Already Installed!</h2><p>Visit <a href="admin/login.php">Admin Panel</a> to get started.</p>');
    }
}

$step = $_GET['step'] ?? 1;
$errors = [];
$success = [];

// Step 2: Database Configuration
if ($step == 2 && $_POST) {
    $db_host = trim($_POST['db_host'] ?? 'localhost');
    $db_name = trim($_POST['db_name'] ?? 'admin_panel');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = trim($_POST['db_pass'] ?? '');
    $admin_user = trim($_POST['admin_user'] ?? 'admin');
    $admin_pass = trim($_POST['admin_pass'] ?? '');
    
    // Validate inputs
    if (empty($db_user)) $errors[] = 'Database username is required';
    if (empty($admin_user)) $errors[] = 'Admin username is required';
    if (empty($admin_pass)) $errors[] = 'Admin password is required';
    if (strlen($admin_pass) < 6) $errors[] = 'Admin password must be at least 6 characters';
    
    if (empty($errors)) {
        try {
            // Test database connection
            $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database if not exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$db_name`");
            
            // Create tables
            $sql = file_get_contents('admin/keywords.sql');
            $statements = explode(';', $sql);
            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if (!empty($stmt)) {
                    $pdo->exec($stmt);
                }
            }
            
            // Update config file
            $config_template = file_get_contents('admin/config.php');
            $config_content = str_replace(
                ["define('DB_HOST', 'localhost');", "define('DB_NAME', 'admin_panel');", "define('DB_USER', 'root');", "define('DB_PASS', '');", "define('ADMIN_USERNAME', 'admin');", "define('ADMIN_PASSWORD_HASH', password_hash('admin123', PASSWORD_DEFAULT));"],
                ["define('DB_HOST', '$db_host');", "define('DB_NAME', '$db_name');", "define('DB_USER', '$db_user');", "define('DB_PASS', '$db_pass');", "define('ADMIN_USERNAME', '$admin_user');", "define('ADMIN_PASSWORD_HASH', '" . password_hash($admin_pass, PASSWORD_DEFAULT) . "');"],
                $config_template
            );
            
            file_put_contents('admin/config.php', $config_content);
            
            $success[] = 'Database and admin panel configured successfully!';
            $step = 3;
            
        } catch (Exception $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎬 Admin Panel Installation</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .steps {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .step {
            padding: 10px 20px;
            margin: 0 5px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        .step.active {
            background: #667eea;
            color: white;
        }
        .step.inactive {
            background: #f8f9fa;
            color: #666;
        }
        .step.completed {
            background: #27ae60;
            color: white;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 5px;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5a6fd8;
        }
        .btn-success {
            background: #27ae60;
            color: white;
        }
        .btn-success:hover {
            background: #229954;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-info {
            background: #cce7f0;
            color: #055160;
            border: 1px solid #b6d4fe;
        }
        .requirements {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .requirements ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        @media (max-width: 600px) {
            .grid {
                grid-template-columns: 1fr;
            }
            .container {
                margin: 10px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎬 Movie Search Admin Panel</h1>
            <p>Installation Wizard</p>
        </div>
        
        <div class="steps">
            <div class="step <?php echo $step == 1 ? 'active' : ($step > 1 ? 'completed' : 'inactive'); ?>">1. Welcome</div>
            <div class="step <?php echo $step == 2 ? 'active' : ($step > 2 ? 'completed' : 'inactive'); ?>">2. Configure</div>
            <div class="step <?php echo $step == 3 ? 'active' : 'inactive'; ?>">3. Complete</div>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success[0]); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($step == 1): ?>
            <h2>📋 Welcome to Installation</h2>
            <div class="requirements">
                <h3>System Requirements:</h3>
                <ul>
                    <li>✅ PHP 7.4+ with PDO MySQL extension</li>
                    <li>✅ MySQL 5.7+ or MariaDB</li>
                    <li>✅ Web server (Apache/Nginx)</li>
                    <li>✅ Write permissions on files</li>
                </ul>
            </div>
            
            <div class="alert alert-info">
                <strong>What will be installed:</strong><br>
                • Database tables for keyword management<br>
                • Admin user account with secure login<br>
                • Complete admin panel interface<br>
                • Sample keywords to get you started
            </div>
            
            <a href="?step=2" class="btn btn-primary">🚀 Start Installation</a>
            
        <?php elseif ($step == 2): ?>
            <h2>⚙️ Database & Admin Configuration</h2>
            
            <form method="POST" action="?step=2">
                <h3>Database Settings:</h3>
                <div class="grid">
                    <div class="form-group">
                        <label for="db_host">Database Host</label>
                        <input type="text" id="db_host" name="db_host" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['db_host'] ?? 'localhost'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="db_name">Database Name</label>
                        <input type="text" id="db_name" name="db_name" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['db_name'] ?? 'admin_panel'); ?>" required>
                    </div>
                </div>
                
                <div class="grid">
                    <div class="form-group">
                        <label for="db_user">Database Username</label>
                        <input type="text" id="db_user" name="db_user" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['db_user'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="db_pass">Database Password</label>
                        <input type="password" id="db_pass" name="db_pass" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['db_pass'] ?? ''); ?>">
                    </div>
                </div>
                
                <h3>Admin Account:</h3>
                <div class="grid">
                    <div class="form-group">
                        <label for="admin_user">Admin Username</label>
                        <input type="text" id="admin_user" name="admin_user" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['admin_user'] ?? 'admin'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_pass">Admin Password</label>
                        <input type="password" id="admin_pass" name="admin_pass" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['admin_pass'] ?? ''); ?>" required minlength="6">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-success">💾 Install Admin Panel</button>
                <a href="?step=1" class="btn btn-primary">← Back</a>
            </form>
            
        <?php elseif ($step == 3): ?>
            <h2>🎉 Installation Complete!</h2>
            
            <div class="alert alert-success">
                <strong>Congratulations!</strong> Your admin panel has been installed successfully.
            </div>
            
            <div class="requirements">
                <h3>What's Next:</h3>
                <ul>
                    <li>✅ Database tables created</li>
                    <li>✅ Admin account configured</li>
                    <li>✅ Sample keywords added</li>
                    <li>✅ Security features enabled</li>
                </ul>
            </div>
            
            <div class="alert alert-info">
                <strong>Important Security Notes:</strong><br>
                • Delete this install.php file for security<br>
                • Your admin credentials are now secured<br>
                • Regular backups are recommended<br>
                • Monitor your admin panel regularly
            </div>
            
            <a href="admin/login.php" class="btn btn-success">🔐 Access Admin Panel</a>
            <a href="index.php" class="btn btn-primary">🌐 View Website</a>
            
            <script>
                // Auto-delete install file after 30 seconds
                setTimeout(function() {
                    if (confirm('Delete install.php file now for security?')) {
                        fetch('install.php?delete=1')
                            .then(() => alert('Install file deleted successfully!'))
                            .catch(() => alert('Please manually delete install.php'));
                    }
                }, 30000);
            </script>
            
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// Auto-delete this file if requested
if (isset($_GET['delete']) && $_GET['delete'] == '1') {
    unlink(__FILE__);
    exit('Install file deleted');
}
?>