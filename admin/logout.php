<?php
/**
 * Logout Page
 * Handles secure logout and session cleanup
 */

require_once __DIR__ . '/config.php';

// Check if user is logged in
$was_logged_in = isLoggedIn();

if ($was_logged_in) {
    // Clear all session data
    $_SESSION = [];
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
    
    // Start new session for flash message
    session_start();
    $_SESSION['logout_message'] = 'You have been logged out successfully.';
}

// Redirect to login page
header('Location: login.php');
exit();
?>