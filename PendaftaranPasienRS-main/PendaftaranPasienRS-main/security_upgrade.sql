-- Security upgrade script for RS Sehat Selalu
-- This script adds security tables and updates existing tables

-- Add security columns to users table
ALTER TABLE users 
ADD COLUMN two_factor_secret VARCHAR(255) DEFAULT NULL,
ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
ADD COLUMN last_login TIMESTAMP NULL,
ADD COLUMN login_attempts INT DEFAULT 0,
ADD COLUMN account_locked TINYINT(1) DEFAULT 0,
ADD COLUMN password_changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Update password field to accommodate longer hashes
ALTER TABLE users MODIFY password VARCHAR(255) NOT NULL;

-- Add security columns to dokter table
ALTER TABLE dokter 
ADD COLUMN two_factor_secret VARCHAR(255) DEFAULT NULL,
ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
ADD COLUMN last_login TIMESTAMP NULL,
ADD COLUMN login_attempts INT DEFAULT 0,
ADD COLUMN account_locked TINYINT(1) DEFAULT 0,
ADD COLUMN password_changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Update password field for dokter table
ALTER TABLE dokter MODIFY password VARCHAR(255) NOT NULL;

-- Create login attempts table for rate limiting
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(50) NULL,
    success TINYINT(1) DEFAULT 0,
    timestamp INT NOT NULL,
    user_agent TEXT NULL,
    INDEX idx_identifier (identifier),
    INDEX idx_timestamp (timestamp)
);

-- Create security logs table
CREATE TABLE security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    event_type VARCHAR(50) NOT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_event_type (event_type),
    INDEX idx_timestamp (timestamp)
);

-- Create 2FA backup codes table
CREATE TABLE backup_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(10) NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_code (code)
);

-- Create session management table
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(128) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_expires_at (expires_at)
);

-- Create encrypted data table for sensitive information
CREATE TABLE encrypted_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    field_name VARCHAR(50) NOT NULL,
    encrypted_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_field_name (field_name)
);

-- Create audit log table for data changes
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    action VARCHAR(10) NOT NULL, -- INSERT, UPDATE, DELETE
    old_values JSON NULL,
    new_values JSON NULL,
    user_id INT NULL,
    ip_address VARCHAR(45) NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_user_id (user_id),
    INDEX idx_timestamp (timestamp)
);

-- Create password reset tokens table
CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(128) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
);

-- Add encryption key management
CREATE TABLE encryption_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(50) NOT NULL UNIQUE,
    key_value VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    rotated_at TIMESTAMP NULL,
    is_active TINYINT(1) DEFAULT 1
);

-- Insert default encryption key (should be changed in production)
INSERT INTO encryption_keys (key_name, key_value) VALUES 
('default', 'your-32-character-secret-key-here');

-- Create stored procedures for security functions
DELIMITER //

CREATE PROCEDURE CleanupLoginAttempts()
BEGIN
    DELETE FROM login_attempts WHERE timestamp < UNIX_TIMESTAMP() - 3600;
END //

CREATE PROCEDURE CleanupExpiredSessions()
BEGIN
    DELETE FROM user_sessions WHERE expires_at < NOW();
END //

CREATE PROCEDURE LogSecurityEvent(
    IN p_user_id INT,
    IN p_event_type VARCHAR(50),
    IN p_details TEXT,
    IN p_ip_address VARCHAR(45),
    IN p_user_agent TEXT
)
BEGIN
    INSERT INTO security_logs (user_id, event_type, details, ip_address, user_agent)
    VALUES (p_user_id, p_event_type, p_details, p_ip_address, p_user_agent);
END //

DELIMITER ;

-- Create events for automatic cleanup (MySQL 5.1+)
CREATE EVENT IF NOT EXISTS cleanup_login_attempts
ON SCHEDULE EVERY 1 HOUR
DO
  CALL CleanupLoginAttempts();

CREATE EVENT IF NOT EXISTS cleanup_expired_sessions
ON SCHEDULE EVERY 30 MINUTE
DO
  CALL CleanupExpiredSessions();

-- Enable event scheduler
SET GLOBAL event_scheduler = ON;

-- Create indexes for better performance
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_last_login ON users(last_login);
CREATE INDEX idx_dokter_username ON dokter(username);
CREATE INDEX idx_dokter_last_login ON dokter(last_login);

-- Update existing passwords to use password_hash format
-- NOTE: This will require users to reset their passwords
-- Alternatively, you can create a migration script to convert existing MD5 hashes
UPDATE users SET password = '$2y$10$example.hash.here' WHERE id = 0; -- This is just an example, don't run this

-- Add foreign key constraints
ALTER TABLE security_logs ADD CONSTRAINT fk_security_logs_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE user_sessions ADD CONSTRAINT fk_user_sessions_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE password_reset_tokens ADD CONSTRAINT fk_password_reset_tokens_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE audit_logs ADD CONSTRAINT fk_audit_logs_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
