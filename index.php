<?php
/**
 * ============================================================
 * TAPTRACK — Main Application Entry Point
 * ============================================================
 * QR Code Attendance System for FEU Roosevelt Marikina
 * 
 * Version: 2.0.0 (Refactored Architecture)
 * 
 * SETUP:
 * 1. Create MySQL database from database/init.sql
 * 2. Access via browser: http://localhost/Taptrack/
 * 3. Default admin login: admin / admin123
 */

// ============================================================
// INITIALIZATION
// ============================================================

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start session
session_start();

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Load configuration
$config = require_once __DIR__ . '/config/config.php';

// Load Database class
require_once __DIR__ . '/config/Database.php';

// Load helpers
require_once __DIR__ . '/backend/helpers/helpers.php';

// Load Models
require_once __DIR__ . '/backend/models/Student.php';
require_once __DIR__ . '/backend/models/Event.php';
require_once __DIR__ . '/backend/models/Attendance.php';

// Load Controllers
require_once __DIR__ . '/backend/controllers/AuthController.php';
require_once __DIR__ . '/backend/controllers/EventController.php';
require_once __DIR__ . '/backend/controllers/AttendanceController.php';

// Load API Router
require_once __DIR__ . '/backend/routes/api.php';

// ============================================================
// DATABASE INITIALIZATION
// ============================================================

try {
    $database = Database::getInstance(
        $config['db']['host'],
        $config['db']['name'],
        $config['db']['user'],
        $config['db']['pass']
    );
    $db = $database->getConnection();
} catch (Exception $e) {
    // Show install page if database doesn't exist
    $errorMessage = $e->getMessage();
    $errorClass = get_class($e);
    showInstallPage($config['db']['name'], $errorMessage, $errorClass);
    exit;
}

// ============================================================
// INSTANTIATE MODELS AND CONTROLLERS
// ============================================================

$StudentModel = new Student($database);
$EventModel = new Event($database);
$AttendanceModel = new Attendance($database);

$AuthController = new AuthController(
    $StudentModel,
    $config['admin']['user'],
    $config['admin']['pass'],
    $config['app']['email_pattern']
);

$EventController = new EventController($EventModel);
$AttendanceController = new AttendanceController($AttendanceModel, $StudentModel, $EventModel);

// ============================================================
// REQUEST ROUTING
// ============================================================

$page = $_GET['page'] ?? 'login';
$action = $_POST['action'] ?? '';
$ajax = $_GET['ajax'] ?? '';

// Handle AJAX requests first
if ($ajax) {
    header('Content-Type: application/json; charset=utf-8');
    $apiRouter = new ApiRouter($AuthController, $EventController, $AttendanceController, $StudentModel);
    echo $apiRouter->route($ajax);
    exit;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    handlePostAction($action);
}

// Check authentication for protected pages
checkPageAccess($page);

// ============================================================
// PAGE RENDERING
// ============================================================

// Get flash message
$flash = getFlash();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['app']['name']; ?> — <?php echo $config['app']['description']; ?></title>
    <meta name="description" content="<?php echo $config['app']['description']; ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- External Libraries -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>

    
    <!-- Styles -->
    <link rel="stylesheet" href="frontend/css/styles.css">
</head>
<body data-student-id="<?php echo e($_SESSION['user_id'] ?? ''); ?>">
    
    <!-- Flash Message Display -->
    <?php if ($flash): ?>
        <div style="position:fixed;top:1rem;right:1rem;z-index:200;max-width:400px;">
            <div class="flash flash-<?php echo e($flash['type']); ?>">
                <?php echo e($flash['msg']); ?>
            </div>
        </div>
        <script>
            setTimeout(() => {
                const flash = document.querySelector('.flash');
                if (flash) flash.parentElement.remove();
            }, 4000);
        </script>
    <?php endif; ?>
    
    <!-- Page Content -->
    <main>
        <?php
            switch ($page) {
                case 'login':
                    include __DIR__ . '/frontend/pages/login.php';
                    break;
                case 'student':
                    include __DIR__ . '/frontend/pages/student_dashboard.php';
                    break;
                case 'admin':
                case 'admin_events':
                case 'admin_qr_generator':
                case 'admin_qr_scanner':
                case 'admin_attendance':
                case 'admin_archived':
                    include __DIR__ . '/frontend/pages/admin/admin_panel.php';
                    break;
                default:
                    include __DIR__ . '/frontend/pages/login.php';
            }
        ?>
    </main>
    
    <!-- Scripts -->
    <script src="frontend/js/ui.js"></script>
</body>
</html>

<?php

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Handle POST actions (form submissions)
 */
