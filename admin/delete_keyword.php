<?php
/**
 * Delete Keyword Page
 * Handles secure deletion of keywords with confirmation
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

// Handle deletion confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $keyword_data) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    
    // Verify CSRF token
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid security token. Please try again.';
    } else if ($confirm !== 'DELETE') {
        $error = 'Please type "DELETE" to confirm deletion.';
    } else {
        try {
            // Delete the keyword
            $stmt = $pdo->prepare("DELETE FROM keywords WHERE id = ?");
            
            if ($stmt->execute([$keyword_id])) {
                $success = 'Keyword "' . htmlspecialchars($keyword_data['keyword']) . '" has been deleted successfully!';
                $keyword_data = null; // Clear data to hide form
            } else {
                $error = 'Failed to delete keyword. Please try again.';
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
    <title>Delete Keyword - Movie Search Panel</title>
    <link rel="stylesheet" href="style.css">
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="dashboard-header">
            <h1>🗑️ Delete Keyword</h1>
            <p>Permanently remove keyword from the system</p>
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
            
        <?php elseif ($success): ?>
            <div class="content-card">
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
                <div style="margin-top: 20px;">
                    <a href="dashboard.php" class="btn btn-primary">← Back to Dashboard</a>
                    <a href="add_keyword.php" class="btn btn-success">Add New Keyword</a>
                </div>
            </div>
            
        <?php elseif ($keyword_data): ?>
            
            <!-- Delete Confirmation -->
            <div class="content-card">
                <h2>⚠️ Confirm Keyword Deletion</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <div class="alert alert-warning">
                    <strong>⚠️ Warning:</strong> This action cannot be undone! You are about to permanently delete the following keyword:
                </div>
                
                <!-- Keyword Preview -->
                <div style="margin: 20px 0;">
                    <h4>Keyword to be deleted:</h4>
                    <div style="padding: 20px; background: #fdf2f2; border: 3px solid #e74c3c; border-radius: 8px; text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: #e74c3c; margin-bottom: 10px;">
                            "<?php echo htmlspecialchars($keyword_data['keyword']); ?>"
                        </div>
                        <?php if (!empty($keyword_data['description'])): ?>
                            <div style="color: #666; font-style: italic;">
                                <?php echo htmlspecialchars($keyword_data['description']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Keyword Details -->
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;">
                    <h4>Keyword Details:</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 10px;">
                        <div>
                            <strong>ID:</strong> #<?php echo $keyword_data['id']; ?>
                        </div>
                        <div>
                            <strong>Status:</strong> 
                            <span class="status-badge status-<?php echo $keyword_data['status']; ?>">
                                <?php echo ucfirst($keyword_data['status']); ?>
                            </span>
                        </div>
                        <div>
                            <strong>Created:</strong> <?php echo date('M j, Y H:i', strtotime($keyword_data['created_at'])); ?>
                        </div>
                        <div>
                            <strong>Updated:</strong> <?php echo date('M j, Y H:i', strtotime($keyword_data['updated_at'])); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Confirmation Form -->
                <form method="POST" action="" id="deleteForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label for="confirm">Type "DELETE" to confirm:</label>
                        <input 
                            type="text" 
                            id="confirm" 
                            name="confirm" 
                            class="form-control" 
                            placeholder="Type DELETE here"
                            required
                            autocomplete="off"
                            style="border-color: #e74c3c; background: #fdf2f2;"
                        >
                        <small style="color: #e74c3c; font-size: 12px;">
                            You must type "DELETE" exactly to confirm this action
                        </small>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="submit" class="btn btn-danger" id="deleteBtn" disabled>
                            🗑️ DELETE KEYWORD
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary">
                            ❌ Cancel
                        </a>
                        <a href="edit_keyword.php?id=<?php echo $keyword_id; ?>" class="btn btn-warning">
                            ✏️ Edit Instead
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Impact Warning -->
            <div class="content-card">
                <h2>📋 What happens when you delete this keyword?</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div>
                        <h4 style="color: #e74c3c;">🗑️ Will be removed:</h4>
                        <ul style="color: #666; font-size: 14px; line-height: 1.6;">
                            <li>Keyword from the database</li>
                            <li>All associated metadata</li>
                            <li>Any search references</li>
                            <li>Creation and update history</li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="color: #f39c12;">⚠️ Potential impacts:</h4>
                        <ul style="color: #666; font-size: 14px; line-height: 1.6;">
                            <li>Site visitors won't see this keyword</li>
                            <li>May affect search functionality</li>
                            <li>Cannot be recovered after deletion</li>
                            <li>May need to recreate manually</li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="color: #27ae60;">✅ Alternatives:</h4>
                        <ul style="color: #666; font-size: 14px; line-height: 1.6;">
                            <li>Set status to "Inactive" instead</li>
                            <li>Edit the keyword content</li>
                            <li>Update the description</li>
                            <li>Keep for future reference</li>
                        </ul>
                    </div>
                </div>
            </div>
            
        <?php endif; ?>
    </div>
    
    <div class="footer">
        <p>&copy; 2025 Movie Search Admin Panel | Delete Keywords</p>
    </div>
    
    <script src="scripts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const confirmInput = document.getElementById('confirm');
            const deleteBtn = document.getElementById('deleteBtn');
            const deleteForm = document.getElementById('deleteForm');
            
            if (confirmInput && deleteBtn) {
                // Enable/disable delete button based on confirmation text
                confirmInput.addEventListener('input', function() {
                    const value = this.value.trim();
                    if (value === 'DELETE') {
                        deleteBtn.disabled = false;
                        deleteBtn.style.opacity = '1';
                        deleteBtn.style.cursor = 'pointer';
                    } else {
                        deleteBtn.disabled = true;
                        deleteBtn.style.opacity = '0.5';
                        deleteBtn.style.cursor = 'not-allowed';
                    }
                });
                
                // Focus on confirmation input
                confirmInput.focus();
                
                // Add warning on form submission
                if (deleteForm) {
                    deleteForm.addEventListener('submit', function(e) {
                        const confirmValue = confirmInput.value.trim();
                        
                        if (confirmValue !== 'DELETE') {
                            e.preventDefault();
                            showAlert('Please type "DELETE" to confirm deletion.', 'error');
                            return false;
                        }
                        
                        // Final confirmation
                        const finalConfirm = confirm(
                            'FINAL WARNING: Are you absolutely sure you want to delete this keyword?\n\n' +
                            'Keyword: "<?php echo addslashes($keyword_data['keyword'] ?? ''); ?>"\n\n' +
                            'This action CANNOT be undone!'
                        );
                        
                        if (!finalConfirm) {
                            e.preventDefault();
                            return false;
                        }
                        
                        // Show loading state
                        deleteBtn.innerHTML = '⏳ Deleting...';
                        deleteBtn.disabled = true;
                    });
                }
            }
        });
        
        // Keyboard shortcut: Escape to cancel
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.location.href = 'dashboard.php';
            }
        });
        
        // Auto-clear confirmation on page load
        window.addEventListener('load', function() {
            const confirmInput = document.getElementById('confirm');
            if (confirmInput) {
                confirmInput.value = '';
            }
        });
    </script>
</body>
</html>