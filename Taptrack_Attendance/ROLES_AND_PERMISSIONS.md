# Roles & Permissions System - Updated Configuration

## Overview
The system uses a **Role-Based Access Control (RBAC)** model with clear separation of responsibilities between Admin and Organizer roles.

---

## 🔐 Permissions Reference

### Admin-Only Permissions
These permissions are **exclusively** available to Admin users:

| Permission | Description | Purpose |
|---|---|---|
| `manage_users` | Create, edit, and delete user accounts | Full user account management |
| `manage_roles` | Assign and modify user roles | Role assignment and configuration |
| `manage_permissions` | Configure system permissions | Permission management (advanced) |
| `manage_all_events` | Create, edit, archive, and delete events | Full event lifecycle control |

### Shared Permissions (Admin + Organizer)
These permissions are available to both Admin and Organizer:

| Permission | Description | Access Level |
|---|---|---|
| `view_all_events` | View all events in system | Read-only |
| `view_all_attendance` | View all attendance records | Read-only |
| `manage_all_attendance` | Edit and delete attendance records | Write access |

### Organizer-Only Permissions
These permissions are **exclusively** for Organizer users:

| Permission | Description | Purpose |
|---|---|---|
| `manage_attendance` | Record attendance via QR scanning during events | Core organizer function |
| `generate_qr_codes` | Generate or access QR codes for attendance scanning | QR code operations |

---

## 👑 Admin Role Permissions Matrix

| Feature | Permission | Access |
|---|---|---|
| **User Management** | manage_users | ✅ Full |
| **Role Management** | manage_roles | ✅ Full |
| **Permission Settings** | manage_permissions | ✅ Full |
| **Events** | manage_all_events | ✅ Create/Edit/Delete/Archive |
| **View Events** | view_all_events | ✅ Yes |
| **Attendance Records** | view_all_attendance | ✅ View all |
| **Edit Attendance** | manage_all_attendance | ✅ Edit/Delete |
| **QR Operations** | generate_qr_codes | ✅ Generate/Access |
| **Record Attendance** | manage_attendance | ✅ Record via QR |

**Summary:** Admin has **full system access** across all operations.

---

## 👤 Organizer Role Permissions Matrix

| Feature | Permission | Access |
|---|---|---|
| **User Management** | manage_users | ❌ No |
| **Role Management** | manage_roles | ❌ No |
| **Event Creation** | manage_all_events | ❌ No |
| **Event Editing** | manage_all_events | ❌ No |
| **Event Deletion** | manage_all_events | ❌ No |
| **View Events** | view_all_events | ✅ Yes (view only) |
| **View Attendance** | view_all_attendance | ✅ Yes |
| **Edit Attendance** | manage_all_attendance | ✅ Yes |
| **Generate QR Codes** | generate_qr_codes | ✅ Yes |
| **Record Attendance** | manage_attendance | ✅ Yes |
| **Create User Accounts** | manage_users | ❌ No |

**Summary:** Organizer has **attendance-focused access** only.

---

## 🎯 Organizer Allowed Operations

### ✅ CAN DO:
1. **View Events** - View all events created by admin
2. **View Attendance Records** - View attendance for any event
3. **Record Attendance** - Scan QR codes during events
4. **Generate QR Codes** - Generate or access QR codes for scanning
5. **Edit Attendance** - Modify attendance records if needed
6. **Delete Attendance** - Remove incorrect attendance records

### ❌ CANNOT DO:
1. **User Management** - Cannot create, edit, or delete users
2. **Role Management** - Cannot assign roles to users
3. **Event Creation** - Cannot create new events
4. **Event Editing** - Cannot modify event details
5. **Event Archiving** - Cannot archive events
6. **Event Deletion** - Cannot delete events
7. **Permission Settings** - Cannot configure permissions
8. **System Configuration** - Cannot access admin settings

---

## 🛡️ Admin Exclusive Operations

Only Admin can perform:
1. ✅ Create admin/organizer accounts
2. ✅ Edit user information and roles
3. ✅ Delete user accounts (data preserved)
4. ✅ Create events
5. ✅ Edit event details
6. ✅ Archive events
7. ✅ Delete events
8. ✅ Assign permissions to roles
9. ✅ Access all system settings

---

## 📊 Database Schema

### Roles Table
```sql
SELECT * FROM roles;
-- id | name       | description
-- 1  | admin      | Full system access and user management
-- 2  | organizer  | Limited access to event and attendance management
```

### Permissions Table
```sql
SELECT * FROM permissions;
-- id | name                   | description
-- 1  | manage_users           | Create, edit, and delete user accounts
-- 2  | manage_roles           | Assign and modify user roles
-- 3  | manage_permissions     | Configure system permissions
-- 4  | manage_all_events      | Create, edit, archive, and delete events
-- 5  | view_all_events        | View all events in system
-- 6  | view_all_attendance    | View all attendance records
-- 7  | manage_all_attendance  | Edit and delete attendance records
-- 8  | manage_attendance      | Record attendance via QR scanning
-- 9  | generate_qr_codes      | Generate or access QR codes for scanning
```

