# Taptrack API & Controller Reference
## Complete Developer Documentation

---

## 📖 Quick Reference

This document provides detailed documentation on:
1. **AJAX API Endpoints** - Server-side API for frontend calls
2. **Controller Methods** - Business logic functions
3. **Model Methods** - Database query methods
4. **Helper Functions** - Utility functions

---

## 🔌 AJAX API Endpoints

All AJAX endpoints are routed through `?ajax=endpoint_name` GET parameter.

### Table of Contents
- [scan_qr](#scan_qr) - Record attendance from QR code
- [get_students](#get_students) - Retrieve all students
- [record_attendance](#record_attendance) - Manual attendance recording

---

### scan_qr

**Purpose:** Record attendance when QR code is scanned

**Route:** `?ajax=scan_qr` (POST)

**Request:**
```json
{
  "studentId": "550e8400-e29b-41d4-a716-446655440000",
  "eventId": "550e8400-e29b-41d4-a716-446655440001",
  "system": "taptrack"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Attendance recorded for John Doe at Event Name",
  "student_name": "John Doe",
  "event_name": "Event Name"
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Attendance already recorded for this student and event"
}
```

**Errors:**
- `"Invalid request"` - Missing required fields
- `"Student not found"` - Invalid student ID
- `"Event not found"` - Invalid event ID
- `"Invalid system identifier"` - system !== 'taptrack'
- `"Attendance already recorded..."` - Duplicate attendance record

**Implemented In:** `backend/controllers/AttendanceController.php` → `recordFromQR()`

---


**Errors:**
- `"Missing student_id"` - No student ID provided
- `"Student not found"` - Invalid student ID

---

### get_students

**Purpose:** Retrieve list of all registered students (Admin only)

**Route:** `?ajax=get_students` (GET)

**Request:**
```
No parameters required
```

**Response (Success):**
```json
{
  "success": true,
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "email": "R20260101001@feuroosevelt.edu.ph",
      "first_name": "John",
      "last_name": "Doe",
      "student_number": "R20260101001",
      "course": "IT",
      "year_level": "2",
      "created_at": "2026-03-18 10:30:00"
    },
    ...
  ]
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Unauthorized - Admin access required"
}
```

**Implemented In:** `backend/controllers/AuthController.php` (uses `StudentModel.getAll()`)



---

### record_attendance

**Purpose:** Manually record attendance (not from QR code)

**Route:** `?ajax=record_attendance` (POST)

**Request:**
```json
{
  "student_id": "550e8400-e29b-41d4-a716-446655440000",
  "event_id": "550e8400-e29b-41d4-a716-446655440001"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Attendance recorded successfully"
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "This student already attended this event"
}
```

**Implemented In:** `backend/controllers/AttendanceController.php` → `recordManual()`

---

## 🎮 Controller Methods

Controllers contain business logic and validation. All return associative arrays with `success` and `message` keys.

### AuthController

Located: `backend/controllers/AuthController.php`

#### loginStudent($email, $password)
Log in a student with email and password validation

```php
$result = $authController->loginStudent(
    'R20260101001@feuroosevelt.edu.ph',
    'password123'
);
// Returns:
// {
//   'success' => true,
//   'message' => 'Login successful',
//   'student_id' => 'uuid',
//   'student_name' => 'John Doe'
// }
```

**Validation:**
- Email must match FEU pattern (R[8+digits]@feuroosevelt.edu.ph)
- Email must exist in database
- Password must match stored hash

---

#### registerStudent($email, $firstName, $lastName, $course, $yearLevel, $password)
Create new student account

```php
$result = $authController->registerStudent(
    'R20260101001@feuroosevelt.edu.ph',
    'John',
    'Doe',
    'IT',
    '2',
    'password123'
);
// Returns:
// {
//   'success' => true,
//   'message' => 'Registration successful.',
//   'student_id' => 'uuid'
// }
```

**Validation:**
- Email must match FEU pattern
- Email must not already exist
- All fields required
- Password minimum 6 characters (recommended: enforce stronger requirement)

---

#### loginAdmin($username, $password)
Authenticate admin user

```php
$result = $authController->loginAdmin('admin', 'admin123');
// Returns:
// {
//   'success' => true,
//   'message' => 'Admin login successful'
// }
```

**Validation:**
- Username must match `$ADMIN_USER` in config
- Password must match `$ADMIN_PASS` in config

---

#### logout()
Destroy session and log out user

```php
$authController->logout();
// Destroys all session data
```


---

### EventController

Located: `backend/controllers/EventController.php`

#### create($name, $date, $location, $description)
Create a new event

```php
$result = $eventController->create(
    'Department Meeting',
    '2026-04-15',
    'Room 101',
    'Quarterly department meeting'
);
// Returns:
// {
//   'success' => true,
//   'message' => 'Event created successfully',
//   'event_id' => 'uuid'
// }
```

**Validation:**
- Name required (max 255 chars)
- Date in YYYY-MM-DD format
- Location required
- Description optional

---

#### update($eventId, $name, $date, $location, $description)
Update event details

```php
$result = $eventController->update(
    'uuid',
    'Updated Name',
    '2026-04-20',
    'Room 102',
    'Updated description'
);
```

---

#### archive($eventId)
Mark event as archived (no longer active)

```php
$result = $eventController->archive('uuid');
// Returns:
// {
//   'success' => true,
//   'message' => 'Event archived successfully'
// }
```

---

#### delete($eventId)
Permanently delete event and associated records

```php
$result = $eventController->delete('uuid');
```

---

#### getActive()
Get all non-archived events

```php
$events = $eventController->getActive();
// Returns array of event objects
```

---

#### getArchived()
Get all archived events

```php
$events = $eventController->getArchived();
```

---

### AttendanceController

Located: `backend/controllers/AttendanceController.php`

#### recordFromQR($studentId, $eventId, $system)
Record attendance from QR code scan

```php
$result = $attendanceController->recordFromQR(
    'student_uuid',
    'event_uuid',
    'taptrack'
);
// Returns success/error object
```

**Validation:**
- System identifier must be 'taptrack'
- Student and event must exist
- Prevents duplicate records

---

#### recordManual($studentId, $eventId)
Manually record attendance (admin feature)

```php
$result = $attendanceController->recordManual(
    'student_uuid',
    'event_uuid'
);
```

---

#### getByEvent($eventId)
Get all attendance records for an event

```php
$records = $attendanceController->getByEvent('event_uuid');
// Returns array of records with student details
// [
//   {
//     'id' => 'uuid',
//     'student_name' => 'John Doe',
//     'student_number' => 'R20260101001',
//     'course' => 'IT',
//     'year_level' => '2',
//     'scanned_at' => '2026-03-18 14:30:00'
//   },
//   ...
// ]
```

---

#### getByStudent($studentId)
Get all events attended by a student

```php
$events = $attendanceController->getByStudent('student_uuid');
```

---

#### getStatistics($eventId)
Get attendance statistics for an event

```php
$stats = $attendanceController->getStatistics('event_uuid');
// Returns:
// {
//   'total_students' => 42,
//   'attended' => 38,
//   'absent' => 4,
//   'percentage' => 90.5
// }
```

---

## 📊 Model Methods

Models handle direct database operations using PDO prepared statements.

### Student Model

Located: `backend/models/Student.php`

#### getById($id)
Get student by UUID

```php
$student = $studentModel->getById('uuid');
// Returns: { id, email, first_name, last_name, student_number, course, year_level, ... }
// Or: null if not found
```

---

#### getByEmail($email)
Get student by email address

```php
$student = $studentModel->getByEmail('R20260101001@feuroosevelt.edu.ph');
```

---

#### getByStudentNumber($number)
Get student by student number (R-number)

```php
$student = $studentModel->getByStudentNumber('R20260101001');
```

---

#### getAll()
Get all registered students

```php
$students = $studentModel->getAll();
// Returns: array of student objects
```

---

#### getCount()
Get total number of registered students

```php
$count = $studentModel->getCount();  // Returns: 156
```

---

#### create($email, $firstName, $lastName, $studentNumber, $course, $yearLevel, $password)
Create new student record

```php
$id = $studentModel->create(
    'R20260101001@feuroosevelt.edu.ph',
    'John',
    'Doe',
    'R20260101001',
    'IT',
    '2',
    'hashed_password'
);
// Returns: UUID of created student
```

---

#### update($id, $data)
Update student record

```php
$studentModel->update('uuid', [
    'first_name' => 'Jonathan',
    'course' => 'ComSci'
]);
```

---


#### search($query)
Search students by name, email, or student number

```php
$results = $studentModel->search('John');
// Searches: first_name, last_name, email, student_number
```

---

### Event Model

Located: `backend/models/Event.php`

#### getById($id)
Get event by UUID

```php
$event = $eventModel->getById('uuid');
```

---

#### getActive()
Get all non-archived events (sorted by date)

```php
$events = $eventModel->getActive();
```

---

#### getArchived()
Get all archived events

```php
$events = $eventModel->getArchived();
```

---

#### getAll()
Get all events (archived and active)

```php
$events = $eventModel->getAll();
```

---

#### getUpcoming()
Get future events (date > today)

```php
$upcoming = $eventModel->getUpcoming();
```

---

#### getPast()
Get past events (date < today)

```php
$past = $eventModel->getPast();
```

---

#### create($name, $date, $location, $description)
Create new event

```php
$id = $eventModel->create(
    'Annual Summit',
    '2026-05-10',
    'Main Hall',
    'Yearly university summit'
);
// Returns: UUID
```

---

#### archive($id)
Mark event as archived

```php
$eventModel->archive('uuid');
// Sets archived = 1
```

---

#### delete($id)
Permanently delete event

```php
$eventModel->delete('uuid');
// Deletes all associated attendance records due to CASCADE constraint
```

---

### Attendance Model

Located: `backend/models/Attendance.php`

#### getById($id)
Get attendance record by UUID

```php
$record = $attendanceModel->getById('uuid');
```

---

#### getByEvent($eventId)
Get all attendance for an event

```php
$records = $attendanceModel->getByEvent('event_uuid');
// Returns: array of {id, student_id, event_id, scanned_at}
```

---

#### getByStudent($studentId)
Get all attendance for a student

```php
$records = $attendanceModel->getByStudent('student_uuid');
```

---

#### record($studentId, $eventId)
Record attendance (check-in)

```php
$id = $attendanceModel->record('student_uuid', 'event_uuid');
// Returns: UUID of attendance record
// Throws: Exception if already attended
```

---

#### recordCheckout($studentId, $eventId)
Record check-out time

```php
$attendanceModel->recordCheckout('student_uuid', 'event_uuid');
// Updates scanned_out_at timestamp
```

---

#### exists($studentId, $eventId)
Check if student attended event

```php
$attended = $attendanceModel->exists('student_uuid', 'event_uuid');
// Returns: true/false
```

---

#### getCount()
Get total attendance records

```php
$total = $attendanceModel->getCount();
```

---

#### getCountByEvent($eventId)
Get attendance count for an event

```php
$count = $attendanceModel->getCountByEvent('event_uuid');
```

---

## 🛠️ Helper Functions

Located: `backend/helpers/helpers.php`

### Security Functions

#### e($html)
Escape HTML special characters (XSS prevention)

```php
echo e('<script>alert("xss")</script>');
// Output: &lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;

echo e($user_input);  // Always escape user input!
```

---

#### isAjax()
Check if request is AJAX

```php
if (isAjax()) {
    return jsonResponse(true, 'JSON response');
}
```

---

### Authentication Functions

#### isAuthenticated()
Check if user is logged in

```php
if (!isAuthenticated()) {
    redirect('index.php');
}
```

---

#### getAuthUser()
Get current logged-in user data

```php
$user = getAuthUser();
// Returns: { id, email, first_name, role (student/admin) }
```

---

#### hasRole($role)
Check if user has specific role

```php
if (hasRole('admin')) {
    // Show admin features
}
```

---

#### requireAdmin()
Redirect to login if not admin

```php
requireAdmin();  // Exits if user not admin
// Rest of code only runs for admins
```

---

#### requireStudent()
Redirect to login if not logged in as student

```php
requireStudent();  // Exits if not authenticated as student
```

---

### Utility Functions

#### generateUUID()
Generate RFC4122 v4 UUID

```php
$id = generateUUID();
// Output: 550e8400-e29b-41d4-a716-446655440000
```

---

#### extractStudentNumber($email)
Extract R-number from FEU email

```php
$number = extractStudentNumber('R20260101001@feuroosevelt.edu.ph');
// Output: R20260101001
```

---

#### formatDate($date)
Format date for display

```php
echo formatDate('2026-03-18');
// Output: Mar 18, 2026
```

---

#### formatTimestamp($datetime)
Format datetime for display

```php
echo formatTimestamp('2026-03-18 14:30:00');
// Output: Mar 18, 2026 2:30 PM
```

---

#### getFlash($key)
Get flash message from session

```php
$message = getFlash('success');
// Returns: stored message or empty string
// Automatically deletes message after retrieval
```

---

#### setFlash($key, $message)
Set flash message in session

```php
setFlash('success', 'Event created successfully');
setFlash('error', 'Invalid email format');
```

---

#### jsonResponse($success, $message, $data = [])
Generate JSON response

```php
echo json_encode(jsonResponse(true, 'Success', ['id' => $uuid]));
// Output: {"success":true,"message":"Success","id":"uuid"}
```

---

#### isValidFEUEmail($email)
Validate FEU email format

```php
if (isValidFEUEmail($email)) {
    // Email matches R[8+digits]@feuroosevelt.edu.ph
}
```

---

## 🔄 Request/Response Flow

### Student Login Flow

```
Frontend Input
    ↓
POST index.php (action=student_login)
    ↓
AuthController::loginStudent()
    ↓
StudentModel::getByEmail()
    ↓
Password verification
    ↓
Set $_SESSION['user']
    ↓
Redirect to student_dashboard
    ↓
index.php includes frontend/pages/student_dashboard.php
    ↓
Display upcoming events
```

### QR Scan Flow

```
Frontend (QR Scanner)
    ↓
AJAX POST ?ajax=scan_qr
    ↓
ApiRouter::route()
    ↓
AttendanceController::recordFromQR()
    ↓
Validate student & event exist
    ↓
Check no duplicate attendance
    ↓
AttendanceModel::record()
    ↓
Insert attendance row
    ↓
Return JSON success
    ↓
Frontend shows "Success!" message
```

---

## 🚨 Error Handling

All controllers use try-catch blocks and return consistent error format:

```php
{
    'success' => false,
    'message' => 'Human-readable error message'
}
```

Frontend should check `success` flag before using data:

```javascript
fetch('/Taptrack?ajax=scan_qr', {
    method: 'POST',
    body: JSON.stringify(data)
})
.then(r => r.json())
.then(response => {
    if (response.success) {
        showToast('Success: ' + response.message, 'success');
    } else {
        showToast('Error: ' + response.message, 'error');
    }
});
```

---

## 📚 Extending the System

### Add New Model

1. Create `backend/models/NewEntity.php`
2. Extend database layer with new table
3. Implement CRUD methods using PDO

### Add New Controller

1. Create `backend/controllers/NewController.php`
2. Use models for data operations
3. Add validation and error handling
4. Return consistent `['success' => bool, 'message' => string]`

### Add New AJAX Endpoint

1. Add case in `backend/routes/api.php` ApiRouter::route()
2. Create corresponding controller method
3. Return `jsonResponse()` for success/error
4. Test with curl or Postman

### Add New Frontend Page

1. Create `frontend/pages/new_page.php`
2. Add role check if needed (`requireAdmin()` or `requireStudent()`)
3. Query data from controllers/models
4. Render HTML using CSS utility classes
5. Add to routing in `index.php`

---

**API Version:** 2.0.0  
**Last Updated:** 2026-03-18
