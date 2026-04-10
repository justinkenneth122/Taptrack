# User Management System - Comprehensive Documentation

## 📋 Overview

The User Management System is a **role-based access control (RBAC)** system that provides:

- **Admin Role**: Full system access and user management
- **Organizer Role**: Limited access to event and attendance management
- **Fine-grained Permissions**: Control over specific features
- **Data Preservation**: Attendance records remain intact after organizer deletion

---

## 🔐 System Architecture

### Database Schema

#### `roles` Table
```sql
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Default Roles:**
- `admin`: Full system control
- `organizer`: Limited event/attendance management

---

#### `permissions` Table
```sql
CREATE TABLE permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Default Permissions:**

| Permission | Role | Description |
|-----------|------|-------------|
| `manage_users` | Admin | Create, edit, delete users |
| `manage_roles` | Admin | Assign/modify roles |
| `manage_permissions` | Admin | Configure permissions |
| `view_all_events` | Admin | View all events |
| `manage_all_events` | Admin | CRUD events |
| `view_all_attendance` | Admin | View all attendance |
| `manage_all_attendance` | Admin | Edit/delete attendance |
| `view_assigned_events` | Organizer | View assigned events |
| `manage_attendance` | Organizer | Handle attendance |
| `view_event_participants` | Organizer | View participants |
| `export_attendance` | Organizer | Export records |

---

#### `role_permissions` Table
```sql
CREATE TABLE role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id)
);
```

---

#### `users` Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    role_id INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
```

**Key Columns:**
- `role_id`: References the user's role
- `created_by`: Tracks which admin created this user
- `is_active`: Soft-delete flag for deactivating users

---

#### `attendance` Table (Enhanced)
```sql
ALTER TABLE attendance ADD COLUMN recorded_by_user_id INT AFTER scanned_at;
ALTER TABLE attendance ADD COLUMN recorded_by_name VARCHAR(100) AFTER recorded_by_user_id;
ALTER TABLE attendance ADD FOREIGN KEY (recorded_by_user_id) REFERENCES users(id) ON DELETE SET NULL;
```

**Data Preservation Logic:**
- When a user is deleted, `recorded_by_user_id` becomes NULL
- `recorded_by_name` is **permanently preserved** (stores the name at time of recording)
- Attendance records remain visible and searchable

---

## 👥 User Management Features

### Admin Panel - User Management (`/admin/users`)

#### 1. View All Users
- Display table of all users
- Shows: Username, Name, Email, Role, Status, Created Date
- Quick actions: Edit, Delete

#### 2. Create New User
```php
$auth = new UserAuthentication($pdo);
$result = $auth->createUser(
    $username,
    $password,
    $email,
    $firstName,
    $lastName,
    $roleId,
    $adminUserId  // Who created this user
);
```

**Features:**
- Username uniqueness validation
- Password hashing with `password_hash()`
- Email required
- Role assignment (Admin or Organizer)
- Tracks who created the user

#### 3. Edit User Details
```php
$result = $auth->updateUser(
    $userId,
    $email,
    $firstName,
    $lastName
);
```

**Note:** Cannot edit username or role through this interface (for security)

#### 4. Delete User
```php
$result = $auth->deleteUser($userId);
```

**Important:**
- User is completely removed from `users` table
- User attendance records are **preserved** because:
  - `recorded_by_user_id` becomes NULL (foreign key ON DELETE SET NULL)
  - `recorded_by_name` retains the user's name (permanent snapshot)
- User cannot delete their own account

---

## 🔑 Authorization & Permissions

### Authorization Class
```php
$authorization = new Authorization($pdo, $userId);

// Check permissions
if ($authorization->hasPermission('manage_users')) {
    // Allow action
}

// Check roles
if ($authorization->isAdmin()) {
    // Admin-only logic
}

if ($authorization->isOrganizer()) {
    // Organizer-only logic
}
```

### Permission Checks in Views
```php
// In admin pages
if (!isLoggedIn()) { 
    header('Location: ?page=login'); 
    exit; 
}

if (!isAdmin()) { 
    echo '<div class="card">Access Denied</div>';
    return;
}
```

---

## 🔐 Admin Role Responsibilities

### Capabilities
✅ Create new Organizer accounts  
✅ Edit organizer details  
✅ Delete organizer accounts  
✅ View all users in system  
✅ Manage system permissions  
✅ Access all events and attendance records  
✅ Export attendance data  

### Account Management
```php
// Get all organizers
$organizers = $auth->getAllOrganizers();