### Role-Permissions Junction Table
```sql
SELECT r.name as role, p.name as permission 
FROM role_permissions rp
JOIN roles r ON rp.role_id = r.id
JOIN permissions p ON rp.permission_id = p.id;

-- role       | permission
-- admin      | manage_users
-- admin      | manage_roles
-- admin      | manage_permissions
-- admin      | manage_all_events
-- admin      | view_all_events
-- admin      | view_all_attendance
-- admin      | manage_all_attendance
-- organizer  | view_all_events
-- organizer  | view_all_attendance
-- organizer  | manage_all_attendance
-- organizer  | manage_attendance
-- organizer  | generate_qr_codes
```

---

## 🔄 Permission Checking in Code

### PHP Helper Function
```php
// Check if user has permission
$auth = new Authorization($pdo, $userId);

if ($auth->hasPermission('manage_users')) {
    // Admin only - user management allowed
}

if ($auth->hasPermission('manage_attendance')) {
    // Both admin and organizer - attendance recording allowed
}

// Check multiple permissions (any match)
if ($auth->hasAnyPermission(['manage_users', 'manage_roles'])) {
    // Admin only - either permission works
}

// Check admin role
if ($auth->isAdmin()) {
    // Admin-only operations
}

// Check organizer role
if ($auth->isOrganizer()) {
    // Organizer-only operations
}
```

---

## 🚀 Implementation Roadmap

### Phase 1: Core System (DONE)
- [x] Role-Permission database schema
- [x] Permission assignment to roles
- [x] Authorization class for checking permissions
- [x] User authentication with role assignment

### Phase 2: Access Control (IN PROGRESS)
- [ ] Update index.php routing to check permissions
- [ ] Add permission guards to admin pages (users, events)
- [ ] Add permission guards to QR operations (generate, scan)
- [ ] Add permission guards to attendance operations

### Phase 3: Enhanced Pages (PENDING)
- [ ] User Management page - organizers see "no access" message
- [ ] Events page - check manage_all_events permission
- [ ] QR Scanner page - check generate_qr_codes permission
- [ ] Attendance page - check manage_attendance permission

---

## 🧪 Testing Checklist

### Admin Account Testing
- [ ] Login as admin
- [ ] Access user management ✅ (should work)
- [ ] Access events page ✅ (should work)
- [ ] Access QR scanner ✅ (should work)
- [ ] Record attendance ✅ (should work)

### Organizer Account Testing
- [ ] Create organizer account as admin
- [ ] Login as organizer
- [ ] Access user management ❌ (should fail with permission denied)
- [ ] Try to access admin pages ❌ (should redirect)
- [ ] Access event viewing ✅ (should work - view only)
- [ ] Access attendance UI ✅ (should work)
- [ ] Record attendance via QR ✅ (should work)
- [ ] Try to create event ❌ (buttons should be hidden/disabled)

---

## 💡 Security Notes

1. **Permission Validation**
   - Always check permissions on the server-side (PHP), never just client-side
   - Validate permission before performing any data modification

2. **Role Assignment**
   - Only admin can assign roles via the User Management system
   - Role changes require admin action with audit trail

3. **Data Integrity**
   - Attendance records are preserved if user is deleted
   - User references set to NULL, but recorded data kept intact

4. **Access Control**
   - Each admin page should check user role before rendering
   - Organizers receive "Access Denied" rather than redirects for security

5. **Session Management**
   - Role stored in session variables
   - Session destroyed on logout
   - Role-based redirects on insufficient permissions

---

## 📝 Future Enhancements

- [ ] Permission-based UI hiding (buttons/menus dynamically shown/hidden)
- [ ] Audit log of permission changes
- [ ] Fine-grained event assignments (organizer → specific events)
- [ ] Custom role creation (admin can create new roles)
- [ ] Temporary permission elevation (admin can grant temporary access)
- [ ] Two-factor authentication for admin accounts
- [ ] Permission inheritance and delegation

---

## 📞 Support & Troubleshooting

**Q: Organizer can see user management page**
A: This shouldn't happen with current guards. Check that:
1. User's role_id is set to organizer (id=2)
2. Access control is implemented in pages/admin/users.php

**Q: Organizer can create events**
A: Check that:
1. Permissions are properly assigned in role_permissions table
2. Event creation page checks `manage_all_events` permission
3. Form submission validates permission on backend

**Q: Admin can't access something they should**
A: Verify:
1. Admin role has all necessary permissions in role_permissions table
2. Permission checking logic isn't too restrictive
3. Admin role_id is correctly set to 1
