<?php
/**
 * Database Migration: Initial Schema
 * Creates all required tables for TapTrack attendance system with face recognition
 */

function runMigration($pdo) {
    try {
        // ======================== STUDENTS TABLE ========================
        $pdo->exec("CREATE TABLE IF NOT EXISTS students (
            id VARCHAR(36) PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            student_number VARCHAR(50) NOT NULL,
            course TEXT NOT NULL,
            year_level VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL,
            face_descriptor LONGTEXT COMMENT 'JSON array of 128 float values from face-api.js',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_student_number (student_number),
            FULLTEXT INDEX idx_name (first_name, last_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // ======================== EVENTS TABLE ========================
        $pdo->exec("CREATE TABLE IF NOT EXISTS events (
            id VARCHAR(36) PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            date DATE NOT NULL,
            location VARCHAR(255) NOT NULL,
            description TEXT,
            archived INT DEFAULT 0,
            created_by VARCHAR(36),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_date (date),
            INDEX idx_archived (archived)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // ======================== ATTENDANCE TABLE ========================
        $pdo->exec("CREATE TABLE IF NOT EXISTS attendance (
            id VARCHAR(36) PRIMARY KEY,
            student_id VARCHAR(36) NOT NULL,
            event_id VARCHAR(36) NOT NULL,
            scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            face_verified TINYINT(1) DEFAULT 0 COMMENT 'Whether attendance was verified via face recognition',
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
            UNIQUE KEY unique_attendance (student_id, event_id),
            INDEX idx_student (student_id),
            INDEX idx_event (event_id),
            INDEX idx_scanned (scanned_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // ======================== FACE_MATCHES TABLE (AUDIT LOG) ========================
        // Optional table to log face matching attempts and potential duplicates
        $pdo->exec("CREATE TABLE IF NOT EXISTS face_matches (
            id VARCHAR(36) PRIMARY KEY,
            student_id_1 VARCHAR(36) NOT NULL,
            student_id_2 VARCHAR(36) NOT NULL,
            similarity_score DECIMAL(5, 3) NOT NULL COMMENT 'Similarity score 0-1',
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // ======================== SYSTEM_LOG TABLE ========================
        $pdo->exec("CREATE TABLE IF NOT EXISTS system_log (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        return [
            'success' => true,
            'message' => 'Database migration completed successfully'
        ];

    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Migration error: ' . $e->getMessage()
        ];
    }
}
