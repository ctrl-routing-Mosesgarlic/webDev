-- Database creation (if not exists)
CREATE DATABASE IF NOT EXISTS wsm_system;

-- Use the database
USE wsm_system;

-- Users table creation
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    role ENUM('admin', 'warehouse_manager', 'stock_clerk', 'supplier', 'customer') NOT NULL,
    registration_date DATETIME NOT NULL,
    last_login DATETIME NULL,
    status TINYINT(1) DEFAULT 1 COMMENT '1=active, 0=inactive'
);

-- Add index for faster searches
CREATE INDEX idx_email ON users(email);
CREATE INDEX idx_role ON users(role);
