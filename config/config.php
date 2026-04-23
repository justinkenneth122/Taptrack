<?php
/**
 * ============================================================
 * TAPTRACK — Application Configuration
 * ============================================================
 * Central configuration file for database and application settings
 */

// Database Configuration
// XAMPP defaults: root user with no password
$DB_HOST = 'localhost';
$DB_NAME = 'taptrack';
$DB_USER = 'root';
$DB_PASS = '';

// For production, use environment variables:
// $DB_HOST = getenv('DB_HOST') ?: 'localhost';
// $DB_NAME = getenv('DB_NAME') ?: 'taptrack';
// $DB_USER = getenv('DB_USER') ?: 'root';
// $DB_PASS = getenv('DB_PASS') ?: '';

// Admin credentials (CHANGE IN PRODUCTION)
$ADMIN_USER = 'admin';
$ADMIN_PASS = 'admin123';

// For production, use environment variables:
// $ADMIN_USER = getenv('ADMIN_USER') ?: 'admin';
// $ADMIN_PASS = getenv('ADMIN_PASS') ?: 'admin123';

// Application settings
$APP_NAME = 'Taptrack';
$APP_VERSION = '2.0.0';
$APP_DESCRIPTION = 'QR Code Attendance System — FEU Roosevelt Marikina';

// Course and year level options
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
    "BS Criminology",
];

$YEAR_LEVELS = ["1st Year", "2nd Year", "3rd Year", "4th Year", "5th Year"];

// Email validation pattern
$EMAIL_PATTERN = '/^R\d{8,}@feuroosevelt\.edu\.ph$/i';

// Session configuration
$SESSION_TIMEOUT = 3600; // 1 hour in seconds
$SESSION_NAME = 'taptrack_session';

// Security headers
$SECURITY_HEADERS = [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'SAMEORIGIN',
    'X-XSS-Protection' => '1; mode=block',
];

// Database connection DSN (now built in Database.php)
// Removed: charset specification due to compatibility issues

return [
    'db' => [
        'host' => $DB_HOST,
        'name' => $DB_NAME,
        'user' => $DB_USER,
        'pass' => $DB_PASS,
    ],
    'admin' => [
        'user' => $ADMIN_USER,
        'pass' => $ADMIN_PASS,
    ],
    'app' => [
        'name' => $APP_NAME,
        'version' => $APP_VERSION,
        'description' => $APP_DESCRIPTION,
        'email_pattern' => $EMAIL_PATTERN,
    ],
    'courses' => $COURSES,
    'year_levels' => $YEAR_LEVELS,
    'session' => [
        'timeout' => $SESSION_TIMEOUT,
        'name' => $SESSION_NAME,
    ],
    'security' => $SECURITY_HEADERS,
];
