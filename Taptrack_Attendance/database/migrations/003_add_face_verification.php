<?php
/**
 * Migration: Add face_verified Column to Attendance Table
 * 
 * This migration adds the face_verified column to the attendance table
 * if it doesn't already exist. It's safe to run multiple times.
 */

function runFaceVerificationMigration($pdo) {
    try {
        // Check if column exists
        $checkColumn = $pdo->query("SHOW COLUMNS FROM attendance LIKE 'face_verified'");
        $columnExists = $checkColumn->rowCount() > 0;
        
        if (!$columnExists) {
            // Add the column if it doesn't exist
            $pdo->exec("ALTER TABLE attendance ADD COLUMN face_verified TINYINT(1) DEFAULT 0 AFTER scanned_at COMMENT 'Whether attendance was verified via face recognition'");
            
            return [
                'success' => true,
                'message' => 'face_verified column added to attendance table'
            ];
        } else {
            return [
                'success' => true,
                'message' => 'face_verified column already exists'
            ];
        }
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error running migration: ' . $e->getMessage()
        ];
    }
}

// Export function for use in database.php or setup
if (!function_exists('runFaceVerificationMigration')) {
    function runFaceVerificationMigration($pdo) {
        try {
            $checkColumn = $pdo->query("SHOW COLUMNS FROM attendance LIKE 'face_verified'");
            $columnExists = $checkColumn->rowCount() > 0;
            
            if (!$columnExists) {
                $pdo->exec("ALTER TABLE attendance ADD COLUMN face_verified TINYINT(1) DEFAULT 0 AFTER scanned_at COMMENT 'Whether attendance was verified via face recognition'");
                return ['success' => true, 'message' => 'face_verified column added'];
            }
            
            return ['success' => true, 'message' => 'face_verified column exists'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
