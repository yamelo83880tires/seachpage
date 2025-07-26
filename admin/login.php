<?php
/**
 * Admin Login Page
 * Handles user authentication with secure sessions
 */

// Include config file
require_once __DIR__ . '/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

// Check for logout message
if (isset($_SESSION['logout_message'])) {
    $success = $_SESSION['logout_message'];
    unset($_SESSION['logout_message']);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Verify CSRF token
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid security token. Please try again.';
    } else if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // Check credentials (in production, use database)
        if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            $_SESSION['login_time'] = time();
            
            // Update last login in database (if using database users)
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE username = ?");
                $stmt->execute([$username]);
            } catch (Exception $e) {
                // Continue even if update fails
            }
            
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password.';
            // Add small delay to prevent brute force attacks
            sleep(1);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Movie Search Panel</title>
    <link rel="stylesheet" href="style.css">
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>🎬 Admin Panel</h1>
                <p>Movie Search Management System</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-control" 
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        required
                        autocomplete="username"
                        placeholder="Enter your username"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    >
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                    🔐 Login to Admin Panel
                </button>
            </form>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center;">
                <p style="font-size: 12px; color: #666;">
                    Default Login: <strong>admin</strong> / <strong>admin123</strong><br>
                    <em>Change these credentials in config.php</em>
                </p>
                <a href="../index.html" style="color: #667eea; text-decoration: none; font-size: 14px;">
                    ← Back to Main Site
                </a>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>&copy; 2025 Movie Search Admin Panel | Secure Login System</p>
    </div>
    
    <script src="scripts.js"></script>
    <script>
        // Focus on username field when page loads
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
        
        // Add loading state to form
        document.getElementById('loginForm').addEventListener('submit', function() {
            const button = this.querySelector('button[type="submit"]');
            button.innerHTML = '🔄 Logging in...';
            button.disabled = true;
        });
    </script>
</body>
</html>