// Get specific organizer
$user = $auth->getUserById($organizerId);

// Create temporary organizer for event
$result = $auth->createUser(
    'student_volunteer_1',
    'temp_password_123',
    'student@feu.edu.ph',
    'Student',
    'Volunteer',
    2,  // Organizer role ID
    $_SESSION['admin_user_id']
);
```

---

## 👤 Organizer Role Responsibilities

### Capabilities
✅ View assigned events  
✅ Manage attendance for events  
✅ View event participants  
✅ Export attendance records  

### Limitations
❌ Cannot create new users  
❌ Cannot delete users  
❌ Cannot access other organizers' data  
❌ Cannot manage system settings  
❌ Cannot view all events (only assigned)  

---

## 📊 Attendance Data Integrity

### Scenario: Organizer Deletion

**Before Deletion:**
```sql
-- attendance record
| id | student_id | event_id | recorded_by_user_id | recorded_by_name        |
|----|------------|----------|---------------------|------------------------|
| 1  | 5          | 2        | 10                  | "John Doe (Organizer)" |
```

**After Deleting User ID 10:**
```sql
-- attendance record (UNCHANGED)
| id | student_id | event_id | recorded_by_user_id | recorded_by_name        |
|----|------------|----------|---------------------|------------------------|
| 1  | 5          | 2        | NULL                | "John Doe (Organizer)" |
```

**Result:**
✅ Attendance record is preserved  
✅ Name snapshot is preserved  
✅ Admin can still view attendance  
✅ Reports include attendance data  
✅ No data loss occurs  

---

## 🚀 Implementation Guide

### Step 1: Migration Runs Automatically
```php
// In config/database.php
require_once __DIR__ . '/../database/migrations/004_add_user_management.php';
$result = runUserManagementMigration($pdo);
```

### Step 2: Access User Management
1. Login as Admin
2. Navigate to **Admin Panel** → **Users** (👥)
3. Create/Edit/Delete users as needed

### Step 3: Create Organizer Accounts
```php
// In pages/admin/users.php
// Admin clicks "Create User"
// Fills form with:
//   - Username: volunteer_event_1
//   - Email: volunteer@event.edu
//   - First Name: Temp
//   - Last Name: Volunteer
//   - Role: Organizer
// Clicks "Create User"
```

### Step 4: Assign to Events
- Organizer logs in with their credentials
- Can only see/manage assigned events
- Can record attendance for those events

### Step 5: Temporary Account Cleanup
```php
// When event is finished:
// Admin goes to Users page
// Clicks "Delete" next to volunteer account
// Attendance records are preserved automatically
```

---

## 🔒 Security Features

### Password Security
- Passwords hashed with `password_hash()` (PHP default)
- Password verification with `password_verify()`
- Minimum 6 characters required

### SQL Injection Prevention
- All queries use prepared statements
- Parameter binding throughout
- No dynamic SQL concatenation

### Access Control
- Session-based authentication
- Role/permission checks on every admin page
- Users can only be created by admins

### Data Integrity
- Foreign key constraints enforce relationships
- Cascading deletes where appropriate (roles→permissions)
- ON DELETE SET NULL for user references (preserves data)

---

## 📝 API Reference

### UserAuthentication Class

#### `createUser($username, $password, $email, $firstName, $lastName, $roleId, $createdById)`
Creates a new user account

```php
$result = $auth->createUser('john_doe', 'password123', 'john@example.com', 'John', 'Doe', 2, $_SESSION['user_id']);
// Returns: ['success' => true/false, 'message' => '...', 'user_id' => '...']
```

#### `authenticateUser($username, $password)`
Authenticates user for login

```php
$result = $auth->authenticateUser('john_doe', 'password123');
// Returns: ['success' => true/false, 'user_id' => '...', 'role' => '...']
```

#### `getUserById($userId)`
Retrieves user information

```php
$user = $auth->getUserById($userId);
// Returns: user record with all details
```

#### `updateUser($userId, $email, $firstName, $lastName)`
Updates user details

```php
$result = $auth->updateUser($userId, 'newemail@example.com', 'Jane', 'Doe');
// Returns: ['success' => true/false, 'message' => '...']
```

#### `changePassword($userId, $oldPassword, $newPassword)`
Changes user password

```php
$result = $auth->changePassword($userId, 'oldpass123', 'newpass456');
// Returns: ['success' => true/false, 'message' => '...']
```

#### `deleteUser($userId)`
Deletes a user account

```php
$result = $auth->deleteUser($userId);
// Returns: ['success' => true/false, 'message' => '...']
// Note: Attendance records and recorded_by_name are preserved
```

#### `getAllUsers($limit = null, $offset = null)`
Gets all users (with pagination)

```php
$users = $auth->getAllUsers(10, 0);  // Get 10 users, starting from offset 0
// Returns: array of user records
```

#### `getAllOrganizers()`
Gets all organizer accounts

```php
$organizers = $auth->getAllOrganizers();
// Returns: array of organizer records
```

---

### Authorization Class

#### `hasPermission($permission)`
Check if user has specific permission

```php
if ($authorization->hasPermission('manage_users')) {
    // Allow user management
}
```

#### `hasRole($role)`
Check if user has specific role

```php
if ($authorization->hasRole('admin')) {
    // Admin-only logic
}
```

#### `isAdmin()` / `isOrganizer()`
Quick role checks

```php
if ($authorization->isAdmin()) { ... }
if ($authorization->isOrganizer()) { ... }
```

#### `getPermissions()`
Get all user permissions

```php
$permissions = $authorization->getPermissions();
// Returns: ['manage_users', 'view_all_events', ...]
```

---

## 🎯 Common Use Cases

### Use Case 1: Create Temporary Event Organizer
```php
// Admin creates a student volunteer for event X
$auth->createUser(
    'student_vol_event5',           // username
    generateRandomPassword(),        // secure random password given to student
    'student@feu.edu.ph',           // email
    'Maria',                        // first name
    'Santos',                       // last name
    2,                              // Organizer role
    $_SESSION['user_id']            // created by current admin
);

