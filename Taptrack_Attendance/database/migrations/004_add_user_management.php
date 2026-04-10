<?php
/**
 * Migration 004: User Management System with Role-Based Access Control
 * 
 * Creates tables for:
 * - Users with roles
 * - Roles (Admin, Organizer)
 * - Permissions for fine-grained control
 * - User-Permission mappings
 */

function runUserManagementMigration($pdo) {
    try {
        // Create roles table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS roles (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) UNIQUE NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Insert default roles if not exist
        $stmt = $pdo->query("SELECT COUNT(*) FROM roles");
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("
                INSERT INTO roles (name, description) VALUES 
                ('admin', 'Full system access and user management'),
                ('organizer', 'Limited access to event and attendance management')
            ");
        }

        // Create permissions table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS permissions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) UNIQUE NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Insert default permissions if not exist
        $stmt = $pdo->query("SELECT COUNT(*) FROM permissions");
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("
                INSERT INTO permissions (name, description) VALUES 
                -- Admin-only permissions
                ('manage_users', 'Create, edit, and delete user accounts'),
                ('manage_roles', 'Assign and modify user roles'),
                ('manage_permissions', 'Configure system permissions'),
                ('manage_all_events', 'Create, edit, archive, and delete events (Admin only)'),
                
                -- Shared permissions
                ('view_all_events', 'View all events in system'),
                ('view_all_attendance', 'View all attendance records'),
                ('manage_all_attendance', 'Edit and delete attendance records'),
                
                -- Organizer permissions
                ('manage_attendance', 'Record attendance via QR scanning during events'),
                ('generate_qr_codes', 'Generate or access QR codes for attendance scanning')
            ");
        }

        // Create role_permissions junction table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS role_permissions (
                role_id INT NOT NULL,
                permission_id INT NOT NULL,
                PRIMARY KEY (role_id, permission_id),
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
                FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
            )
        ");

        // Assign permissions to roles if not already done
        $stmt = $pdo->query("SELECT COUNT(*) FROM role_permissions");
        if ($stmt->fetchColumn() == 0) {
            // Get role IDs
            $adminRole = $pdo->query("SELECT id FROM roles WHERE name = 'admin'")->fetch()['id'];
            $organizerRole = $pdo->query("SELECT id FROM roles WHERE name = 'organizer'")->fetch()['id'];

            // Get permission IDs
            $permissions = [];
            foreach ($pdo->query("SELECT id, name FROM permissions") as $row) {
                $permissions[$row['name']] = $row['id'];
            }

            // Assign all permissions to Admin
            $adminPerms = [
                'manage_users', 'manage_roles', 'manage_permissions',
                'manage_all_events', 'view_all_events',
                'view_all_attendance', 'manage_all_attendance'
            ];

            foreach ($adminPerms as $perm) {
                if (isset($permissions[$perm])) {
                    $stmt = $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                    $stmt->execute([$adminRole, $permissions[$perm]]);
                }
            }

            // Assign limited permissions to Organizer (Attendance operations only)
            $organizerPerms = [
                'view_all_events',        // View events created by admin
                'view_all_attendance',    // View attendance records
                'manage_attendance',      // Record attendance via QR scanning
                'generate_qr_codes'       // Generate/access QR codes for scanning
            ];

            foreach ($organizerPerms as $perm) {
                if (isset($permissions[$perm])) {
                    $stmt = $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                    $stmt->execute([$organizerRole, $permissions[$perm]]);
                }
            }
        }

        // Create users table (if not exists, or alter if needed)
        $checkUsersTable = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($checkUsersTable->rowCount() == 0) {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    username VARCHAR(100) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    email VARCHAR(100),
                    first_name VARCHAR(100),
                    last_name VARCHAR(100),
                    role_id INT NOT NULL,
                    is_active TINYINT(1) DEFAULT 1,
                    created_by INT NULL DEFAULT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (role_id) REFERENCES roles(id),
                    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
                )
            ");
        } else {
            // Alter existing users table if needed
            $columns = $pdo->query("SHOW COLUMNS FROM users WHERE Field = 'role_id'");
            if ($columns->rowCount() == 0) {
                $pdo->exec("ALTER TABLE users ADD COLUMN role_id INT DEFAULT 1 AFTER password");
                $pdo->exec("ALTER TABLE users ADD FOREIGN KEY (role_id) REFERENCES roles(id)");
            }

            // Check if created_by column exists
            $columns = $pdo->query("SHOW COLUMNS FROM users WHERE Field = 'created_by'");
            if ($columns->rowCount() == 0) {
                $pdo->exec("ALTER TABLE users ADD COLUMN created_by INT AFTER is_active");
                $pdo->exec("ALTER TABLE users ADD FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL");
            }
        }

        // Enhance attendance table to track organizer reference
        $columns = $pdo->query("SHOW COLUMNS FROM attendance WHERE Field = 'recorded_by_user_id'");
        if ($columns->rowCount() == 0) {
            $pdo->exec("ALTER TABLE attendance ADD COLUMN recorded_by_user_id INT AFTER scanned_at");
            $pdo->exec("ALTER TABLE attendance ADD COLUMN recorded_by_name VARCHAR(100) AFTER recorded_by_user_id");
            $pdo->exec("ALTER TABLE attendance ADD FOREIGN KEY (recorded_by_user_id) REFERENCES users(id) ON DELETE SET NULL");
        }

        return true;
    } catch (Exception $e) {
        error_log("User Management Migration Error: " . $e->getMessage());
        return false;
    }
}

// Execute migration
if (!function_exists('runUserManagementMigration')) {
    function runUserManagementMigration($pdo) {
        return true;
    }
}
?>
