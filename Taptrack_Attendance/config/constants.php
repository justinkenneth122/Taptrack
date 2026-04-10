<?php
/**
 * Application Constants and Configuration
 * 
 * TapTrack v2 - QR Code Attendance System with Role-Based Access Control
 */

// ============================================================
// APPLICATION SETTINGS
// ============================================================

define('APP_NAME', 'TapTrack');
define('APP_VERSION', '2.0');
define('APP_URL', 'http://localhost/Taptrack_Attendance');
define('INSTITUTION', 'FEU Roosevelt Marikina');

// ============================================================
// AUTHENTICATION SETTINGS
// ============================================================

// Admin credentials
$ADMIN_USER = 'admin@feuroosevelt.edu.ph';
$ADMIN_PASS = 'admin123';

// Session timeout in seconds (24 hours)
define('SESSION_TIMEOUT', 86400);

// Password requirements
define('MIN_PASSWORD_LENGTH', 8);
define('REQUIRE_STRONG_PASSWORD', true);

// ============================================================
// FEATURE SETTINGS
// ============================================================

// QR Code settings
define('QR_EXPIRATION_HOURS', 24);
define('QR_INCLUDE_WATERMARK', true);

// Face recognition settings
define('FACE_SIMILARITY_THRESHOLD', 0.6);
define('FACE_REGISTRATION_REQUIRED', false); // Optional face registration

// Event settings
define('MAX_EVENTS_PER_PAGE', 20);
define('ALLOW_ORGANIZER_CREATE_EVENTS', true);

// ============================================================
// USER ROLES
// ============================================================

$ROLES = [
    'admin' => 'System Administrator',
    'organizer' => 'Event Organizer',
    'student' => 'Student'
];

// ============================================================
// COURSES & ACADEMIC LEVELS
// ============================================================

$COURSES = [
    "BS Information Technology",
    "BS Computer Science",
    "BS Accountancy",
    "BS Business Administration",
    "BS Psychology",
    "BS Nursing",
    "BS Pharmacy",
    "BS Medical Technology",
    "BS Education",
    "BS Criminology"
];

$YEAR_LEVELS = [
    "1st Year",
    "2nd Year",
    "3rd Year",
    "4th Year",
    "5th Year"
];

// ============================================================
// PERMISSION GROUPS
// ============================================================

$PERMISSIONS = [
    'admin' => [
        'manage_users',
        'manage_organizers',
        'manage_roles',
        'view_all_events',
        'manage_all_events',
        'view_all_attendance',
        'manage_settings',
        'archive_events',
        'delete_events',
        'bulk_actions'
    ],
    'organizer' => [
        'create_event',
        'manage_own_events',
        'view_own_attendance',
        'check_in_students',
        'generate_qr'
    ],
    'student' => [
        'view_profile',
        'register_face',
        'check_in',
        'view_own_attendance'
    ]
];

// ============================================================
// HELPER FUNCTION - GET INSTALLATION SQL
// ============================================================

function getInstallSQL() {
    // NOTE: Full schema is in database/migrations/001_initial_schema.php
    // This is kept for backwards compatibility
    return <<<'SQL'
-- This function is deprecated. Use database/migrations for schema.
CREATE DATABASE IF NOT EXISTS taptrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SQL;
}

?>
