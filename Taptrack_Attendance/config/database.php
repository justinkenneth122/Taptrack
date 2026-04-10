<?php
/**
 * Database Configuration and Connection
 * 
 * Updated for TapTrack v2 with role-based access control
 */

// Database credentials
$DB_HOST = 'localhost';
$DB_NAME = 'taptrack';
$DB_USER = 'root';
$DB_PASS = '';
$DB_PORT = 3306;

// Connection options
$DB_OPTIONS = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

try {
    $dsn = "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $DB_OPTIONS);
    
    // Run program-based event support migration if needed
    require_once __DIR__ . '/../database/migrations/002_add_program_support.php';
    $migration_result = runProgramMigration($pdo);
    // Log migration status but don't fail if it fails (columns might already exist)
    
    // Run face verification migration if needed
    require_once __DIR__ . '/../database/migrations/003_add_face_verification.php';
    $face_verification_result = runFaceVerificationMigration($pdo);
    // Log migration status but don't fail if it fails (column might already exist)
    
    // Run user management migration if needed
    require_once __DIR__ . '/../database/migrations/004_add_user_management.php';
    $user_management_result = runUserManagementMigration($pdo);
    // Log migration status but don't fail if it fails (tables might already exist)
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        // Run database setup
        require_once __DIR__ . '/../database/migrations/001_initial_schema.php';
        require_once __DIR__ . '/../database/migrations/002_add_program_support.php';
        require_once __DIR__ . '/../database/migrations/003_add_face_verification.php';
        require_once __DIR__ . '/../database/migrations/004_add_user_management.php';
        try {
            // Create database
            $pdo_setup = new PDO("mysql:host=$DB_HOST;port=$DB_PORT;charset=utf8mb4", $DB_USER, $DB_PASS, $DB_OPTIONS);
            $pdo_setup->exec("CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Reconnect to new database
            $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $DB_OPTIONS);
            
            // Run migration
            $result = runMigration($pdo);
            $result2 = runProgramMigration($pdo);
            $result3 = runFaceVerificationMigration($pdo);
            $result4 = runUserManagementMigration($pdo);
            if ($result['success'] && $result2['success'] && $result3['success'] && $result4) {
                @session_start();
                $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Database initialized successfully!'];
                header('Location: ?page=login');
                exit;
            } else {
                die("Migration error: " . ($result['message'] ?? $result2['message'] ?? $result3['message'] ?? 'User management migration failed'));
            }
        } catch (\Exception $ex) {
            die("Database setup error: " . $ex->getMessage());
        }
    }
    die("Database connection failed: " . $e->getMessage());
}

// Set session configuration BEFORE starting session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');

// Start session
@session_start();
