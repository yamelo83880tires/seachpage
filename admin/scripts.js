/**
 * Admin Panel JavaScript
 * Contains form validation, interactive features, and utilities
 */

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize all features
    initFormValidation();
    initConfirmDialogs();
    initTooltips();
    initAutoRefresh();
    
});

/**
 * Form Validation
 */
function initFormValidation() {
    
    // Login form validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                showAlert('Please fill in all fields', 'error');
                return false;
            }
            
            if (username.length < 3) {
                e.preventDefault();
                showAlert('Username must be at least 3 characters', 'error');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showAlert('Password must be at least 6 characters', 'error');
                return false;
            }
        });
    }
    
    // Keyword form validation
    const keywordForm = document.getElementById('keywordForm');
    if (keywordForm) {
        keywordForm.addEventListener('submit', function(e) {
            const keyword = document.getElementById('keyword').value.trim();
            
            if (!keyword) {
                e.preventDefault();
                showAlert('Keyword is required', 'error');
                return false;
            }
            
            if (keyword.length < 2) {
                e.preventDefault();
                showAlert('Keyword must be at least 2 characters', 'error');
                return false;
            }
            
            if (keyword.length > 255) {
                e.preventDefault();
                showAlert('Keyword must be less than 255 characters', 'error');
                return false;
            }
        });
    }
    
    // Real-time validation for inputs
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            clearFieldError(this);
        });
    });
}

/**
 * Validate individual field
 */
function validateField(field) {
    const value = field.value.trim();
    const fieldName = field.getAttribute('name');
    
    // Remove existing error styling
    clearFieldError(field);
    
    // Check if field is required
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, `${fieldName} is required`);
        return false;
    }
    
    // Specific validations
    switch (fieldName) {
        case 'username':
            if (value && value.length < 3) {
                showFieldError(field, 'Username must be at least 3 characters');
                return false;
            }
            break;
            
        case 'password':
            if (value && value.length < 6) {
                showFieldError(field, 'Password must be at least 6 characters');
                return false;
            }
            break;
            
        case 'keyword':
            if (value && value.length < 2) {
                showFieldError(field, 'Keyword must be at least 2 characters');
                return false;
            }
            if (value && value.length > 255) {
                showFieldError(field, 'Keyword must be less than 255 characters');
                return false;
            }
            break;
    }
    
    return true;
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    field.style.borderColor = '#e74c3c';
    field.style.background = '#fdf2f2';
    
    // Remove existing error message
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Add error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.style.color = '#e74c3c';
    errorDiv.style.fontSize = '12px';
    errorDiv.style.marginTop = '5px';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

/**
 * Clear field error
 */
function clearFieldError(field) {
    field.style.borderColor = '#e1e5e9';
    field.style.background = '#f8f9fa';
    
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

/**
 * Initialize confirmation dialogs
 */
function initConfirmDialogs() {
    const deleteLinks = document.querySelectorAll('.confirm-delete');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const message = this.getAttribute('data-message') || 'Are you sure you want to delete this item?';
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.js-alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} js-alert`;
    alertDiv.textContent = message;
    
    // Insert at top of content
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
        
        // Smooth scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

/**
 * Initialize tooltips (simple implementation)
 */
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            showTooltip(this);
        });
        
        element.addEventListener('mouseleave', function() {
            hideTooltip();
        });
    });
}

/**
 * Show tooltip
 */
function showTooltip(element) {
    const message = element.getAttribute('data-tooltip');
    if (!message) return;
    
    const tooltip = document.createElement('div');
    tooltip.id = 'tooltip';
    tooltip.textContent = message;
    tooltip.style.cssText = `
        position: absolute;
        background: #333;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 1000;
        pointer-events: none;
        white-space: nowrap;
    `;
    
    document.body.appendChild(tooltip);
    
    element.addEventListener('mousemove', function(e) {
        tooltip.style.left = (e.pageX + 10) + 'px';
        tooltip.style.top = (e.pageY - 30) + 'px';
    });
}

/**
 * Hide tooltip
 */
function hideTooltip() {
    const tooltip = document.getElementById('tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

/**
 * Initialize auto-refresh for dashboard stats
 */
function initAutoRefresh() {
    const statsCards = document.querySelectorAll('.stat-card[data-refresh]');
    if (statsCards.length > 0) {
        // Refresh stats every 30 seconds
        setInterval(refreshStats, 30000);
    }
}

/**
 * Refresh dashboard statistics
 */
function refreshStats() {
    fetch('dashboard.php?ajax=stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatCard('total-keywords', data.stats.total_keywords);
                updateStatCard('active-keywords', data.stats.active_keywords);
                updateStatCard('inactive-keywords', data.stats.inactive_keywords);
            }
        })
        .catch(error => {
            console.log('Stats refresh failed:', error);
        });
}

/**
 * Update individual stat card
 */
function updateStatCard(cardId, value) {
    const card = document.getElementById(cardId);
    if (card) {
        const numberElement = card.querySelector('.stat-number');
        if (numberElement) {
            // Animate number change
            const currentValue = parseInt(numberElement.textContent);
            if (currentValue !== value) {
                animateNumber(numberElement, currentValue, value);
            }
        }
    }
}

/**
 * Animate number change
 */
function animateNumber(element, from, to) {
    const duration = 1000; // 1 second
    const steps = 20;
    const stepValue = (to - from) / steps;
    const stepDuration = duration / steps;
    let currentStep = 0;
    
    const timer = setInterval(() => {
        currentStep++;
        const currentValue = Math.round(from + (stepValue * currentStep));
        element.textContent = currentValue;
        
        if (currentStep >= steps) {
            clearInterval(timer);
            element.textContent = to;
        }
    }, stepDuration);
}

/**
 * Utility functions
 */

// Format date for display
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

// Copy text to clipboard
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showAlert('Copied to clipboard!', 'success');
        });
    } else {
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showAlert('Copied to clipboard!', 'success');
    }
}

// Search/filter table rows
function filterTable(searchInput, tableId) {
    const input = document.getElementById(searchInput);
    const table = document.getElementById(tableId);
    
    if (!input || !table) return;
    
    input.addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
}

// Smooth page transitions
function smoothTransition(url) {
    document.body.style.opacity = '0.5';
    setTimeout(() => {
        window.location.href = url;
    }, 200);
}

// Auto-save form data to localStorage
function autoSaveForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        // Load saved value
        const savedValue = localStorage.getItem(`autosave_${input.name}`);
        if (savedValue && !input.value) {
            input.value = savedValue;
        }
        
        // Save on change
        input.addEventListener('input', function() {
            localStorage.setItem(`autosave_${this.name}`, this.value);
        });
    });
    
    // Clear saved data on successful submit
    form.addEventListener('submit', function() {
        inputs.forEach(input => {
            localStorage.removeItem(`autosave_${input.name}`);
        });
    });
}