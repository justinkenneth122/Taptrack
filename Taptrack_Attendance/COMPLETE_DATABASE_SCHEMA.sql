-- ============================================================
-- TAPTRACK - Complete Final Database Schema
-- QR Code Attendance System for FEU Roosevelt Marikina
-- ============================================================
-- Created: April 3, 2026
-- Includes: Initial schema + Program-based event support
-- ============================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS taptrack;
USE taptrack;

-- ======================== STUDENTS TABLE ========================
-- Stores student information with face recognition data
CREATE TABLE IF NOT EXISTS students (
    id VARCHAR(36) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    student_number VARCHAR(50) NOT NULL,
    course TEXT NOT NULL COMMENT 'Program/Course name (IT, Psychology, Business, etc.)',
    year_level VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    face_descriptor LONGTEXT COMMENT 'JSON array of 128 float values from face-api.js',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_student_number (student_number),
    INDEX idx_course (course(50)),
    FULLTEXT INDEX idx_name (first_name, last_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================== EVENTS TABLE ========================
-- Stores event information with program-based access control
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT,
    programs JSON DEFAULT JSON_ARRAY('ALL') COMMENT 'JSON array of program names allowed to attend',
    QR_token VARCHAR(255) UNIQUE NOT NULL DEFAULT (UUID()) COMMENT 'Unique token for QR code scanning',
    archived INT DEFAULT 0,
    created_by VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_date (date),
    INDEX idx_archived (archived),
    INDEX idx_qr_token (QR_token),
    INDEX idx_programs (programs(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================== ATTENDANCE TABLE ========================
-- Records student attendance at events
-- Enforces one check-in per student per event
CREATE TABLE IF NOT EXISTS attendance (
    id VARCHAR(36) PRIMARY KEY,
    student_id VARCHAR(36) NOT NULL,
    event_id INT NOT NULL,
    scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    face_verified TINYINT(1) DEFAULT 0 COMMENT 'Whether attendance was verified via face recognition',
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (student_id, event_id) COMMENT 'Prevent duplicate check-ins',
    INDEX idx_student (student_id),
    INDEX idx_event (event_id),
    INDEX idx_scanned (scanned_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================== FACE_MATCHES TABLE ========================
-- Audit log for tracking potential duplicate student faces
CREATE TABLE IF NOT EXISTS face_matches (
    id VARCHAR(36) PRIMARY KEY,
    student_id_1 VARCHAR(36) NOT NULL,
    student_id_2 VARCHAR(36) NOT NULL,
    similarity_score DECIMAL(5, 3) NOT NULL COMMENT 'Similarity score 0-1 (0=no match, 1=identical)',
    status VARCHAR(50) DEFAULT 'detected' COMMENT 'detected, verified, resolved, false_positive',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_by VARCHAR(36),
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (student_id_1) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id_2) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_student1 (student_id_1),
    INDEX idx_student2 (student_id_2),
    INDEX idx_status (status),
    INDEX idx_similarity (similarity_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================== SYSTEM_LOG TABLE ========================
-- Audit trail for system actions and changes
CREATE TABLE IF NOT EXISTS system_log (
    id VARCHAR(36) PRIMARY KEY,
    action VARCHAR(100) NOT NULL,
    user_id VARCHAR(36),
    student_id VARCHAR(36),
    details JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_action (action),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================== ADMIN USERS TABLE ========================
-- Stores admin account information
CREATE TABLE IF NOT EXISTS admin_users (
    id VARCHAR(36) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Sample Data (Optional - Comment out if not needed)
-- ============================================================

-- Sample Admin User (username: admin | password: admin123)
-- Password: $2y$10$abcdefghijklmnopqrstuvwxyz (bcrypt hashed)
INSERT INTO admin_users (id, email, username, password, full_name, role) VALUES
('11111111-1111-1111-1111-111111111111', 'admin@feuroosevelt.edu.ph', 'admin', '$2y$10$iuTMNM/p.Vkp0J6e8EG5ReNMCsD0aQXlJ9pXqSHBLhF7N7K1gvWna', 'System Administrator', 'admin')
ON DUPLICATE KEY UPDATE email=email;

-- Sample Events
INSERT INTO events (name, date, location, description, programs, archived) VALUES
('Orientation Week', '2026-04-10', 'Main Auditorium', 'Welcome to FEU Roosevelt Marikina', JSON_ARRAY('ALL'), 0),
('Tech Innovation Summit', '2026-04-15', 'IT Building Room 101', 'For IT and Computer Science students', JSON_ARRAY('BS Information Technology', 'BS Computer Science'), 0),
('Psychology Seminar', '2026-04-20', 'Psychology Building', 'Latest developments in psychology', JSON_ARRAY('BS Psychology'), 0)
ON DUPLICATE KEY UPDATE name=name;

-- ============================================================
-- Database Setup Instructions
-- ============================================================
-- 1. Open phpMyAdmin in your browser
-- 2. Go to SQL tab
-- 3. Copy and paste the entire SQL script above
-- 4. Click "Go" to execute
--
-- OR from command line:
-- mysql -u root -p < COMPLETE_DATABASE_SCHEMA.sql
--
-- Default Admin Credentials:
-- Username: admin
-- Password: admin123
-- ============================================================

-- ======================== VERIFICATION QUERIES ========================
-- Use these to verify your setup:

-- Check all tables exist
-- SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='taptrack';

-- Check students table structure
-- DESCRIBE students;

-- Check events table structure
-- DESCRIBE events;

-- Check attendance table structure
-- DESCRIBE attendance;

-- Count records in each table
-- SELECT 'students' as table_name, COUNT(*) as count FROM students
-- UNION ALL
-- SELECT 'events', COUNT(*) FROM events
-- UNION ALL
-- SELECT 'attendance', COUNT(*) FROM attendance
-- UNION ALL
-- SELECT 'admin_users', COUNT(*) FROM admin_users;

-- ============================================================
-- Key Features & Updates
-- ============================================================
-- 
-- ✅ QR Code Support:
--    - QR_token field in events table for secure QR scanning
--    - Programs field stores JSON array of eligible programs
--
-- ✅ Program-Based Access Control:
--    - students.course field stores program name
--    - events.programs JSON array stores eligible programs
--    - Program filtering in queries using JSON_CONTAINS()
--
-- ✅ Attendance Management:
--    - UNIQUE constraint prevents duplicate check-ins
--    - Foreign keys enforce referential integrity
--    - Timestamps track when attendance was recorded
--
-- ✅ Face Recognition:
--    - face_descriptor stores 128-point face data
--    - face_matches table audits potential duplicates
--    - face_verified flag tracks verification method
--
-- ✅ Security & Auditing:
--    - system_log table tracks all Admin actions
--    - admin_users table stores admin accounts
--    - IP address logging for audit trail
--
-- ✅ Performance:
--    - Proper indexes on frequently queried columns
--    - JSON column types for flexible data
--    - FULLTEXT indexes for student search
--
-- ============================================================
-- Backup & Recovery
-- ============================================================
--
-- To backup database:
-- mysqldump -u root -p taptrack > taptrack_backup.sql
--
-- To restore from backup:
-- mysql -u root -p taptrack < taptrack_backup.sql
--
-- ============================================================
