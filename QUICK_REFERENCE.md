# Taptrack Quick Reference Guide
## Common Tasks & Code Snippets

---

## 🚀 Getting Started (5 Minutes)

### 1. Import Database Schema
```bash
mysql -u root -p
# Enter password (empty for XAMPP default)

CREATE DATABASE taptrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE taptrack;
source C:\xampp\htdocs\Taptrack\database\init.sql;
SHOW TABLES;  # Should show 4 tables
```

### 2. Test Application
```
http://localhost/Taptrack/
Admin: admin / admin123
```

### 3. Create Test Student
- Email: `R20260101001@feuroosevelt.edu.ph`
- Password: `test123`
- Course: IT
- Year: 2

### 4. Test Face Registration
- Allow camera access
- Wait for "Face Captured Successfully"
- Check student dashboard

---

## 📝 File Locations Quick Map

```
CONFIG:      config/config.php
DATABASE:    config/Database.php
MODELS:      backend/models/ (Student.php, Event.php, Attendance.php)
LOGIC:       backend/controllers/ (AuthController, EventController, AttendanceController)
ROUTING:     backend/routes/api.php
HELPERS:     backend/helpers/helpers.php
STYLES:      frontend/css/styles.css
JAVASCRIPT:  frontend/js/ui.js
LOGIN PAGE:  frontend/pages/login.php
STUDENT HOME: frontend/pages/student_dashboard.php
ADMIN LAYOUT: frontend/pages/admin/admin_panel.php
ADMIN PAGES: frontend/pages/admin/ (6 sub-pages)
ENTRY:       index.php
```

---

## 💻 Common Code Snippets

### Get Current Logged-In User
```php
$user = getAuthUser();  // Returns: ['id', 'email', 'role', 'name']

if ($user) {
    echo $user['email'];
    echo $user['id'];
}
```

### Check User Role
```php
<?php requireAdmin(); ?>          // Exit if not admin
<?php requireStudent(); ?>        // Exit if not logged in

if (hasRole('admin')) {
    // Show admin features
}
```

### Escape HTML Output
```php
<?php echo e($user_input); ?>     // Always escape user data!
```

### Query Students
```php
$student = $studentModel->getByEmail('R20260101001@feuroosevelt.edu.ph');
$student = $studentModel->getById($uuid);
$students = $studentModel->getAll();
$count = $studentModel->getCount();
```

### Query Events
```php
$events = $eventModel->getActive();    // Non-archived
$events = $eventModel->getUpcoming();  // Future events
$count = $eventModel->getCount();
```

### Get Attendance for Event
```php
$records = $attendanceController->getByEvent($event_id);
// Returns: [{ student_name, student_number, course, year, scanned_at }, ...]
```

### Record Attendance Manually
```php
$result = $attendanceController->recordManual($student_id, $event_id);
if ($result['success']) {
    echo $result['message'];  // "Attendance recorded successfully"
}
```

### Create Event
```php
$result = $eventController->create(
    'Meeting Name',
    '2026-04-15',           // YYYY-MM-DD format
    'Room 101',
    'Description here'
);

if ($result['success']) {
    $event_id = $result['event_id'];
}
```

### Generate UUID
```php
$id = generateUUID();  // Returns: 550e8400-e29b-41d4-a716-446655440000
```

### Set Flash Message
```php
setFlash('success', 'Event created successfully!');
setFlash('error', 'Email already registered');

// Retrieve in next page:
$message = getFlash('success');  // Gets and deletes message
```

### Redirect User
```php
redirect('index.php?page=student_dashboard');
redirectWithFlash('index.php', 'success', 'Operation completed!');
```

### Validate Email
```php
if (isValidFEUEmail('R20260101001@feuroosevelt.edu.ph')) {
    // Valid FEU email
}

$pattern = '/^R\d{8,}@feuroosevelt\.edu\.ph$/i';
if (preg_match($pattern, $email)) {
    // Also valid
}
```

### JSON Response
```php
echo json_encode(jsonResponse(true, 'Success', ['id' => $uuid]));
// Output: {"success":true,"message":"Success","id":"uuid"}
```

---

