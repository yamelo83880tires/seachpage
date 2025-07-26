<?php
/**
 * Add Keyword Page
 * Allows admin to add new keywords to the database
 */

require_once __DIR__ . '/config.php';

// Ensure user is logged in
requireLogin();

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keyword = sanitizeInput($_POST['keyword'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $status = sanitizeInput($_POST['status'] ?? 'active');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Verify CSRF token
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid security token. Please try again.';
    } else if (empty($keyword)) {
        $error = 'Keyword is required.';
    } else if (strlen($keyword) < 2) {
        $error = 'Keyword must be at least 2 characters long.';
    } else if (strlen($keyword) > 255) {
        $error = 'Keyword must be less than 255 characters.';
    } else {
        try {
            $pdo = getDBConnection();
            
            // Check if keyword already exists
            $stmt = $pdo->prepare("SELECT id FROM keywords WHERE keyword = ?");
            $stmt->execute([$keyword]);
            
            if ($stmt->fetch()) {
                $error = 'This keyword already exists in the database.';
            } else {
                // Insert new keyword
                $stmt = $pdo->prepare("INSERT INTO keywords (keyword, description, status) VALUES (?, ?, ?)");
                
                if ($stmt->execute([$keyword, $description, $status])) {
                    $success = 'Keyword "' . htmlspecialchars($keyword) . '" has been added successfully!';
                    // Clear form data
                    $_POST = [];
                } else {
                    $error = 'Failed to add keyword. Please try again.';
                }
            }
            
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Keyword - Movie Search Panel</title>
    <link rel="stylesheet" href="style.css">
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="dashboard-header">
            <h1>➕ Add New Keyword</h1>
            <p>Add a new movie search keyword to your collection</p>
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
        
        <!-- Add Keyword Form -->
        <div class="content-card">
            <h2>📝 Keyword Information</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                    <div style="margin-top: 15px;">
                        <a href="add_keyword.php" class="btn btn-success">Add Another Keyword</a>
                        <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="keywordForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="keyword">Keyword *</label>
                    <input 
                        type="text" 
                        id="keyword" 
                        name="keyword" 
                        class="form-control" 
                        value="<?php echo htmlspecialchars($_POST['keyword'] ?? ''); ?>"
                        required
                        placeholder="e.g., bollywood movies, web series"
                        maxlength="255"
                    >
                    <small style="color: #666; font-size: 12px;">This will be the search term displayed on your site</small>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        class="form-control" 
                        rows="4"
                        placeholder="Optional description for this keyword..."
                    ><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    <small style="color: #666; font-size: 12px;">Internal description for your reference (not shown on site)</small>
                </div>
                
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active" <?php echo (($_POST['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>
                            ✅ Active (visible on site)
                        </option>
                        <option value="inactive" <?php echo (($_POST['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>
                            ⏸️ Inactive (hidden from site)
                        </option>
                    </select>
                    <small style="color: #666; font-size: 12px;">Only active keywords will be displayed on your website</small>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-success">
                        ➕ Add Keyword
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        ❌ Cancel
                    </a>
                    <button type="reset" class="btn btn-warning">
                        🔄 Reset Form
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Tips and Guidelines -->
        <div class="content-card">
            <h2>💡 Tips & Guidelines</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div>
                    <h4 style="color: #667eea; margin-bottom: 10px;">🎯 Good Keywords</h4>
                    <ul style="color: #666; font-size: 14px; line-height: 1.6;">
                        <li>Movie titles or series names</li>
                        <li>Genre-specific terms</li>
                        <li>Actor or director names</li>
                        <li>Popular search phrases</li>
                    </ul>
                </div>
                <div>
                    <h4 style="color: #667eea; margin-bottom: 10px;">📝 Best Practices</h4>
                    <ul style="color: #666; font-size: 14px; line-height: 1.6;">
                        <li>Use lowercase for consistency</li>
                        <li>Keep keywords concise</li>
                        <li>Test keywords before making them active</li>
                        <li>Add descriptions for team reference</li>
                    </ul>
                </div>
                <div>
                    <h4 style="color: #667eea; margin-bottom: 10px;">⚠️ Avoid</h4>
                    <ul style="color: #666; font-size: 14px; line-height: 1.6;">
                        <li>Duplicate keywords</li>
                        <li>Too generic terms</li>
                        <li>Special characters</li>
                        <li>Very long phrases</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Recent Keywords -->
        <div class="content-card">
            <h2>📚 Recent Keywords (for reference)</h2>
            <?php
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->query("SELECT keyword, status, created_at FROM keywords ORDER BY created_at DESC LIMIT 5");
                $recent_keywords = $stmt->fetchAll();
                
                if (empty($recent_keywords)): ?>
                    <div class="alert alert-info">
                        No keywords found. This will be your first keyword!
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Keyword</th>
                                    <th>Status</th>
                                    <th>Added</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_keywords as $kw): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($kw['keyword']); ?></strong></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $kw['status']; ?>">
                                            <?php echo ucfirst($kw['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($kw['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif;
                
            } catch (Exception $e): ?>
                <div class="alert alert-warning">
                    Could not load recent keywords: <?php echo htmlspecialchars($e->getMessage()); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="footer">
        <p>&copy; 2025 Movie Search Admin Panel | Add New Keywords</p>
    </div>
    
    <script src="scripts.js"></script>
    <script>
        // Auto-save form data
        document.addEventListener('DOMContentLoaded', function() {
            autoSaveForm('keywordForm');
            
            // Focus on keyword field
            document.getElementById('keyword').focus();
            
            // Character counter for keyword
            const keywordInput = document.getElementById('keyword');
            const keywordGroup = keywordInput.parentNode;
            
            // Create character counter
            const counter = document.createElement('small');
            counter.style.cssText = 'color: #666; font-size: 12px; float: right;';
            keywordGroup.appendChild(counter);
            
            function updateCounter() {
                const length = keywordInput.value.length;
                const maxLength = 255;
                counter.textContent = `${length}/${maxLength} characters`;
                
                if (length > maxLength * 0.9) {
                    counter.style.color = '#e74c3c';
                } else if (length > maxLength * 0.7) {
                    counter.style.color = '#f39c12';
                } else {
                    counter.style.color = '#666';
                }
            }
            
            keywordInput.addEventListener('input', updateCounter);
            updateCounter();
        });
        
        // Form submission with loading state
        document.getElementById('keywordForm').addEventListener('submit', function() {
            const button = this.querySelector('button[type="submit"]');
            button.innerHTML = '⏳ Adding Keyword...';
            button.disabled = true;
        });
        
        // Preview keyword as it's typed
        document.getElementById('keyword').addEventListener('input', function() {
            const value = this.value.trim();
            const preview = document.getElementById('keyword-preview');
            
            if (!preview && value) {
                const previewDiv = document.createElement('div');
                previewDiv.id = 'keyword-preview';
                previewDiv.style.cssText = `
                    margin-top: 10px;
                    padding: 10px;
                    background: #f8f9fa;
                    border: 2px solid #667eea;
                    border-radius: 8px;
                    font-weight: bold;
                    color: #667eea;
                    text-align: center;
                `;
                this.parentNode.appendChild(previewDiv);
            }
            
            if (value) {
                document.getElementById('keyword-preview').textContent = `Preview: "${value}"`;
            } else if (preview) {
                preview.remove();
            }
        });
    </script>
</body>
</html>