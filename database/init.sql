-- ============================================================
-- TAPTRACK Database Schema
-- ============================================================
-- Create database and tables for QR Code Attendance System

CREATE DATABASE IF NOT EXISTS taptrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE taptrack;

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID',
    email VARCHAR(255) NOT NULL UNIQUE COMMENT 'FEU Email address',
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    student_number VARCHAR(50) NOT NULL UNIQUE,
    course VARCHAR(100) NOT NULL COMMENT 'Program/Course',
    year_level VARCHAR(20) DEFAULT '1st Year',
    password VARCHAR(255) DEFAULT '' COMMENT 'Hashed password',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_student_number (student_number),
    INDEX idx_course (course),
    INDEX idx_created_at (created_at)
) COMMENT 'Registered student accounts';

-- Events table
CREATE TABLE IF NOT EXISTS events (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID',
    name VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    archived TINYINT(1) DEFAULT 0 COMMENT '0=active, 1=archived',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_date (date),
    INDEX idx_archived (archived),
    INDEX idx_created_at (created_at)
) COMMENT 'Events requiring attendance tracking';

-- Attendance records table
CREATE TABLE IF NOT EXISTS attendance (
    id VARCHAR(36) PRIMARY KEY COMMENT 'UUID',
    student_id VARCHAR(36) NOT NULL,
    event_id VARCHAR(36) NOT NULL,
    scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Check-in time',
    scanned_out_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Optional check-out time',
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (student_id, event_id),
    
    INDEX idx_student_id (student_id),
    INDEX idx_event_id (event_id),
    INDEX idx_scanned_at (scanned_at)
) COMMENT 'Student attendance records for events';

-- Audit log table (optional, for tracking admin actions)
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id VARCHAR(36),
    user_id VARCHAR(36),
    details LONGTEXT DEFAULT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    INDEX idx_user_id (user_id)
) COMMENT 'Admin activity and system audit trail';
