<?php
/**
 * ============================================================
 * TAPTRACK — QR Code Attendance System (Refactored)
 * FEU Roosevelt Marikina
 * ============================================================
 *
 * SETUP INSTRUCTIONS:
 * 1. Create a MySQL database called `taptrack`
 * 2. Run the SQL at ?page=install
 * 3. Place this file on any PHP server (XAMPP, WAMP, hosting, etc.)
 * 4. Access it via browser: http://localhost/Taptrack_Attendance/
 *
 * REQUIREMENTS: PHP 7.4+, MySQL/MariaDB, PDO extension
 * ============================================================
 */

// Load configuration
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/RoleBasedAccessControl.php';
require_once __DIR__ . '/modules/handlers.php';

// ======================== ROUTING ========================
$page = $_GET['page'] ?? 'login';
$action = $_POST['action'] ?? '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handleAction($pdo, $action);
}

// Handle API requests
if ($page === 'api_attendance') {
    require __DIR__ . '/pages/admin/api_attendance.php';
    exit;
}

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    handleAjax($pdo, $_GET['ajax']);
    exit;
}

// ======================== AUTH GUARDS ========================
// Student routes - require login and student role
if (in_array($page, ['student'])) {
    if (!isLoggedIn()) { header('Location: ?page=login'); exit; }
    if (!isStudent()) { header('Location: ?page=login'); exit; }
}

// Face registration - require session flag
if ($page === 'face_register' && empty($_SESSION['face_reg_student_id'])) {
    header('Location: ?page=login'); exit;
}

// Admin routes - require login and admin role
$adminPages = ['admin', 'admin_events', 'admin_qr_generator', 'admin_qr_scanner', 'admin_attendance', 'admin_archived', 'admin_users', 'admin_home', 'api_attendance'];
if (in_array($page, $adminPages)) {
    if (!isLoggedIn()) { header('Location: ?page=login'); exit; }
    if (!isAdmin()) requireAdmin(); // This will show the 403 error page
}

// Organizer routes - require login and organizer or admin role
$organizerPages = ['organizer_qr_scanner', 'organizer_attendance', 'organizer_events', 'organizer_qr_generator', 'organizer_archived', 'organizer_home'];
if (in_array($page, $organizerPages)) {
    if (!isLoggedIn()) { header('Location: ?page=login'); exit; }
    if (!isOrganizer() && !isAdmin()) { 
        http_response_code(403);
        echo '<div style="padding:2rem;"><h1>Access Denied</h1><p>You do not have permission to access this page.</p><a href="?page=login">Go to Login</a></div>';
        exit;
    }
}

// Get flash message
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taptrack — QR Attendance System</title>
    <meta name="description" content="QR Code Attendance System for FEU Roosevelt Marikina">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- QRious library for QR code generation (LOCAL) -->
    <script src="assets/js/qrious.min.js"></script>
    <!-- html5-qrcode library for QR code scanning (LOCAL) -->
    <script src="assets/js/html5-qrcode.min.js"></script>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
<?php
// Show flash messages
if ($flash): ?>
    <div style="position:fixed;top:1rem;right:1rem;z-index:200;max-width:400px;">
        <div class="flash flash-<?= $flash['type'] ?>"><?= e($flash['msg']) ?></div>
    </div>
    <script>setTimeout(() => document.querySelector('.flash')?.parentElement?.remove(), 4000);</script>
<?php endif;

// ======================== PAGE ROUTING ========================
switch ($page) {
    case 'login': 
        require __DIR__ . '/pages/login.php'; 
        break;
    
    case 'face_register': 
        require __DIR__ . '/pages/face-register.php'; 
        break;
    
    case 'student': 
        require __DIR__ . '/pages/student-dashboard.php'; 
        break;
    
    case 'admin': 
        renderAdminLayout('home');
        break;
    
    case 'admin_events': 
        renderAdminLayout('events');
        break;
    
    case 'admin_qr_generator': 
        renderAdminLayout('qr_generator');
        break;
    
    case 'admin_qr_scanner': 
        renderAdminLayout('qr_scanner');
        break;
    
    case 'admin_attendance': 
        renderAdminLayout('attendance');
        break;
    
    case 'admin_archived': 
        renderAdminLayout('archived');
        break;
    
    case 'admin_users':
        renderAdminLayout('users');
        break;
    
    case 'organizer':
    case 'organizer_home':
        renderOrganizerLayout('home');
        break;
    
    case 'organizer_events':
        renderOrganizerLayout('events');
        break;
    
    case 'organizer_qr_generator':
        renderOrganizerLayout('qr_generator');
        break;
    
    case 'organizer_qr_scanner':
        renderOrganizerLayout('qr_scanner');
        break;
    
    case 'organizer_attendance':
        renderOrganizerLayout('attendance');
        break;
    
    case 'organizer_archived':
        renderOrganizerLayout('archived');
        break;
    
    case 'install': 
        require_once __DIR__ . '/includes/install.php';
        showInstallPage('localhost', 'taptrack', 'root', '');
        break;
    
    default: 
        require __DIR__ . '/pages/login.php'; 
        break;
}

