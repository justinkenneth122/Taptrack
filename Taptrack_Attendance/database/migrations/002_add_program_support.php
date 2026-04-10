<?php
/**
 * Database Migration: Add Program-Based Event Support
 * Adds QR_token and programs fields to events table for program-based access control
 */

function runProgramMigration($pdo) {
    try {
        // ======================== ALTER EVENTS TABLE ========================
        // Add QR_token field (unique identifier for each event)
        $pdo->exec("ALTER TABLE events ADD COLUMN IF NOT EXISTS QR_token VARCHAR(255) UNIQUE NOT NULL DEFAULT UUID() COMMENT 'Unique token for QR code scanning'");

        // Add programs JSON field (stores array of eligible programs)
        $pdo->exec("ALTER TABLE events ADD COLUMN IF NOT EXISTS programs JSON DEFAULT JSON_ARRAY('ALL') COMMENT 'JSON array of program names allowed to attend'");

        // Add index for QR_token for faster lookup
        $pdo->exec("ALTER TABLE events ADD INDEX IF NOT EXISTS idx_qr_token (QR_token)");

        // ======================== VERIFY STUDENTS TABLE HAS COURSE ========================
        // Check if students table has course field, if not add it
        $columns = $pdo->query("SHOW COLUMNS FROM students")->fetchAll();
        $hasProgram = false;
        foreach ($columns as $col) {
            if ($col['Field'] === 'program') {
                $hasProgram = true;
                break;
            }
        }

        // If students has 'course' but not 'program', we'll use 'course' as program field
        // If it has neither, add program field
        if (!$hasProgram) {
            $hasCourse = false;
            foreach ($columns as $col) {
                if ($col['Field'] === 'course') {
                    $hasCourse = true;
                    break;
                }
            }
            
            if (!$hasCourse) {
                $pdo->exec("ALTER TABLE students ADD COLUMN program VARCHAR(100) COMMENT 'Program/Course name (IT, Psychology, Criminology, etc.)'");
            }
        }

        return [
            'success' => true,
            'message' => 'Program-based event support added successfully'
        ];

    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Migration error: ' . $e->getMessage()
        ];
    }
}

// Function to generate a secure QR token
function generateQRToken() {
    return bin2hex(random_bytes(16));
}

// Function to get or create QR token for an event
function ensureEventQRToken($pdo, $event_id) {
    try {
        // Check if event already has QR_token
        $stmt = $pdo->prepare("SELECT QR_token FROM events WHERE id = ?");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch();
        
        if ($event && !empty($event['QR_token'])) {
            return $event['QR_token'];
        }
        
        // Generate new QR token
        $qr_token = generateQRToken();
        
        // Ensure uniqueness
        while (true) {
            $check = $pdo->prepare("SELECT id FROM events WHERE QR_token = ?");
            $check->execute([$qr_token]);
            if (!$check->fetch()) {
                break;
            }
            $qr_token = generateQRToken();
        }
        
        // Update event with QR token
        $stmt = $pdo->prepare("UPDATE events SET QR_token = ? WHERE id = ?");
        $stmt->execute([$qr_token, $event_id]);
        
        return $qr_token;
    } catch (PDOException $e) {
        return null;
    }
}