function handlePostAction($action)
{
    global $AuthController, $EventController, $AttendanceController;
    
    switch ($action) {
        // Auth actions
        case 'student_login':
            $result = $AuthController->loginStudent(
                $_POST['email'] ?? '',
                $_POST['password'] ?? ''
            );
            if ($result['success']) {
                header('Location: ' . $result['redirect']);
                exit;
            } else {
                setFlash('error', $result['message']);
            }
            break;
            
        case 'student_register':
            $result = $AuthController->registerStudent(
                $_POST['email'] ?? '',
                $_POST['password'] ?? '',
                $_POST['first_name'] ?? '',
                $_POST['last_name'] ?? '',
                $_POST['course'] ?? '',
                $_POST['year_level'] ?? ''
            );
            if ($result['success']) {
                $_SESSION['user_name'] = ($_POST['first_name'] ?? '') . ' ' . ($_POST['last_name'] ?? '');
                setFlash('success', $result['message']);
                header('Location: ' . $result['redirect']);
                exit;
            } else {
                setFlash('error', $result['message']);
            }
            break;
            
            
        case 'admin_login':
            $result = $AuthController->loginAdmin(
                $_POST['username'] ?? '',
                $_POST['password'] ?? ''
            );
            if ($result['success']) {
                header('Location: ' . $result['redirect']);
                exit;
            } else {
                setFlash('error', $result['message']);
            }
            break;
            
        case 'logout':
            $AuthController->logout();
            header('Location: ?page=login');
            exit;
            
        // Event actions
        case 'add_event':
            requireAdmin();
            $result = $EventController->create(
                $_POST['name'] ?? '',
                $_POST['date'] ?? '',
                $_POST['location'] ?? '',
                $_POST['description'] ?? ''
            );
            setFlash($result['success'] ? 'success' : 'error', $result['message']);
            header('Location: ?page=admin_events');
            exit;
            
        case 'archive_event':
            requireAdmin();
            $result = $EventController->archive($_POST['event_id'] ?? '');
            setFlash($result['success'] ? 'success' : 'error', $result['message']);
            header('Location: ?page=admin_events');
            exit;
            
        // Attendance actions
        case 'record_attendance':
            requireAdmin();
            $result = $AttendanceController->recordManual(
                $_POST['student_id'] ?? '',
                $_POST['event_id'] ?? ''
            );
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
    }
}

/**
 * Check if user has access to requested page
 */
function checkPageAccess($page)
{
    $publicPages = ['login', 'install'];
    $studentPages = ['student'];
    $adminPages = ['admin', 'admin_events', 'admin_qr_generator', 'admin_qr_scanner', 'admin_attendance', 'admin_archived'];
    
    // Public pages - no auth needed
    if (in_array($page, $publicPages)) {
        return;
    }
    
    // Student pages - require student role
    if (in_array($page, $studentPages) && !hasRole('student')) {
        header('Location: ?page=login');
        exit;
    }
    
    // Admin pages - require admin role
    if (in_array($page, $adminPages) && !hasRole('admin')) {
        header('Location: ?page=login');
        exit;
    }
}

/**
 * Show database installation page
 */
function showInstallPage($dbname, $errorMessage = '', $errorClass = '')
{
    $sql = file_get_contents(__DIR__ . '/database/init.sql');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Taptrack Installation</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: system-ui, sans-serif; padding: 2rem; background: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
            h1 { color: #333; margin-bottom: 1rem; }
            p { color: #666; line-height: 1.6; margin-bottom: 1rem; }
            code { background: #f9f9f9; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New', monospace; }
            pre { background: #f9f9f9; padding: 1rem; border-radius: 4px; overflow: auto; border: 1px solid #ddd; margin: 1rem 0; font-size: 0.85rem; max-height: 400px; }
            .error { background: #fee; border: 1px solid #fcc; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; color: #c33; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Taptrack — Database Setup Required</h1>
            <?php if ($errorMessage): ?>
                <div class="error">
                    <strong>Error Details:</strong><br>
                    <code><?php echo htmlspecialchars($errorMessage); ?></code><br>
                    <small>Type: <?php echo htmlspecialchars($errorClass); ?></small>
                </div>
            <?php endif; ?>
            <p>The database <code><?php echo htmlspecialchars($dbname); ?></code> does not exist. Please create it and run the following SQL script:</p>
            <pre><?php echo htmlspecialchars($sql); ?></pre>
            <p>After creating the database and tables, refresh this page.</p>
            <p><strong>Quick Setup Instructions:</strong></p>
            <ol>
                <li>Open phpMyAdmin or your MySQL client</li>
                <li>Create a new database named <code><?php echo htmlspecialchars($dbname); ?></code></li>
                <li>Select the database and import or paste the SQL script above</li>
                <li>Refresh this page</li>
            </ol>
        </div>
    </body>
    </html>
    <?php
}

?>
