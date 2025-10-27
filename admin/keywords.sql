-- Admin Panel Database Structure
-- Run this SQL to create the database and table

-- Create database
CREATE DATABASE IF NOT EXISTS admin_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE admin_panel;

-- Create keywords table
CREATE TABLE IF NOT EXISTS keywords (
    id INT AUTO_INCREMENT PRIMARY KEY,
    keyword VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO keywords (keyword, description, status) VALUES
('aqua regia fest', 'Default movie search keyword', 'active'),
('bollywood movies', 'Bollywood movie downloads', 'active'),
('hollywood movies', 'Hollywood movie downloads', 'active'),
('web series', 'Web series downloads', 'active'),
('tamil movies', 'Tamil movie downloads', 'inactive');

-- Create admin users table (optional - for future use)
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Insert default admin user (username: admin, password: admin123)
INSERT INTO admin_users (username, password_hash, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com');