<?php
/**
 * Helper Functions
 */

function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
}

/**
 * Check if user is student
 * @return bool
 */
function isStudent() {
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'student';
}

/**
 * Check if user is organizer
 * @return bool
 */
function isOrganizer() {
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'organizer';
}

/**
 * Require user to be logged in
 * Redirects to login if not authenticated
 * @return void
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Please log in to continue.'];
        header('Location: ?page=login');
        exit;
    }
}

/**
 * Require user to be admin
 * Shows 403 unauthorized page if user is not admin
 * @return void
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        echo '<!DOCTYPE html><html><head><title>Unauthorized</title><style>
        body { font-family: Arial, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #f5f5f5; }
        .error-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        h1 { color: #d32f2f; margin: 0; }
        p { color: #666; margin: 10px 0 20px 0; }
        a { color: #1976d2; text-decoration: none; }
        </style></head><body>
        <div class="error-box">
            <h1>🛑 Access Denied</h1>
            <p>You do not have permission to access this page.</p>
            <a href="?page=login">Return to Login</a>
        </div>
        </body></html>';
        exit;
    }
}

/**
 * Require user to be student
 * Shows 403 unauthorized page if user is not student
 * @return void
 */
function requireStudent() {
    requireLogin();
    if (!isStudent()) {
        http_response_code(403);
        echo '<!DOCTYPE html><html><head><title>Unauthorized</title><style>
        body { font-family: Arial, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #f5f5f5; }
        .error-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        h1 { color: #d32f2f; margin: 0; }
        p { color: #666; margin: 10px 0 20px 0; }
        a { color: #1976d2; text-decoration: none; }
        </style></head><body>
        <div class="error-box">
            <h1>🛑 Access Denied</h1>
            <p>You do not have permission to access this page.</p>
            <a href="?page=login">Return to Login</a>
        </div>
        </body></html>';
        exit;
    }
}

/**
 * Get current user info
 * @return array|null User array with id, role, name or null if not logged in
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'role' => $_SESSION['user_role'],
        'name' => $_SESSION['user_name'] ?? 'User'
    ];
}

/**
 * Safely redirect after authentication check
 * @param string $page
 * @return void
 */
function redirectAfterAuth($page) {
    header("Location: ?page=$page");
    exit;
}

function e($str) { 
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); 
}

function getFlash() {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