// Student uses this account during the event to record attendance
// After event: Admin deletes the account
// Attendance records remain preserved with recorded_by_name intact
```

### Use Case 2: Permanent Organizer Account
```php
// Admin creates permanent staff organizer
$auth->createUser(
    'staff_organizer_1',
    'securepassword123',
    'staff.organizer@feu.edu.ph',
    'Juan',
    'Dela Cruz',
    2,              // Organizer role
    $_SESSION['user_id']
);

// Staff can manage events and attendance indefinitely
// Permission system restricts access to assigned events only
```

### Use Case 3: View Organizer's Recorded Attendance
```php
// In attendance records query
SELECT a.*, 
       s.first_name, s.last_name,
       a.recorded_by_name          -- Name snapshot from when recorded
FROM attendance a
JOIN students s ON a.student_id = s.id
WHERE a.event_id = ? 
      AND a.recorded_by_name LIKE '%Maria%';  -- Find records by this organizer

// Result: Even if Maria's account is deleted, this query still works
```

---

## 🐛 Troubleshooting

### Issue: "Access Denied" message on User Management page
**Solution:** Ensure you're logged in as an Admin. Organizers cannot access user management.

### Issue: User cannot delete their own account
**Solution:** By design, admins must ask another admin to delete their account. This prevents accidental lockout.

### Issue: Attendance records show "NULL" for recorded_by_user_id
**Solution:** This is normal and expected. The organizer who recorded it was deleted. The `recorded_by_name` field still shows who recorded it.

### Issue: Password reset not working
**Solution:** Use the `changePassword()` method to reset. Admin cannot force reset the password (security feature).

---

## 📚 Related Files

| File | Purpose |
|------|---------|
| `database/migrations/004_add_user_management.php` | Database schema creation |
| `includes/RoleBasedAccessControl.php` | RBAC classes (Authorization, UserAuthentication) |
| `pages/admin/users.php` | User management interface |
| `index.php` | Routing and access control |
| `config/database.php` | Migration runner |

---

## ✅ Feature Checklist

- ✅ Admin can create Organizer accounts
- ✅ Admin can edit user details
- ✅ Admin can delete user accounts
- ✅ Organizers cannot create users
- ✅ Organizers have limited permissions
- ✅ Attendance records preserved after organizer deletion
- ✅ Organizer name preserved in attendance records
- ✅ Role-based access control enforced
- ✅ Password security implemented
- ✅ SQL injection prevention
- ✅ Fine-grained permissions system
- ✅ User creation tracking (created_by field)

---

## 🔄 Next Steps

1. **Hard refresh** the application: `Ctrl + Shift + R`
2. **Login as Admin** with default admin credentials
3. **Navigate to Admin Panel** → **Users** (👥 icon)
4. **Create an Organizer account** for testing
5. **Test** attendance recording with the new organizer
6. **Delete** the organizer and verify attendance remains

---

**Last Updated:** April 9, 2026  
**Version:** 2.0 (User Management System)  
**Status:** ✅ Production Ready
