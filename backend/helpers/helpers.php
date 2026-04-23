<?php
/**
 * ============================================================
 * Helper Functions
 * ============================================================
 * Utility functions used throughout the application
 */

/**
 * Generate a UUID v4
 * 
 * @return string UUID in format xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
 */
function generateUUID()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

/**
 * HTML escape output
 * Prevents XSS attacks
 * 
 * @param mixed $str String to escape
 * @return string Escaped HTML
 */
function e($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Get and clear flash message from session
 * 
 * @return array|null Flash message with 'type' and 'msg' keys, or null
 */
function getFlash()
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Set flash message in session
 * 
 * @param string $type 'success', 'error', 'warning', 'info'
 * @param string $message Flash message text
 */
function setFlash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $message];
}

/**
 * Check if user is authenticated
 * 
 * @return bool True if user is logged in
 */
function isAuthenticated()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has specific role
 * 
 * @param string $role Role to check ('student', 'admin')
 * @return bool True if user has role
 */
function hasRole($role)
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Require admin role, redirect to login if not authorized
 */
function requireAdmin()
{
    if (!hasRole('admin')) {
        header('Location: ?page=login');
        exit;
    }
}

/**
 * Require student role, redirect to login if not authorized
 */
function requireStudent()
{
    if (!hasRole('student')) {
        header('Location: ?page=login');
        exit;
    }
}

/**
 * Require authentication, redirect to login if not logged in
 */
function requireAuth()
{
    if (!isAuthenticated()) {
        header('Location: ?page=login');
        exit;
    }
}

/**
 * Format date string in readable format
 * 
 * @param string|null $date Date string
 * @param string $format PHP date format
 * @return string Formatted date or empty string
 */
function formatDate($date, $format = 'M j, Y')
{
    if (!$date) {
        return '';
    }
    try {
        return date($format, strtotime($date));
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Format timestamp in readable format
 * 
 * @param string|null $timestamp Timestamp string
 * @return string Formatted datetime
 */
function formatTimestamp($timestamp)
{
    if (!$timestamp) {
        return '';
    }
    return date('M j, Y g:i A', strtotime($timestamp));
}

/**
 * Get client IP address
 * 
 * @return string Client IP address
 */
function getClientIP()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}

/**
 * Validate FEU email format
 * 
 * @param string $email Email to validate
 * @param string $pattern Regex pattern from config
 * @return bool True if valid
 */
function isValidFEUEmail($email, $pattern = null)
{
    $pattern = $pattern ?? '/^R\d{8,}@feuroosevelt\.edu\.ph$/i';
    return (bool) preg_match($pattern, trim($email ?? ''));
}

/**
 * Extract student number from email
 * 
 * @param string $email FEU email address
 * @return string Student number (R-number part)
 */
function extractStudentNumber($email)
{
    return explode('@', trim($email))[0];
}

/**
 * Hash password (for future use with bcrypt)
 * 
 * @param string $password Plain password
 * @return string Hashed password
 */
function hashPassword($password)
{
    // For now, plain text (not recommended for production)
    // TODO: Implement bcrypt or use password_hash()
    return $password;
}

/**
 * Verify password
 * 
 * @param string $plain Plain password from input
 * @param string $hash Stored hash
 * @return bool True if password matches
 */
function verifyPassword($plain, $hash)
{
    // For now, plain text comparison
    // TODO: Implement bcrypt verification
    return $plain === $hash;
}

/**
 * Redirect with flash message
 * 
 * @param string $location URL to redirect to
 * @param string $type Flash message type
 * @param string $message Flash message text
 */
function redirectWithFlash($location, $type, $message)
{
    setFlash($type, $message);
    header("Location: $location");
    exit;
}

/**
 * Convert array to JSON safe response
 * 
 * @param array $data Data to encode
 * @return string JSON string
 */
function jsonResponse($data)
{
    header('Content-Type: application/json; charset=utf-8');
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * Check if request is AJAX
 * 
 * @return bool True if request is AJAX
 */
function isAjax()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get JSON request body
 * 
 * @return array|null Decoded JSON or null
 */
function getJsonInput()
{
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}
