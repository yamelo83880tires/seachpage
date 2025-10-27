<?php
/**
 * Test File - Debug Configuration
 * Use this to test if your setup is working correctly
 */

echo "<h2>🔧 Admin Panel Test</h2>";

// Test 1: Check PHP version
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";

// Test 2: Check file paths
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Config File Exists:</strong> " . (file_exists(__DIR__ . '/config.php') ? '✅ Yes' : '❌ No') . "</p>";

// Test 3: Try to include config
try {
    require_once __DIR__ . '/config.php';
    echo "<p><strong>Config Loaded:</strong> ✅ Success</p>";
    
    // Test 4: Check functions
    if (function_exists('getDBConnection')) {
        echo "<p><strong>Functions Available:</strong> ✅ Yes</p>";
        
        // Test 5: Try database connection
        try {
            $pdo = getDBConnection();
            echo "<p><strong>Database Connection:</strong> ✅ Success</p>";
            
            // Test 6: Check tables
            $stmt = $pdo->query("SHOW TABLES LIKE 'keywords'");
            if ($stmt->fetch()) {
                echo "<p><strong>Keywords Table:</strong> ✅ Exists</p>";
                
                // Test 7: Count keywords
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM keywords");
                $result = $stmt->fetch();
                echo "<p><strong>Total Keywords:</strong> " . $result['count'] . "</p>";
            } else {
                echo "<p><strong>Keywords Table:</strong> ❌ Not found</p>";
                echo "<p><em>Run install.php to create database tables</em></p>";
            }
            
        } catch (Exception $e) {
            echo "<p><strong>Database Connection:</strong> ❌ Failed</p>";
            echo "<p><em>Error: " . htmlspecialchars($e->getMessage()) . "</em></p>";
            echo "<p><em>Check your database settings in config.php</em></p>";
        }
        
    } else {
        echo "<p><strong>Functions Available:</strong> ❌ No</p>";
    }
    
} catch (Exception $e) {
    echo "<p><strong>Config Loaded:</strong> ❌ Failed</p>";
    echo "<p><em>Error: " . htmlspecialchars($e->getMessage()) . "</em></p>";
}

// Test 8: Check session
echo "<p><strong>Session Status:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? '✅ Active' : '⏸️ Inactive') . "</p>";

// Test 9: File permissions test
echo "<p><strong>Write Permissions:</strong> " . (is_writable(__DIR__) ? '✅ OK' : '❌ No write access') . "</p>";

echo "<hr>";
echo "<h3>📁 File Structure Check:</h3>";
$files = ['config.php', 'login.php', 'dashboard.php', 'add_keyword.php', 'edit_keyword.php', 'delete_keyword.php', 'logout.php', 'style.css', 'scripts.js', 'keywords.sql'];
foreach ($files as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    echo "<p>📄 {$file}: " . ($exists ? '✅' : '❌') . "</p>";
}

echo "<hr>";
echo "<h3>🔗 Quick Links:</h3>";
echo "<p><a href='login.php'>🔐 Admin Login</a></p>";
echo "<p><a href='../index.php'>🌐 Main Site</a></p>";
echo "<p><a href='../install.php'>⚙️ Installation</a></p>";

echo "<hr>";
echo "<p><small>Delete this test.php file when everything is working!</small></p>";
?>