/**
 * Render Admin Layout with Subpage
 */
function renderAdminLayout($subpage) {
    global $pdo;
    
    $adminPages = [
        'home' => ['Dashboard', 'admin', '🏠'],
        'events' => ['Events', 'admin_events', '📅'],
        'qr_generator' => ['QR Generator', 'admin_qr_generator', '▣'],
        'qr_scanner' => ['QR Scanner', 'admin_qr_scanner', '📷'],
        'attendance' => ['Attendance', 'admin_attendance', '📋'],
        'archived' => ['Archived Events', 'admin_archived', '📦'],
        'users' => ['Users', 'admin_users', '👥'],
    ];
    ?>
    <div class="admin-layout">
        <div class="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon">▣</div>
                <span class="font-bold">Taptrack</span>
            </div>
            <nav class="sidebar-nav">
                <?php foreach ($adminPages as $key => [$label, $link, $icon]): ?>
                    <a href="?page=<?= $link ?>" class="sidebar-link <?= $subpage === $key ? 'active' : '' ?>">
                        <span><?= $icon ?></span>
                        <span><?= $label ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="sidebar-footer">
                <form method="POST"><input type="hidden" name="action" value="logout">
                    <button type="submit" class="sidebar-link w-full" style="border:none;background:none;cursor:pointer;color:var(--sidebar-fg);font-size:0.875rem;">⬅ <span>Logout</span></button>
                </form>
            </div>
        </div>
        <div class="admin-main">
            <div class="admin-header">
                <span class="text-sm font-medium text-muted">Admin Panel</span>
            </div>
            <div class="admin-content">
                <?php
                $adminPageMap = [
                    'home' => 'pages/admin/home.php',
                    'events' => 'pages/admin/events.php',
                    'qr_generator' => 'pages/admin/qr-generator.php',
                    'qr_scanner' => 'pages/admin/qr-scanner.php',
                    'attendance' => 'pages/admin/attendance.php',
                    'archived' => 'pages/admin/archived.php',
                    'users' => 'pages/admin/users.php',
                ];
                
                if (isset($adminPageMap[$subpage])) {
                    require __DIR__ . '/' . $adminPageMap[$subpage];
                }
                ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render Organizer Layout with Subpage
 * Same as admin but without user management section
 */
function renderOrganizerLayout($subpage) {
    global $pdo;
    
    $organizerPages = [
        'home' => ['Dashboard', 'organizer_home', '🏠'],
        'events' => ['Events', 'organizer_events', '📅'],
        'qr_generator' => ['QR Generator', 'organizer_qr_generator', '▣'],
        'qr_scanner' => ['QR Scanner', 'organizer_qr_scanner', '📷'],
        'attendance' => ['Attendance', 'organizer_attendance', '📋'],
        'archived' => ['Archived Events', 'organizer_archived', '📦'],
    ];
    ?>
    <div class="admin-layout">
        <div class="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon">▣</div>
                <span class="font-bold">Taptrack</span>
            </div>
            <nav class="sidebar-nav">
                <?php foreach ($organizerPages as $key => [$label, $link, $icon]): ?>
                    <a href="?page=<?= $link ?>" class="sidebar-link <?= $subpage === $key ? 'active' : '' ?>">
                        <span><?= $icon ?></span>
                        <span><?= $label ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="sidebar-footer">
                <form method="POST"><input type="hidden" name="action" value="logout">
                    <button type="submit" class="sidebar-link w-full" style="border:none;background:none;cursor:pointer;color:var(--sidebar-fg);font-size:0.875rem;">⬅ <span>Logout</span></button>
                </form>
            </div>
        </div>
        <div class="admin-main">
            <div class="admin-header">
                <span class="text-sm font-medium text-muted">Organizer Portal</span>
            </div>
            <div class="admin-content">
                <?php
                $organizerPageMap = [
                    'home' => 'pages/admin/home.php',
                    'events' => 'pages/admin/events.php',
                    'qr_generator' => 'pages/admin/qr-generator.php',
                    'qr_scanner' => 'pages/admin/qr-scanner.php',
                    'attendance' => 'pages/admin/attendance.php',
                    'archived' => 'pages/admin/archived.php',
                ];
                
                if (isset($organizerPageMap[$subpage])) {
                    require __DIR__ . '/' . $organizerPageMap[$subpage];
                }
                ?>
            </div>
        </div>
    </div>
    <?php
}
?>
</body>
</html>

<!-- Verify libraries are loaded -->
<script>
    console.log('[INIT] Checking library availability...');
    console.log('[INIT] QRious available:', typeof QRious !== 'undefined' ? '✓ YES' : '❌ NO');
    console.log('[INIT] Html5Qrcode available:', typeof Html5Qrcode !== 'undefined' ? '✓ YES' : '❌ NO');
    if (typeof QRious === 'undefined') {
        console.error('❌ QRious library failed to load! Check qrious.min.js');
    }
    if (typeof Html5Qrcode === 'undefined') {
        console.error('❌ Html5Qrcode library failed to load! Check html5-qrcode.min.js');
    }
</script>

<script src="assets/js/main.js"></script>
