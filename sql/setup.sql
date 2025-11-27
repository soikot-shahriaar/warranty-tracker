-- Warranty Tracker CMS Database Setup
-- Run this script to create the database and tables

CREATE DATABASE IF NOT EXISTS warranty_tracker;
USE warranty_tracker;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Warranties table for storing warranty information
CREATE TABLE IF NOT EXISTS warranties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    brand VARCHAR(100),
    model VARCHAR(100),
    purchase_date DATE NOT NULL,
    warranty_period_months INT NOT NULL,
    warranty_expiry_date DATE NOT NULL,
    store_vendor VARCHAR(255),
    purchase_price DECIMAL(10,2),
    receipt_image VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_expiry_date (warranty_expiry_date),
    INDEX idx_product_name (product_name)
);

-- Insert a default admin user (password: admin123)
-- Password hash for 'admin123' using PHP password_hash()
INSERT INTO users (username, email, password_hash) VALUES 
('admin', 'admin@example.com', '$2y$10$6LiuUzDxKPdjCeb3bICKM.dHpFESU3fg7E8XGmYxzWA/4QI1Wv8gy');

-- Sample warranty data for testing
INSERT INTO warranties (user_id, product_name, brand, model, purchase_date, warranty_period_months, warranty_expiry_date, store_vendor, purchase_price, notes) VALUES
(1, 'Laptop Computer', 'Dell', 'XPS 13', '2024-01-15', 24, '2026-01-15', 'Best Buy', 1299.99, 'Business laptop with extended warranty'),
(1, 'Smartphone', 'Apple', 'iPhone 15', '2024-06-01', 12, '2025-06-01', 'Apple Store', 999.99, 'Latest model with AppleCare'),
(1, 'Washing Machine', 'Samsung', 'WF45R6100AP', '2023-12-10', 36, '2026-12-10', 'Home Depot', 649.99, 'Energy efficient front-load washer');

