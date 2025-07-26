<?php
/**
 * Edit Keyword Page
 * Allows admin to edit existing keywords
 */

require_once 'config.php';

// Ensure user is logged in
requireLogin();

$error = '';
$success = '';
$keyword_data = null;

// Get keyword ID from URL
$keyword_id = intval($_GET['id'] ?? 0);

if ($keyword_id <= 0) {
    header('Location: dashboard.php');
    exit();
}

// Get keyword data
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM keywords WHERE id = ?");
    $stmt->execute([$keyword_id]);
    $keyword_data = $stmt->fetch();
    
    if (!$keyword_data) {
        $error = 'Keyword not found.';
    }
} catch (Exception $e) {
    $error = 'Database error: ' . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $keyword_data) {
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
            // Check if keyword already exists (excluding current record)
            $stmt = $pdo->prepare("SELECT id FROM keywords WHERE keyword = ? AND id != ?");
            $stmt->execute([$keyword, $keyword_id]);
            
            if ($stmt->fetch()) {
                $error = 'This keyword already exists in the database.';
            } else {
                // Update keyword
                $stmt = $pdo->prepare("UPDATE keywords SET keyword = ?, description = ?, status = ?, updated_at = NOW() WHERE id = ?");
                
                if ($stmt->execute([$keyword, $description, $status, $keyword_id])) {
                    $success = 'Keyword "' . htmlspecialchars($keyword) . '" has been updated successfully!';
                    // Refresh keyword data
                    $stmt = $pdo->prepare("SELECT * FROM keywords WHERE id = ?");
                    $stmt->execute([$keyword_id]);
                    $keyword_data = $stmt->fetch();
                } else {
                    $error = 'Failed to update keyword. Please try again.';
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
    <title>Edit Keyword - Movie Search Panel</title>
    <link rel="stylesheet" href="style.css">
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="dashboard-header">
            <h1>✏️ Edit Keyword</h1>
            <p>Update keyword information and settings</p>
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
        
        <?php if ($error && !$keyword_data): ?>
            <div class="content-card">
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <div style="margin-top: 20px;">
                    <a href="dashboard.php" class="btn btn-primary">← Back to Dashboard</a>
                </div>
            </div>
        <?php elseif ($keyword_data): ?>
            
            <!-- Edit Keyword Form -->
            <div class="content-card">
                <h2>📝 Edit Keyword #<?php echo $keyword_data['id']; ?></h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <div style="margin-top: 15px;">
                            <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                            <a href="add_keyword.php" class="btn btn-success">Add New Keyword</a>
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
                            value="<?php echo htmlspecialchars($_POST['keyword'] ?? $keyword_data['keyword']); ?>"
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
                        ><?php echo htmlspecialchars($_POST['description'] ?? $keyword_data['description']); ?></textarea>
                        <small style="color: #666; font-size: 12px;">Internal description for your reference (not shown on site)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" class="form-control" required>
                            <?php 
                            $current_status = $_POST['status'] ?? $keyword_data['status'];
                            ?>
                            <option value="active" <?php echo ($current_status === 'active') ? 'selected' : ''; ?>>
                                ✅ Active (visible on site)
                            </option>
                            <option value="inactive" <?php echo ($current_status === 'inactive') ? 'selected' : ''; ?>>
                                ⏸️ Inactive (hidden from site)
                            </option>
                        </select>
                        <small style="color: #666; font-size: 12px;">Only active keywords will be displayed on your website</small>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="submit" class="btn btn-success">
                            💾 Update Keyword
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary">
                            ❌ Cancel
                        </a>
                        <a href="delete_keyword.php?id=<?php echo $keyword_id; ?>" 
                           class="btn btn-danger confirm-delete" 
                           data-message="Are you sure you want to delete this keyword permanently?">
                            🗑️ Delete
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Keyword Information -->
            <div class="content-card">
                <h2>📊 Keyword Information</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div>
                        <strong>Keyword ID:</strong><br>
                        <span style="color: #667eea;">#<?php echo $keyword_data['id']; ?></span>
                    </div>
                    <div>
                        <strong>Current Status:</strong><br>
                        <span class="status-badge status-<?php echo $keyword_data['status']; ?>">
                            <?php echo ucfirst($keyword_data['status']); ?>
                        </span>
                    </div>
                    <div>
                        <strong>Created:</strong><br>
                        <span style="color: #667eea;"><?php echo date('M j, Y H:i', strtotime($keyword_data['created_at'])); ?></span>
                    </div>
                    <div>
                        <strong>Last Updated:</strong><br>
                        <span style="color: #667eea;"><?php echo date('M j, Y H:i', strtotime($keyword_data['updated_at'])); ?></span>
                    </div>
                </div>
                
                <div style="margin-top: 20px;">
                    <h4>Current Keyword Preview:</h4>
                    <div style="padding: 15px; background: #f8f9fa; border: 2px solid #667eea; border-radius: 8px; text-align: center; font-weight: bold; color: #667eea; font-size: 18px;">
                        "<?php echo htmlspecialchars($keyword_data['keyword']); ?>"
                    </div>
                </div>
            </div>
            
            <!-- Similar Keywords -->
            <div class="content-card">
                <h2>🔍 Related Keywords</h2>
                <?php
                try {
                    // Get similar keywords (same first word or containing similar terms)
                    $first_word = explode(' ', $keyword_data['keyword'])[0];
                    $stmt = $pdo->prepare("SELECT * FROM keywords WHERE (keyword LIKE ? OR keyword LIKE ?) AND id != ? ORDER BY created_at DESC LIMIT 5");
                    $stmt->execute(["%$first_word%", "%". $keyword_data['keyword'] . "%", $keyword_id]);
                    $similar_keywords = $stmt->fetchAll();
                    
                    if (empty($similar_keywords)): ?>
                        <div class="alert alert-info">
                            No related keywords found.
                        </div>
                    <?php else: ?>
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
                                    <?php foreach ($similar_keywords as $kw): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($kw['keyword']); ?></strong></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $kw['status']; ?>">
                                                <?php echo ucfirst($kw['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($kw['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="edit_keyword.php?id=<?php echo $kw['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                                <button onclick="copyToClipboard('<?php echo htmlspecialchars($kw['keyword']); ?>')" class="btn btn-secondary btn-sm">Copy</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif;
                    
                } catch (Exception $e): ?>
                    <div class="alert alert-warning">
                        Could not load related keywords: <?php echo htmlspecialchars($e->getMessage()); ?>
                    </div>
                <?php endif; ?>
            </div>
            
        <?php endif; ?>
    </div>
    
    <div class="footer">
        <p>&copy; 2025 Movie Search Admin Panel | Edit Keywords</p>
    </div>
    
    <script src="scripts.js"></script>
    <script>
        // Auto-save form data
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('keywordForm');
            if (form) {
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
                
                // Form submission with loading state
                form.addEventListener('submit', function() {
                    const button = this.querySelector('button[type="submit"]');
                    button.innerHTML = '⏳ Updating Keyword...';
                    button.disabled = true;
                });
            }
        });
        
        // Show changes indicator
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('keywordForm');
            if (!form) return;
            
            const originalValues = {};
            const inputs = form.querySelectorAll('input, textarea, select');
            
            // Store original values
            inputs.forEach(input => {
                originalValues[input.name] = input.value;
            });
            
            // Check for changes
            function checkChanges() {
                let hasChanges = false;
                inputs.forEach(input => {
                    if (originalValues[input.name] !== input.value) {
                        hasChanges = true;
                    }
                });
                
                const submitButton = form.querySelector('button[type="submit"]');
                if (hasChanges) {
                    submitButton.innerHTML = '💾 Save Changes';
                    submitButton.style.background = '#f39c12';
                } else {
                    submitButton.innerHTML = '💾 Update Keyword';
                    submitButton.style.background = '#27ae60';
                }
            }
            
            inputs.forEach(input => {
                input.addEventListener('input', checkChanges);
                input.addEventListener('change', checkChanges);
            });
        });
    </script>
</body>
</html>