## 🎨 CSS Class Reference

### Layout
```html
<div class="flex gap-4">              <!-- Flexbox with spacing -->
<div class="grid grid-cols-3 gap-6">  <!-- 3-column grid -->
<div class="block">                   <!-- Block display -->
```

### Spacing
```html
<div class="m-4">          <!-- Margin 1rem -->
<div class="p-6">          <!-- Padding 1.5rem -->
<div class="space-y-4">    <!-- Vertical spacing between children -->
<div class="space-x-2">    <!-- Horizontal spacing between children -->
```

### Text
```html
<h1 class="text-2xl font-bold">      <!-- Large bold heading -->
<p class="text-sm text-muted">       <!-- Small gray text -->
<span class="font-600">Bold</span>   <!-- Medium weight -->
```

### Colors
```html
<div class="bg-primary text-white">   <!-- Primary background -->
<div class="text-destructive">        <!-- Red text -->
<div class="border border-secondary">  <!-- Gold border -->
<div class="bg-success">              <!-- Green background -->
```

### Buttons
```html
<button class="btn btn-primary">      <!-- Primary button -->
<button class="btn btn-outline">      <!-- Outline button -->
<button class="btn btn-destructive">  <!-- Red button -->
<button class="btn btn-ghost">        <!-- Borderless button -->
```

### Cards
```html
<div class="card">
    <!-- Auto shadow, border, padding -->
</div>
```

### Forms
```html
<input class="input" type="text">
<select class="select">
<textarea class="textarea"></textarea>
```

### Tables
```html
<table class="table table-zebra">
    <!-- Striped rows, clean styling -->
</table>
```

### Badges
```html
<span class="badge">Label</span>
<span class="badge badge-success">Active</span>
<span class="badge badge-destructive">Inactive</span>
```

### Responsive
```html
<div class="hide-mobile">    <!-- Hidden on mobile (< 768px) -->
<div class="hide-desktop">   <!-- Hidden on desktop (>= 768px) -->
```

### Modals
```html
<div id="myModal" class="modal">
    <div class="modal-content">
        <!-- Content here -->
    </div>
</div>

<script>
    openModal('myModal');
    closeModal('myModal');
</script>
```

---

## 🔗 JavaScript Function Reference

### Modal Operations
```javascript
openModal('modalId');     // Show modal
closeModal('modalId');    // Hide modal
```

### QR Generation
```javascript
generateQR(studentId, eventId);  // Create QR code
```

### QR Scanning
```javascript
startScanning(eventId);   // Start camera
stopScanning();           // Stop camera
```

### Face Registration
```javascript
startFaceCamera();        // Initialize webcam
captureFace();           // Attempt face detection
uploadFaceDescriptor(descriptor);  // Send to server
```

### UI Utilities
```javascript
toggleCollapsible(header);              // Expand/collapse
showToast(message, 'success');          // Show notification
confirmAction('Are you sure?');         // Confirmation dialog
redirect('index.php?page=dashboard');   // Navigate
```

### Tab Switching
```javascript
switchTab('student');     // Switch to student tab
switchTab('admin');       // Switch to admin tab
switchStudentMode('login');    // Toggle login/register
switchStudentMode('register');
```

---

## 🗄️ Database Query Examples

### Direct Database Queries
```php
$db = Database::getInstance();

// Single result
$student = $db->fetchOne(
    'SELECT * FROM students WHERE email = ?',
    [$email]
);

// Multiple results
$events = $db->fetchAll(
    'SELECT * FROM events WHERE archived = 0 ORDER BY date DESC',
    []
);

// Execute (INSERT, UPDATE, DELETE)
$db->execute(
    'INSERT INTO attendance (id, student_id, event_id, scanned_at) VALUES (?, ?, ?, ?)',
    [$uuid, $student_id, $event_id, date('Y-m-d H:i:s')]
);

// Get single column
$count = $db->fetchColumn(
    'SELECT COUNT(*) FROM students',
    [],
    'COUNT(*)'
);
```

