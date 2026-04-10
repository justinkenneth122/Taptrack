<?php
/**
 * Role-Based Access Control (RBAC) System
 * 
 * Handles role and permission checks for the application
 */

class Authorization {
    private $pdo;
    private $userId;
    private $userRole;
    private $userPermissions = [];

    public function __construct($pdo, $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->loadUserRole();
        $this->loadUserPermissions();
    }

    /**
     * Load user's role
     */
    private function loadUserRole() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT r.id, r.name FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.id = ?
            ");
            $stmt->execute([$this->userId]);
            $result = $stmt->fetch();
            
            if ($result) {
                $this->userRole = $result['name'];
            }
        } catch (Exception $e) {
            error_log("Error loading user role: " . $e->getMessage());
        }
    }

    /**
     * Load user's permissions
     */
    private function loadUserPermissions() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.name FROM users u
                JOIN roles r ON u.role_id = r.id
                JOIN role_permissions rp ON r.id = rp.role_id
                JOIN permissions p ON rp.permission_id = p.id
                WHERE u.id = ?
            ");
            $stmt->execute([$this->userId]);
            $results = $stmt->fetchAll();
            
            foreach ($results as $row) {
                $this->userPermissions[] = $row['name'];
            }
        } catch (Exception $e) {
            error_log("Error loading user permissions: " . $e->getMessage());
        }
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission($permission) {
        return in_array($permission, $this->userPermissions);
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission($permissions = []) {
        foreach ($permissions as $permission) {
            if (in_array($permission, $this->userPermissions)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions($permissions = []) {
        foreach ($permissions as $permission) {
            if (!in_array($permission, $this->userPermissions)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($role) {
        return $this->userRole === $role;
    }

    /**
     * Get user's role
     */
    public function getRole() {
        return $this->userRole;
    }

    /**
     * Get all user's permissions
     */
    public function getPermissions() {
        return $this->userPermissions;
    }

    /**
     * Check if user is Admin
     */
    public function isAdmin() {
        return $this->userRole === 'admin';
    }

    /**
     * Check if user is Organizer
     */
    public function isOrganizer() {
        return $this->userRole === 'organizer';
    }

    /**
     * ========== COMMON PERMISSION CHECKS ==========
     * These methods provide easy access to frequently checked permissions
     */

    /**
     * Check if user can manage users (admin only)
     */
    public function canManageUsers() {
        return $this->hasPermission('manage_users');
    }

    /**
     * Check if user can manage events (admin only)
     */
    public function canManageEvents() {
        return $this->hasPermission('manage_all_events');
    }

    /**
     * Check if user can view events
     */
    public function canViewEvents() {
        return $this->hasPermission('view_all_events');
    }

    /**
     * Check if user can record attendance (admin and organizer)
     */
    public function canRecordAttendance() {
        return $this->hasPermission('manage_attendance');
    }

    /**
     * Check if user can generate/access QR codes (admin and organizer)
     */
    public function canGenerateQRCodes() {
        return $this->hasPermission('generate_qr_codes');
    }

    /**
     * Check if user can view attendance records
     */
    public function canViewAttendance() {
        return $this->hasPermission('view_all_attendance');
    }

    /**
     * Check if user can manage/edit attendance records
     */
    public function canManageAttendance() {
        return $this->hasPermission('manage_all_attendance');
    }

    /**
     * Check if user can manage roles (admin only)
     */
    public function canManageRoles() {
        return $this->hasPermission('manage_roles');
    }

    /**
     * Check if user can manage permissions (admin only)
     */
    public function canManagePermissions() {
        return $this->hasPermission('manage_permissions');
    }
}

/**
 * Helper class for user authentication
 */
class UserAuthentication {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Create a new user account (Admin only)
     */
    public function createUser($username, $password, $email, $firstName, $lastName, $roleId, $createdById) {
        try {
            // Validate inputs
            if (empty($username) || empty($password) || empty($email)) {
                return ['success' => false, 'message' => 'Username, password, and email are required'];
            }

            if (strlen($password) < 6) {
                return ['success' => false, 'message' => 'Password must be at least 6 characters'];
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }

            // Check if username already exists
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Username already exists'];
            }

            // Check if email already exists
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Email already registered'];
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Validate that createdById exists in users table if provided
            if (!empty($createdById)) {
                $stmt = $this->pdo->prepare("SELECT id FROM users WHERE id = ?");
                $stmt->execute([$createdById]);
                if (!$stmt->fetch()) {
                    // Creator ID doesn't exist, set to NULL (for first user creation)
                    $createdById = null;
                }
            } else {
                $createdById = null;
            }

            // Insert user
            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, password, email, first_name, last_name, role_id, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$username, $hashedPassword, $email, $firstName, $lastName, $roleId, $createdById]);

            return ['success' => true, 'message' => 'User created successfully', 'user_id' => $this->pdo->lastInsertId()];
        } catch (Exception $e) {
            error_log("Error creating user: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error creating user: ' . $e->getMessage()];
        }
    }

    /**
     * Authenticate user (for login)
     */
    public function authenticateUser($username, $password) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.id, u.username, u.password, u.email, u.is_active, r.name as role
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.username = ? AND u.is_active = 1
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['success' => false, 'message' => 'Invalid username or password'];
            }

            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid username or password'];
            }

            return ['success' => true, 'user_id' => $user['id'], 'username' => $user['username'], 'role' => $user['role']];
        } catch (Exception $e) {
            error_log("Error authenticating user: " . $e->getMessage());
            return ['success' => false, 'message' => 'Authentication error'];
        }
    }

    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.*, r.name as role_name
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getting user: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update user details
     */
    public function updateUser($userId, $email, $firstName, $lastName) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE users
                SET email = ?, first_name = ?, last_name = ?
                WHERE id = ?
            ");
            $stmt->execute([$email, $firstName, $lastName, $userId]);
            return ['success' => true, 'message' => 'User updated successfully'];
        } catch (Exception $e) {
            error_log("Error updating user: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating user'];
        }
    }

    /**
     * Change user password
     */
    public function changePassword($userId, $oldPassword, $newPassword) {
        try {
            // Get user's current password
            $stmt = $this->pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($oldPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);

            return ['success' => true, 'message' => 'Password changed successfully'];
        } catch (Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error changing password'];
        }
    }

    /**
     * Delete user (deactivate or remove)
     */
    public function deleteUser($userId) {
        try {
            // Check if trying to delete admin (should only be allowed by other admins)
            $stmt = $this->pdo->prepare("SELECT role_id FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            // Delete user
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);

            return ['success' => true, 'message' => 'User deleted successfully'];
        } catch (Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error deleting user'];
        }
    }

    /**
     * Get all users with their roles
     */
    public function getAllUsers($limit = null, $offset = null) {
        try {
            $query = "
                SELECT u.id, u.username, u.email, u.first_name, u.last_name, 
                       r.name as role_name, u.is_active, u.created_at,
                       creator.username as created_by_username
                FROM users u
                JOIN roles r ON u.role_id = r.id
                LEFT JOIN users creator ON u.created_by = creator.id
                ORDER BY u.created_at DESC
            ";

            if ($limit && $offset !== null) {
                $query .= " LIMIT ? OFFSET ?";
                $stmt = $this->pdo->prepare($query);
                $stmt->execute([$limit, $offset]);
            } else {
                $stmt = $this->pdo->prepare($query);
                $stmt->execute();
            }

            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total users
     */
    public function countUsers() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM users");
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error counting users: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get all organizers
     */
    public function getAllOrganizers() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.is_active, u.created_at
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE r.name = 'organizer'
                ORDER BY u.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting organizers: " . $e->getMessage());
            return [];
        }
    }
}
?>
