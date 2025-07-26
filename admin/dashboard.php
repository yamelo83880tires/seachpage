<?php
/**
 * Admin Dashboard
 * Main control panel with statistics and quick access to all features
 */

require_once 'config.php';

// Ensure user is logged in
requireLogin();

// Handle AJAX requests for real-time stats
if (isset($_GET['ajax']) && $_GET['ajax'] === 'stats') {
    header('Content-Type: application/json');
    
    try {
        $pdo = getDBConnection();
        
        // Get statistics
        $stats = [];
        
        // Total keywords
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM keywords");
        $stats['total_keywords'] = $stmt->fetchColumn();
        
        // Active keywords
        $stmt = $pdo->query("SELECT COUNT(*) as active FROM keywords WHERE status = 'active'");
        $stats['active_keywords'] = $stmt->fetchColumn();
        
        // Inactive keywords
        $stmt = $pdo->query("SELECT COUNT(*) as inactive FROM keywords WHERE status = 'inactive'");
        $stats['inactive_keywords'] = $stmt->fetchColumn();
        
        echo json_encode(['success' => true, 'stats' => $stats]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit();
}

// Get dashboard statistics
$stats = [];
try {
    $pdo = getDBConnection();
    
    // Total keywords
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM keywords");
    $stats['total_keywords'] = $stmt->fetchColumn();
    
    // Active keywords
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM keywords WHERE status = 'active'");
    $stats['active_keywords'] = $stmt->fetchColumn();
    
    // Inactive keywords
    $stmt = $pdo->query("SELECT COUNT(*) as inactive FROM keywords WHERE status = 'inactive'");
    $stats['inactive_keywords'] = $stmt->fetchColumn();
    
    // Recent keywords (last 5)
    $stmt = $pdo->query("SELECT keyword, status, created_at FROM keywords ORDER BY created_at DESC LIMIT 5");
    $recent_keywords = $stmt->fetchAll();
    
} catch (Exception $e) {
    $stats = ['total_keywords' => 0, 'active_keywords' => 0, 'inactive_keywords' => 0];
    $recent_keywords = [];
    $db_error = "Database connection error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Movie Search Panel</title>
    <link rel="stylesheet" href="style.css">
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
    <div class="container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1>🎬 Admin Dashboard</h1>
            <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>! 
            Manage your movie search keywords and monitor system performance.</p>
        </div>
        
        <!-- Navigation Menu -->
        <nav class="nav-menu">
            <ul>
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="add_keyword.php">➕ Add Keyword</a></li>
                <li><a href="dashboard.php#keywords-table">📝 Manage Keywords</a></li>
                <li><a href="../index.html" target="_blank">🔗 View Site</a></li>
                <li><a href="logout.php" class="logout">🚪 Logout</a></li>
            </ul>
        </nav>
        
        <?php if (isset($db_error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($db_error); ?>
                <br><small>Please check your database configuration in config.php and ensure the database is created.</small>
            </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card" id="total-keywords" data-refresh="true">
                <div class="stat-number"><?php echo $stats['total_keywords']; ?></div>
                <div class="stat-label">Total Keywords</div>
            </div>
            
            <div class="stat-card" id="active-keywords" data-refresh="true">
                <div class="stat-number"><?php echo $stats['active_keywords']; ?></div>
                <div class="stat-label">Active Keywords</div>
            </div>
            
            <div class="stat-card" id="inactive-keywords" data-refresh="true">
                <div class="stat-number"><?php echo $stats['inactive_keywords']; ?></div>
                <div class="stat-label">Inactive Keywords</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo date('H:i'); ?></div>
                <div class="stat-label">Current Time</div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="content-card">
            <h2>⚡ Quick Actions</h2>
            <div class="action-buttons">
                <a href="add_keyword.php" class="btn btn-success">
                    ➕ Add New Keyword
                </a>
                <a href="#keywords-table" class="btn btn-primary">
                    📝 View All Keywords
                </a>
                <a href="../index.html" target="_blank" class="btn btn-secondary">
                    🔗 Preview Site
                </a>
                <button onclick="location.reload()" class="btn btn-warning">
                    🔄 Refresh Data
                </button>
            </div>
        </div>
        
        <!-- Recent Keywords -->
        <?php if (!empty($recent_keywords)): ?>
        <div class="content-card">
            <h2>📚 Recent Keywords</h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Keyword</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_keywords as $keyword): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($keyword['keyword']); ?></strong></td>
                            <td>
                                <span class="status-badge status-<?php echo $keyword['status']; ?>">
                                    <?php echo ucfirst($keyword['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y H:i', strtotime($keyword['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit_keyword.php?id=<?php echo $keyword['id'] ?? ''; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <button onclick="copyToClipboard('<?php echo htmlspecialchars($keyword['keyword']); ?>')" class="btn btn-secondary btn-sm">Copy</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- All Keywords Table -->
        <div class="content-card" id="keywords-table">
            <h2>📋 All Keywords Management</h2>
            
            <?php if (!isset($db_error)): ?>
                <div style="margin-bottom: 20px;">
                    <input type="text" id="search-keywords" class="form-control" placeholder="🔍 Search keywords..." style="max-width: 300px; display: inline-block;">
                    <a href="add_keyword.php" class="btn btn-success" style="margin-left: 10px;">➕ Add New</a>
                </div>
                
                <?php
                try {
                    // Get all keywords
                    $stmt = $pdo->query("SELECT * FROM keywords ORDER BY created_at DESC");
                    $all_keywords = $stmt->fetchAll();
                    
                    if (empty($all_keywords)): ?>
                        <div class="alert alert-info">
                            No keywords found. <a href="add_keyword.php">Add your first keyword</a> to get started!
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table" id="keywords-main-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Keyword</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_keywords as $keyword): ?>
                                    <tr>
                                        <td>#<?php echo $keyword['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($keyword['keyword']); ?></strong></td>
                                        <td><?php echo htmlspecialchars(substr($keyword['description'] ?? '', 0, 50)); ?><?php echo strlen($keyword['description'] ?? '') > 50 ? '...' : ''; ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $keyword['status']; ?>">
                                                <?php echo ucfirst($keyword['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($keyword['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="edit_keyword.php?id=<?php echo $keyword['id']; ?>" class="btn btn-warning btn-sm" data-tooltip="Edit keyword">✏️</a>
                                                <a href="delete_keyword.php?id=<?php echo $keyword['id']; ?>" 
                                                   class="btn btn-danger btn-sm confirm-delete" 
                                                   data-message="Are you sure you want to delete '<?php echo htmlspecialchars($keyword['keyword']); ?>'?"
                                                   data-tooltip="Delete keyword">🗑️</a>
                                                <button onclick="copyToClipboard('<?php echo htmlspecialchars($keyword['keyword']); ?>')" 
                                                        class="btn btn-secondary btn-sm" 
                                                        data-tooltip="Copy to clipboard">📋</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif;
                    
                } catch (Exception $e): ?>
                    <div class="alert alert-error">
                        Error loading keywords: <?php echo htmlspecialchars($e->getMessage()); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- System Information -->
        <div class="content-card">
            <h2>🔧 System Information</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <strong>PHP Version:</strong><br>
                    <span style="color: #667eea;"><?php echo PHP_VERSION; ?></span>
                </div>
                <div>
                    <strong>Session ID:</strong><br>
                    <span style="color: #667eea; font-family: monospace;"><?php echo substr(session_id(), 0, 8); ?>...</span>
                </div>
                <div>
                    <strong>Login Time:</strong><br>
                    <span style="color: #667eea;"><?php echo date('Y-m-d H:i:s', $_SESSION['login_time']); ?></span>
                </div>
                <div>
                    <strong>Database:</strong><br>
                    <span style="color: <?php echo isset($db_error) ? '#e74c3c' : '#27ae60'; ?>;">
                        <?php echo isset($db_error) ? 'Disconnected' : 'Connected'; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>&copy; 2025 Movie Search Admin Panel | Last updated: <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
    
    <script src="scripts.js"></script>
    <script>
        // Initialize search functionality
        document.addEventListener('DOMContentLoaded', function() {
            filterTable('search-keywords', 'keywords-main-table');
        });
        
        // Auto-refresh stats every 30 seconds
        setInterval(function() {
            fetch('dashboard.php?ajax=stats')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('#total-keywords .stat-number').textContent = data.stats.total_keywords;
                        document.querySelector('#active-keywords .stat-number').textContent = data.stats.active_keywords;
                        document.querySelector('#inactive-keywords .stat-number').textContent = data.stats.inactive_keywords;
                    }
                })
                .catch(error => console.log('Stats refresh failed:', error));
        }, 30000);
    </script>
</body>
</html>