### Using Models (Recommended)
```php
// Students
$student = $studentModel->getById($id);
$student = $studentModel->getByEmail($email);
$all = $studentModel->getAll();
$studentModel->create($email, $fn, $ln, $num, $course, $year, $pass);
$studentModel->update($id, $data);
$studentModel->delete($id);

// Events
$event = $eventModel->getById($id);
$active = $eventModel->getActive();
$eventModel->create($name, $date, $location, $desc);
$eventModel->archive($id);
$eventModel->delete($id);

// Attendance
$attendance = $attendanceModel->getByEvent($event_id);
$attendance = $attendanceModel->getByStudent($student_id);
$attendanceModel->record($student_id, $event_id);
$count = $attendanceModel->getCountByEvent($event_id);
```

---

## 🔐 Security Checklist

### Input Validation
```php
// ✓ Always validate input
if (!isValidFEUEmail($email)) {
    return ['success' => false, 'message' => 'Invalid email'];
}

// ✓ Use prepared statements
$student = $db->fetchOne('SELECT * FROM students WHERE email = ?', [$email]);

// ✗ NEVER: Direct SQL with variables
// $db->fetchOne("SELECT * FROM students WHERE email = '$email'"); // SQL INJECTION!
```

### Output Escaping
```php
// ✓ Always escape user output
<?php echo e($user_input); ?>

// ✗ NEVER: Direct output
// <?php echo $user_input; ?>  <!-- XSS VULNERABILITY! -->
```

### Authentication Check
```php
// ✓ Check auth in protected pages
<?php requireAdmin(); ?>
<?php requireStudent(); ?>

// ✓ Use getAuthUser() safely
$user = getAuthUser();
if (!$user) redirect('index.php');
```

### Password Handling
```php
// Current: Simple hash (upgrade needed for production)
$hash = hashPassword($password);
if (verifyPassword($password, $hash)) { /* Match */ }

// Future: Use bcrypt
$hash = password_hash($password, PASSWORD_BCRYPT);
if (password_verify($password, $hash)) { /* Match */ }
```

---

## ⚠️ Common Mistakes & Fixes

### Mistake 1: Not Escaping Output
```php
// ❌ WRONG
<?php echo $name; ?>

// ✓ CORRECT
<?php echo e($name); ?>
```

### Mistake 2: Hardcoding Database Values
```php
// ❌ WRONG
$db->execute("SELECT * FROM students WHERE id = '$id'");

// ✓ CORRECT
$db->fetchOne("SELECT * FROM students WHERE id = ?", [$id]);
```

### Mistake 3: Forgetting to Check Success
```php
// ❌ WRONG
$result = $eventController->create($name, $date, $location);
$event_id = $result['event_id'];  // Might be null!

// ✓ CORRECT
$result = $eventController->create($name, $date, $location);
if ($result['success']) {
    $event_id = $result['event_id'];
} else {
    setFlash('error', $result['message']);
}
```

### Mistake 4: Not Requiring Admin on Admin Pages
```php
// ❌ WRONG
// No role check - anyone can access

// ✓ CORRECT
<?php requireAdmin(); ?>
// Rest of page
```

### Mistake 5: Improper Redirects
```php
// ❌ WRONG
// Page renders after redirect
header('Location: index.php?page=login');
// More code runs!

// ✓ CORRECT
redirect('index.php?page=login');  // Uses exit()
```

---

## 🐛 Troubleshooting

### Student Can't Login
1. Check email matches FEU pattern: `R[8+ digits]@feuroosevelt.edu.ph`
2. Verify student exists: Query `SELECT * FROM students WHERE email = '...'`
3. Check password: Try resetting or creating new student
4. Session issue: Clear browser cookies, try incognito

### QR Code Not Scanning
1. Verify QR generated with correct data: `{studentId, eventId, system: 'taptrack'}`
2. Check camera permissions in browser
3. Test with different camera angle/distance
4. Verify event ID matches selected event

### Face Registration Not Working
1. Check camera access permission
2. Ensure good lighting
3. Center face in guide circle
4. Try different browser (Chrome vs Firefox)
5. Check face-api.js models loaded: Browser console (F12)

