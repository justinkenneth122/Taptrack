# Organizer Dashboard & Login System

## Overview
The Organizer Dashboard provides organizers with a limited interface to view and manage their assigned events and attendance records.

## Login Flow for Organizers

### 1. **Authentication**
Organizers login using their email and password created by the admin in the User Management system.

**Login Process:**
1. Organizer enters email and password on login page
2. System checks:
   - First: Default admin credentials (`admin@feuroosevelt.edu.ph` / `admin`)
   - Second: Users table (Organizers and Admins created in the system)
   - Third: Students table (for student login)

3. System validates password using `password_verify()` (salted hash)

4. On successful login:
   - Session variables set:
     - `$_SESSION['user_id']` = Organizer's user ID
     - `$_SESSION['user_role']` = 'organizer' (from roles table)
     - `$_SESSION['username']` = Organizer's username
   - User redirected to `?page=organizer` (Organizer Dashboard)

## Organizer Dashboard Features

### Dashboard Page (`pages/organizer/dashboard.php`)
**Location:** Admin creates Organizer → Organizer logs in

**Features:**
- 👋 Welcome message with organizer name
- 📊 Statistics cards:
  - Total Events
  - Recent Attendance Records
  - Quick Action button to attendance page
- 📅 Upcoming Events table (read-only):
  - Program name
  - Event date
  - Status badge
  - "View Details" button
- 📋 Recent Attendance Records table:
  - Student name
  - Event program
  - Scan timestamp
  - Status (Present/Absent)
- 💡 Help section explaining organizer limitations

### Attendance Management (`pages/organizer/attendance.php`)
**Features:**
- Event filter dropdown - Filter records by specific event
- Statistics cards:
  - Total Scanned
  - Present count
  - Absent count
  - Attendance rate percentage
- Searchable attendance records table:
  - Student name and email
  - Event program
  - Scan timestamp
  - Status badge
- Responsive design for mobile viewing

## Navigation Structure

### Organizer Sidebar
The organizer panel includes limited navigation:
1. 🏠 **Dashboard** - Main overview page
2. 📋 **Attendance** - Attendance management and filtering

### Admin Panel
Admins continue to see all admin features:
1. 🏠 **Dashboard**
2. 📅 **Events**
3. ▣ **QR Generator**
4. 📷 **QR Scanner**
5. 📋 **Attendance**
6. 📦 **Archived Events**
7. 👥 **Users** (User Management)

## User Access Control

### Routing Guards in `index.php`

**Organizer Pages:**
```php
$organizerPages = ['organizer', 'organizer_attendance', 'organizer_home'];
if (in_array($page, $organizerPages)) {
    if (!isLoggedIn()) { header('Location: ?page=login'); exit; }
    if (!isOrganizer() && !isAdmin()) { 
        // Access denied message
    }
}
```

**Organizers can:**
- ✅ View their events
- ✅ View attendance for events
- ✅ Filter attendance by event
- ✅ See attendance statistics

**Organizers cannot:**
- ❌ Create/Edit/Delete events
- ❌ Create/Edit/Delete users
- ❌ Generate or scan QR codes directly
- ❌ See attendance for unassigned events (not yet implemented)

## Helper Functions

Added to `includes/functions.php`:

```php
function isOrganizer() {
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'organizer';
}
```

## Session Variables After Login

When an organizer logs in successfully:
```php
$_SESSION['user_id'] = 123; // Organizer's user ID
$_SESSION['user_role'] = 'organizer'; // Role from roles table
$_SESSION['username'] = 'john.organizer'; // Username
$_SESSION['flash'] = ['type' => 'success', 'msg' => 'Welcome john.organizer!'];
```

## File Structure

```
pages/organizer/
├── dashboard.php      # Main organizer dashboard
└── attendance.php     # Attendance management

includes/
├── functions.php      # Added isOrganizer() function
└── RoleBasedAccessControl.php

modules/
└── handlers.php       # Updated unified_login handler

index.php             # Updated routing and layout
```

## Step-by-Step: Creating and Using an Organizer Account

### 1. **Admin Creates Organizer Account**
- Navigate to Admin Panel → Users (👥)
- Click "Create User"
- Fill form:
  - Username: `john.organizer`
  - Email: `john.organizer@example.com`
  - Password: `SecurePass123`
  - Confirm Password: `SecurePass123`
  - Role: **Organizer**
- Click "Create User"
- Success message appears

### 2. **Organizer Logs In**
- Go to login page
- Enter:
  - Email: `john.organizer@example.com`
  - Password: `SecurePass123`
- Click "Sign In"
- Redirect to Organizer Dashboard

### 3. **Organizer Views Their Data**
- **Dashboard:** See statistics and upcoming events
- **Attendance:** View all attendance records, filter by event
- **Logout:** Use sidebar logout button to end session

## Security Features

1. **Password Hashing**
   - Passwords hashed with `PASSWORD_DEFAULT` (bcrypt)
   - Verified using `password_verify()` function

2. **Access Control**
   - Session-based authentication
   - Role-based access guards on sensitive pages
   - Admin users can access organizer pages
   - Organizers cannot access admin pages

3. **Data Protection**
   - Foreign key constraints on user references
   - NULL values for deleted user references
   - Prepared statements for SQL injection prevention

## Limitations & Future Enhancements

### Current Limitations:
- Event assignment not yet implemented (shows all events)
- No organizer-specific event restrictions
- No attendance export functionality
- No QR code scanning for organizers

### Planned Enhancements:
- [ ] Assign organizers to specific events
- [ ] Show only assigned events for organizers
- [ ] Export attendance reports
- [ ] QR code scanning for organizers
- [ ] Event creation for organizers
- [ ] Organizer activity audit log
- [ ] Multi-organizer collaboration on events
- [ ] Attendance analytics and reporting

## Testing Checklist

- [ ] Create organizer account as admin
- [ ] Verify password confirmation works
- [ ] Login as organizer with correct password
- [ ] Login fails with incorrect password
- [ ] Organizer dashboard displays correctly
- [ ] Event list shows all events
- [ ] Attendance filter works
- [ ] Organizer cannot access admin pages
- [ ] Admin can access organizer pages
- [ ] Logout works correctly
- [ ] Session expires on browser close

## Code Examples

### Checking Organizer Role in PHP
```php
if (isOrganizer()) {
    echo "This is an organizer";
}

if (isOrganizer() || isAdmin()) {
    echo "Organizer or admin can see this";
}
```

### Database Query for Organizers
```php
$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.email
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE r.name = 'organizer' AND u.is_active = 1
");
$stmt->execute();
$organizers = $stmt->fetchAll();
```

## Support & Troubleshooting

**Q: Organizer can't log in**
A: Check that:
1. User created successfully in User Management
2. Email is correct
3. Password is at least 6 characters
4. User is marked as active (is_active = 1)

**Q: Organizer sees admin pages**
A: This should not happen. Check that:
1. User's role_id points to 'organizer' role
2. Access control in index.php is checking `isOrganizer()`

**Q: Events not showing**
A: Events table may be empty. Create sample events from Admin Panel → Events

**Q: Attendance records not showing**
A: Attendance table may be empty. Scan QR codes or create test records manually.