### Database Errors
1. Check MySQL running: `mysql -u root -p -e "SELECT 1"`
2. Verify connection string: `config/config.php`
3. Check tables exist: `SHOW TABLES FROM taptrack;`
4. Check user permissions: `GRANT ALL ON taptrack.* TO 'root'@'localhost';`

### Permission Errors
1. Check file permissions: `chmod 755 . && chmod 644 *.php`
2. Check folder structure created properly
3. Verify config.php is readable
4. Check database write permissions

---

## 📚 Documentation Links

| Document | Purpose |
|----------|---------|
| [README.md](README.md) | Project overview & features |
| [SETUP.md](SETUP.md) | Installation & configuration |
| [API_REFERENCE.md](API_REFERENCE.md) | Controller & model documentation |
| [FILELIST.md](FILELIST.md) | Complete file-by-file reference |
| [QUICK_REFERENCE.md](QUICK_REFERENCE.md) | This file - common snippets |

---

## 🔄 Request Flow Diagrams

### Login Flow
```
index.php?page=login
    ↓
Display login form
    ↓
User enters email/password
    ↓
POST index.php (action=student_login)
    ↓
AuthController::loginStudent()
    ↓
StudentModel::getByEmail()
    ↓
Check password match
    ↓
setUserSession($student_id, 'student', $name)
    ↓
Redirect to face_register or student_dashboard
```

### Attendance from QR
```
Admin opens QR Scanner
    ↓
Selects event from dropdown
    ↓
Starts camera
    ↓
Points at QR code
    ↓
QR code detected
    ↓
Parse JSON: {studentId, eventId, system}
    ↓
Validate system === 'taptrack'
    ↓
AJAX POST ?ajax=scan_qr
    ↓
ApiRouter::route('scan_qr')
    ↓
AttendanceController::recordFromQR()
    ↓
Check student & event exist
    ↓
Check not already attended
    ↓
AttendanceModel::record()
    ↓
INSERT into attendance table
    ↓
Return JSON success
    ↓
Frontend: Show "Attendance recorded!" in green
```

---

## 🎯 Most Common Tasks

### Task 1: Create Student Account Programmatically
```php
$authController = new AuthController();
$result = $authController->registerStudent(
    'R20260101001@feuroosevelt.edu.ph',
    'John',
    'Doe',
    'IT',
    '2',
    'password123'
);

echo $result['student_id'];  // Use this student ID
```

### Task 2: Create Event
```php
$eventController = new EventController();
$result = $eventController->create(
    'Monthly Meeting',
    '2026-04-10',
    'Auditorium',
    'Department monthly gathering'
);

echo $result['event_id'];
```

### Task 3: Get All Attendance for Event
```php
$records = $attendanceController->getByEvent($event_id);

foreach ($records as $record) {
    echo $record['student_name'] . " attended at " . $record['scanned_at'];
}
```

### Task 4: Check If Student Attended Event
```php
$attended = $attendanceModel->exists($student_id, $event_id);

if ($attended) {
    echo "Student attended this event";
}
```

### Task 5: Archive Event
```php
$result = $eventController->archive($event_id);

if ($result['success']) {
    echo "Event archived";
}
```

---

## 🚀 Performance Tips

1. **Indexes** - Already created on: email, student_number, date, archived, created_at
2. **Query Optimization** - Use `fetchOne()` when expecting single result
3. **Caching** - Consider Redis for frequently accessed data
4. **Lazy Loading** - Pagination for large result sets
5. **Database Connection** - Singleton pattern used (efficient)

---

## 📞 Getting Help

**Error in Browser?**
1. Check error logs: XAMPP `apache/logs/error.log`
2. Open browser console: F12 → Console tab
3. Search error in documentation
4. Test in different browser

**Database Issue?**
1. Verify connection: `mysql -u root -p -e "USE taptrack; SHOW TABLES;"`
2. Check table structure: `DESCRIBE students;`
3. Test query manually in phpMyAdmin

**Code Question?**
1. Check [API_REFERENCE.md](API_REFERENCE.md) for method signatures
2. Look in [FILELIST.md](FILELIST.md) for file purposes
3. Search in respective controller/model file

---

**Version:** 2.0.0  
**Last Updated:** 2026-03-18
