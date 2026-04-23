# Taptrack Complete Project Structure
## Full File Inventory & Documentation

---

## 📦 Project Tree (24 Files + 3 Documentation)

```
Taptrack/
├─ 📄 README.md                          [DOCUMENTATION] Main project overview
├─ 📄 SETUP.md                           [DOCUMENTATION] Installation guide
├─ 📄 API_REFERENCE.md                   [DOCUMENTATION] Developer API docs
├─ 📄 FILELIST.md                        [THIS FILE] Complete file inventory
├─ 📄 index.php                          [ENTRY POINT] Main application router
├─ 📄 index.php.backup                   [BACKUP] Original single-file version
│
├─ 📁 config/                            [CONFIGURATION] Settings & database
│   ├─ 📄 config.php                     [FILE 1] App configuration (DB, admin, courses)
│   └─ 📄 Database.php                   [FILE 2] PDO singleton for DB connection
│
├─ 📁 database/                          [SCHEMA] Database structure
│   └─ 📄 init.sql                       [FILE 3] Database schema & tables
│
├─ 📁 backend/                           [APPLICATION LOGIC] Business logic
│   ├─ 📁 models/                        Data access layer
│   │   ├─ 📄 Student.php                [FILE 4] Student database operations
│   │   ├─ 📄 Event.php                  [FILE 5] Event database operations
│   │   └─ 📄 Attendance.php             [FILE 6] Attendance tracking
│   │
│   ├─ 📁 controllers/                   Business logic & validation
│   │   ├─ 📄 AuthController.php         [FILE 7] Login/register logic
│   │   ├─ 📄 EventController.php        [FILE 8] Event management logic
│   │   └─ 📄 AttendanceController.php   [FILE 9] Attendance recording
│   │
│   ├─ 📁 helpers/                       Utility functions
│   │   └─ 📄 helpers.php                [FILE 10] 32+ utility functions
│   │
│   └─ 📁 routes/                        AJAX routing
│       └─ 📄 api.php                    [FILE 11] ApiRouter class (5 endpoints)
│
├─ 📁 frontend/                          [USER INTERFACE] Frontend pages
│   ├─ 📁 pages/                         Page templates
│   │   ├─ 📄 login.php                  [FILE 12] Login/register forms
│   │   ├─ 📄 student_dashboard.php      [FILE 14] Student view
│   │   │
│   │   └─ 📁 admin/                     Admin pages
│   │       ├─ 📄 admin_panel.php        [FILE 15] Admin layout & navigation
│   │       ├─ 📄 dashboard.php          [FILE 16] Admin stats dashboard
│   │       ├─ 📄 events.php             [FILE 17] Event CRUD interface
│   │       ├─ 📄 qr_generator.php       [FILE 18] QR code generation tool
│   │       ├─ 📄 qr_scanner.php         [FILE 19] QR code scanner
│   │       ├─ 📄 attendance.php         [FILE 20] Attendance records view
│   │       └─ 📄 archived.php           [FILE 21] Archived events view
│   │
│   ├─ 📁 css/                           Styling
│   │   └─ 📄 styles.css                 [FILE 22] Complete responsive stylesheet
│   │
│   └─ 📁 js/                            Client-side logic
│       └─ 📄 ui.js                      [FILE 23] All UI interactions
│
└─ 📄 *.htaccess                         [OPTIONAL] URL rewriting (not created)
```

---

## 📋 File-by-File Reference

### Root Directory Files

#### **index.php** (Main Entry Point)
- **Type:** PHP Application Router
- **Size:** ~400 lines
- **Purpose:** 
  - Initialize session and security headers
  - Load all dependencies (config, models, controllers)
  - Route requests (AJAX, POST actions, page rendering)
  - Check authentication and authorization
  - Handle final HTML output
- **Key Functions:**
  - `handlePostAction($action)` - Process form submissions
  - `checkPageAccess($page)` - Enforce role-based access
  - Initialize database singleton
- **Dependencies:** Requires all files in backend/, config/, frontend/
- **Workflow:**
  1. Start session with security headers
  2. Load config.php and Database.php
  3. Load all Models (3 classes)
  4. Load all Controllers (3 classes)
  5. Load ApiRouter
  6. Route incoming request
  7. Output HTML with embedded PHP
- **Example Request Flow:**
  - User visits `http://localhost/Taptrack/`
  - Routed to login page (not authenticated)
  - User submits login form
  - POST triggers `handlePostAction('student_login')`
  - AuthController validates credentials
  - Sets session and redirects to dashboard

#### **index.php.backup** (Original File)
- **Type:** Backup
- **Size:** ~2000 lines
- **Purpose:** Archive of original monolithic application
- **Notable:** All code was in this single file (models, views, styles, JavaScript)
- **Note:** Reference only, not used in refactored version

---

## 🗂️ Config Directory

### **config/config.php** (APPLICATION CONFIGURATION)
- **File Number:** 1/27
- **Type:** Configuration file
- **Size:** ~50 lines
- **Purpose:** Centralized application settings
- **Key Variables:**
  - `$DB_HOST` - MySQL host
  - `$DB_NAME` - Database name
  - `$DB_USER` - Database username
  - `$DB_PASS` - Database password
  - `$ADMIN_USER` - Admin username
  - `$ADMIN_PASS` - Admin password (CHANGE IN PRODUCTION)
  - `$EMAIL_PATTERN` - FEU email validation regex
  - `$TIMEZONE` - Application timezone
  - `$COURSES` - Array of available courses
  - `$YEAR_LEVELS` - Array of academic years
- **Security Notes:**
  - Keep out of version control in production
  - Use environment variables for sensitive data
  - Change admin password immediately on production
- **Imported By:** index.php (first include)
- **Used By:** Database.php, helpers.php, all controllers

### **config/Database.php** (DATABASE CONNECTION MANAGER)
- **File Number:** 2/27
- **Type:** PHP Class (Singleton Pattern)
- **Size:** ~120 lines
- **Class:** `Database`
- **Key Methods:**
  - `getInstance()` - Static method to get singleton instance
  - `query($sql, $params)` - Execute query with parameters
  - `fetchOne($sql, $params)` - Get single result
  - `fetchAll($sql, $params)` - Get multiple results
  - `fetchColumn($sql, $params, $column)` - Get single column
  - `execute($sql, $params)` - Execute without result
  - `beginTransaction()` - Start transaction
  - `commit()` - Commit transaction
  - `rollback()` - Rollback transaction
- **Features:**
  - Single static instance (Singleton pattern)
  - PDO error mode set to EXCEPTION
  - Prepared statement enforcement
  - Transaction support
  - Proper error handling
- **Depends On:** config.php
- **Used By:** All Model classes (Student, Event, Attendance)
- **Important:** Never instantiate directly, always use `getInstance()`

---

## 🗄️ Database Directory

### **database/init.sql** (DATABASE SCHEMA)
- **File Number:** 3/27
- **Type:** SQL schema definition
- **Size:** ~150 lines
- **Purpose:** Complete database structure
- **Tables Created:**

  1. **students**
     - Columns: id (UUID), email (UNIQUE), first_name, last_name, student_number (INDEX), course, year_level, password, (JSON), created_at (INDEX), updated_at
     - Constraints: PRIMARY KEY id, UNIQUE email, UNIQUE student_number, INDEX created_at
     - Purpose: Store student user accounts and biometric data

  2. **events**
     - Columns: id (UUID), name, date (INDEX), location, description, archived (INDEX), created_at (INDEX), updated_at
     - Constraints: PRIMARY KEY id, INDEX date, INDEX archived
     - Purpose: Store event information

  3. **attendance**
     - Columns: id (UUID), student_id (FK), event_id (FK), scanned_at, scanned_out_at, created_at
     - Constraints: PRIMARY KEY id, FOREIGN KEY student_id, FOREIGN KEY event_id, UNIQUE (student_id, event_id)
     - Purpose: Track attendance records with cascade delete

  4. **audit_logs** (Optional)
     - Columns: id, action, entity_type, entity_id, user_id, details, ip_address, timestamp
     - Purpose: Log admin actions for auditing

- **Indexes:** Applied to frequently queried columns (email, student_number, date, archived, created_at)
- **Character Set:** utf8mb4 (full Unicode support)
- **Collation:** utf8mb4_unicode_ci (case-insensitive, accent-insensitive)
- **Execution:** Import via phpMyAdmin or mysql command line

---

## 🧠 Backend Directory

### **backend/models/** - Data Access Layer

#### **Student.php** (STUDENT MODEL)
- **File Number:** 4/27
- **Type:** PHP Class
- **Size:** ~250 lines
- **Class:** `Student`
- **Dependencies:** Database singleton from config/Database.php
- **Key Methods:**
  - `getById($id)` - Retrieve student by UUID
  - `getByEmail($email)` - Retrieve by email address
  - `getByStudentNumber($number)` - Retrieve by R-number
  - `getAll()` - Get all students
  - `getCount()` - Count total students
  - `create($email, $fn, $ln, $num, $course, $year, $pass)` - Create new student
  - `update($id, $data)` - Update student record
  - `delete($id)` - Delete student and attendance records
  - `emailExists($email)` - Check email uniqueness
  - `studentNumberExists($number)` - Check R-number uniqueness
  - `search($query)` - Search by name/email/R-number
- **All Methods:** Use prepared statements with parameter binding
- **Used By:** AuthController, AttendanceController, admin pages
- **Returns:** Associative arrays or null

#### **Event.php** (EVENT MODEL)
- **File Number:** 5/27
- **Type:** PHP Class
- **Size:** ~280 lines
- **Class:** `Event`
- **Dependencies:** Database singleton
- **Key Methods:**
  - `getById($id)` - Get by UUID
  - `getActive()` - All non-archived events (sorted by date)
  - `getArchived()` - All archived events
  - `getAll()` - All events regardless of status
  - `getCount()` - Count events
  - `getUpcoming()` - Future events (date > today)
  - `getPast()` - Past events (date < today)
  - `create($name, $date, $location, $desc)` - Create event
  - `update($id, $name, $date, $location, $desc)` - Update event
  - `archive($id)` - Mark as archived (archived = 1)
  - `unarchive($id)` - Unarchive event
  - `delete($id)` - Delete event and attendance records
  - `search($query)` - Search events by name/location
  - `getByDateRange($from, $to)` - Query by date range
- **Used By:** EventController, admin pages, student dashboard

#### **Attendance.php** (ATTENDANCE MODEL)
- **File Number:** 6/27
- **Type:** PHP Class
- **Size:** ~300 lines
- **Class:** `Attendance`
- **Dependencies:** Database singleton
- **Key Methods:**
  - `getById($id)` - Get record by UUID
  - `getByStudentEvent($sid, $eid)` - Get specific attendance record
  - `getByEvent($eid)` - All students who attended event
  - `getByStudent($sid)` - All events attended by student
  - `getAll()` - All attendance records
  - `record($student_id, $event_id)` - Create attendance record
  - `recordCheckout($sid, $eid)` - Update check-out time
  - `exists($sid, $eid)` - Check if attended
  - `update($id, $data)` - Update record
  - `delete($id)` - Delete record
  - `getStatistics($event_id)` - Count attended vs absent
  - `getSummaryByEvent($eid)` - Attendance summary for event
  - `getSummaryByStudent($sid)` - Attendance summary for student
  - `getCount()` - Total records
  - `getCountByEvent($eid)` - Count for specific event
  - `getUniqueStudentCount($eid)` - Unique attendees for event
- **Special Features:**
  - Joins with student data (name, number, course, year)
  - Returns rich data including student details
  - Used for reporting and analytics
- **Used By:** AttendanceController, admin reporting pages

---

### **backend/controllers/** - Business Logic Layer

#### **AuthController.php** (AUTHENTICATION)
- **File Number:** 7/27
- **Type:** PHP Class
- **Size:** ~180 lines
- **Class:** `AuthController`
- **Dependencies:** StudentModel, helpers.php
- **Key Methods:**

  **loginStudent($email, $password)**
  - Validates email format (FEU pattern)
  - Checks if email exists
  - Verifies password
  - Sets session variables
  - Returns: `['success' => bool, 'message' => string, 'student_id' => string]`

  **registerStudent($email, $fn, $ln, $course, $year, $pass)**
  - Validates FEU email format
  - Checks email not already registered
  - Creates student record
  - Returns: `['success' => bool, 'message' => string, 'student_id' => uuid]`

  **loginAdmin($username, $password)**
  - Validates against config credentials
  - Sets admin session
  - Returns: `['success' => bool, 'message' => string]`

  **logout()**
  - Destroys all session data
  - Clears session variables

- **Validation:** Email format, password length, required fields
- **Used By:** index.php handlePostAction(), AJAX endpoints
- **Security:** All passwords intended to be hashed (future enhancement)

#### **EventController.php** (EVENT MANAGEMENT)
- **File Number:** 8/27
- **Type:** PHP Class
- **Size:** ~200 lines
- **Class:** `EventController`
- **Dependencies:** EventModel
- **Key Methods:**

  **create($name, $date, $location, $desc)**
  - Validates date format (YYYY-MM-DD)
  - Requires name and location
  - Creates new event record
  - Returns: `['success' => bool, 'message' => string, 'event_id' => uuid]`

  **update($id, $name, $date, $location, $desc)**
  - Updates event details with same validation

  **archive($id)**
  - Marks event as archived (sets archived = 1)
  - Returns: `['success' => bool, 'message' => string]`

  **delete($id)**
  - Permanently deletes event
  - Attendance records deleted via CASCADE

  **search($query)**
  - Searches by name/location

  **getActive(), getArchived(), getCount(), getUpcoming()**
  - Query methods wrapping EventModel

- **Business Logic:**
  - Date validation (must be YYYY-MM-DD format)
  - Event existence checking
  - Consistent error handling

- **Used By:** index.php handlePostAction(), admin events page

#### **AttendanceController.php** (ATTENDANCE OPERATIONS)
- **File Number:** 9/27
- **Type:** PHP Class
- **Size:** ~220 lines
- **Class:** `AttendanceController`
- **Dependencies:** StudentModel, EventModel, AttendanceModel
- **Key Methods:**

  **recordFromQR($student_id, $event_id, $system)**
  - Validates system identifier === 'taptrack'
  - Checks student and event exist
  - Prevents duplicate attendance
  - Records attendance timestamp
  - Returns: `['success' => bool, 'message' => string, 'student_name' => string]`

  **recordManual($student_id, $event_id)**
  - Admin-triggered attendance recording
  - Same validation as QR recording

  **getByEvent($event_id)**
  - Returns all attendance for event with student details
  - Joins: student name, number, course, year, timestamp

  **getByStudent($student_id)**
  - Returns all events attended by student

  **getStatistics($event_id)**
  - Calculates: total students, attended, absent, percentage

  **getSummaryByEvent($event_id), getSummaryByStudent($student_id)**
  - Attendance summaries by event or student

  **delete($id), getStudentAttendedEvents()**
  - Additional utilities

- **Key Feature:** Comprehensive data joining for reports
- **Used By:** AJAX endpoint scan_qr, admin attendance/attendance pages, student dashboard

---

### **backend/helpers/helpers.php** (UTILITY FUNCTIONS)
- **File Number:** 10/27
- **Type:** PHP utility functions file
- **Size:** ~400 lines
- **Contains:** 32+ functions organized by category
- **Categories:**

  **Security & Output**
  - `e($html)` - HTML escape (XSS prevention)
  - `jsonResponse($success, $msg, $data)` - JSON formatting

  **Authentication**
  - `isAuthenticated()` - Check if user logged in
  - `getAuthUser()` - Get current user data
  - `hasRole($role)` - Check user role
  - `requireAdmin()` - Enforce admin access
  - `requireStudent()` - Enforce student access
  - `setUserSession($id, $role, $name)` - Create session
  - `clearSession()` - Logout

  **Validation**
  - `isValidFEUEmail($email)` - FEU email validation
  - `extractStudentNumber($email)` - Parse R-number from email
  - `isValidDate($date, $format)` - Date format validation
  - `isValidUUID($uuid)` - UUID validation

  **Utilities**
  - `generateUUID()` - Create RFC4122 v4 UUID
  - `hashPassword($pass)` - Password hashing (MD5 - needs bcrypt upgrade)
  - `verifyPassword($pass, $hash)` - Password verification
  - `formatDate($date)` - Display formatting (e.g., "Mar 18, 2026")
  - `formatTimestamp($ts)` - DateTime formatting (e.g., "Mar 18, 2026 2:30 PM")
  - `getClientIP()` - Retrieve client IP address

  **Session Messages**
  - `getFlash($key)` - Retrieve and delete flash message
  - `setFlash($key, $message)` - Store flash message
  - `hasFlash($key)` - Check if flash exists
  - `getAllFlashes()` - Get all flash messages

  **Request Utilities**
  - `isAjax()` - Check if AJAX request
  - `getJsonInput()` - Parse JSON POST data
  - `redirect($url)` - HTTP redirect
  - `redirectWithFlash($url, $key, $msg)` - Redirect with message

- **Used By:** All controllers, views, index.php
- **Security Notes:** All user output should be escaped with `e()`

---

### **backend/routes/api.php** (AJAX ROUTING)
- **File Number:** 11/27
- **Type:** PHP Class (single class)
- **Size:** ~250 lines
- **Class:** `ApiRouter`
- **Purpose:** Central handler for all AJAX requests
- **Key Method:** `route($endpoint)`
- **Endpoints Handled:**

  1. **scan_qr** - Record attendance from QR code
  2. **save_face_descriptor** - Save face recognition data
  3. **get_students** - Retrieve all students (admin only)
  4. **get_face_descriptor** - Get face data for student
  5. **record_attendance** - Manual attendance recording

- **Features:**
  - Consistent error handling with try-catch
  - JSON request/response format
  - Authentication checks where needed
  - Input validation for all endpoints
  - Returns `['success' => bool, 'message' => string, ...]`

- **Security:**
  - Admin-only endpoints checked
  - Input parameter validation
  - Prepared statement usage enforced
  - XSS protection via helpers

- **Used By:** index.php when `isAjax()` returns true

---

## 🎨 Frontend Directory

### **frontend/pages/login.php** (LOGIN & REGISTRATION)
- **File Number:** 12/27
- **Type:** HTML/PHP template
- **Size:** ~180 lines
- **Purpose:** User authentication interface
- **Features:**

  - **Tabbed Interface** (Student | Admin)
  - **Student Login Tab**
    - Email field (FEU format: R-number@feuroosevelt.edu.ph)
    - Password field
    - Submit button
  
  - **Student Register Tab**
    - Email field with validation
    - First name
    - Last name
    - Course dropdown
    - Year level dropdown
    - Password field
    - Confirm password
    - Submit button

  - **Admin Login Tab**
    - Username field
    - Password field
    - Submit button

- **Forms:** All POST to handlePostAction with specific action values
- **Styling:** Gradient background, card-based layout, responsive design
- **JavaScript:** Tab switching functionality, form submission

### **frontend/pages/face_register.php** (FACE RECOGNITION)
- **File Number:** 13/27
- **Type:** HTML/PHP template with JavaScript
- **Size:** ~240 lines
- **Purpose:** Facial recognition enrollment
- **Workflow:**

  1. **Idle State** - Instructions shown, "Next" button
  2. **Camera Active** - Video stream to canvas with guide circle
  3. **Scanning Line** - Animated horizontal line over guide circle
  4. **Face Detection** - face-api.js processes frames
  5. **Multiple Attempts** - 14 attempts with different presets
  6. **Success** - Shows confirmation, "Continue to Dashboard"

- **Features:**
  - Real-time video capture from webcam
  - face-api.js TinyFaceDetector for face detection
  - Multiple detection presets for robustness
  - Fallback to canvas snapshot if detection fails
  - Error handling for camera permission denied
  - Face descriptor extracted and sent to server via AJAX

- **Dependencies:** face-api.js, tensorflow.js
- **Security:** Face descriptor validated (128 values)

### **frontend/pages/student_dashboard.php** (STUDENT HOME)
- **File Number:** 14/27
- **Type:** HTML/PHP template
- **Size:** ~200 lines
- **Purpose:** Student view of events and attendance
- **Features:**

  - **Header** - Student name, logout button, course info
  - **Welcome Message** - Shows email, course, year level
  - **Face Registration Check** - Warning if not completed
  - **Upcoming Events List**
    - Event name, description, date, location
    - Attendance badge (Attended/Not Attended)
    - Collapsible QR code display
    - QR data: { studentId, eventId, system: 'taptrack' }

- **Data Source:**
  - EventModel.getActive() - upcoming events
  - AttendanceController.getStudentAttendedEvents() - check attendance

- **Styling:** Responsive cards, mobile-friendly

### **frontend/pages/admin/admin_panel.php** (ADMIN LAYOUT)
- **File Number:** 15/27
- **Type:** HTML/PHP template
- **Size:** ~150 lines
- **Purpose:** Admin interface layout wrapper
- **Structure:**

  - **Sidebar Navigation** (240px wide, dark green)
    - Taptrack logo/branding
    - 6 navigation links:
      1. Dashboard
      2. Events
      3. QR Generator
      4. QR Scanner
      5. Attendance
      6. Archived Events
    - Logout button at bottom

  - **Main Content Area**
    - Header bar
    - Scrollable content section

  - **Responsive:** Sidebar collapses to 60px on mobile (< 768px)

- **Routing:** Includes appropriate sub-page based on `$subpage` variable
- **Used By:** All admin pages use this layout

### **frontend/pages/admin/dashboard.php** (ADMIN DASHBOARD)
- **File Number:** 16/27
- **Type:** HTML/PHP template
- **Size:** ~100 lines
- **Purpose:** Admin overview with key metrics
- **Statistics Cards:**

  1. **Total Events** - EventModel.getCount()
  2. **Registered Students** - StudentModel.getCount()
  3. **Total Scans** - AttendanceModel.getCount()
  4. **Unique Attendees** - Calculated from all events

- **Display:** Grid layout with icons, labels, large numbers
- **Color Coding:** Each metric has distinct icon/color
- **Real-time:** Queries current database state on each page load

### **frontend/pages/admin/events.php** (EVENT MANAGEMENT)
- **File Number:** 17/27
- **Type:** HTML/PHP template
- **Size:** ~220 lines
- **Purpose:** Create, view, and manage events
- **Features:**

  - **Event Table**
    - Columns: Name, Date, Location, Description, Actions
    - Rows: All active events
    - Action buttons: Edit, Archive, Delete
    - Responsive: Hide description on mobile

  - **Add Event Modal**
    - Modal form overlay
    - Fields: Name, Date (date picker), Location, Description
    - Submit creates new event via POST

  - **Archive Function**
    - Confirmation dialog before archiving
    - Removes event from active list

  - **Data Source:** EventModel.getActive()

- **JavaScript Integration:** Modal open/close, confirmation dialogs

### **frontend/pages/admin/qr_generator.php** (QR GENERATION)
- **File Number:** 18/27
- **Type:** HTML/PHP template
- **Size:** ~140 lines
- **Purpose:** Generate QR codes for attending events
- **Workflow:**

  1. **Student Selection Dropdown** - All registered students
  2. **Event Selection Dropdown** - All active events
  3. **QR Code Display** - Canvas with generated QR
  4. **Student & Event Info** - Display selected items

- **QR Data Format:** `{ "studentId": "uuid", "eventId": "uuid", "system": "taptrack" }`
- **Library:** qrcode.js for generation
- **Features:**
  - Real-time generation on dropdown change
  - Canvas rendering
  - Could add print/download functionality

- **Data Sources:** StudentModel.getAll(), EventModel.getActive()

### **frontend/pages/admin/qr_scanner.php** (QR SCANNING)
- **File Number:** 19/27
- **Type:** HTML/PHP template
- **Size:** ~200 lines
- **Purpose:** Real-time QR code scanning
- **Workflow:**

  1. **Event Selection** - Required before scanning
  2. **Camera Preview** - Live video in canvas
  3. **Start/Stop Buttons** - Control scanning
  4. **QR Detection** - html5-qrcode library
  5. **Validation** - Parse QR data and validate:
     - System === 'taptrack'
     - Event ID matches selected event
     - Student exists
  6. **Recording** - AJAX call to scan_qr endpoint
  7. **Result Display** - Green success / Red error message

- **Error Handling:**
  - Camera permission denied
  - No camera found
  - Camera in use by another application
  - Invalid QR format
  - Event mismatch

- **Libraries:** Html5Qrcode for scanning
- **Real-time:** Continuously scans for QR codes

### **frontend/pages/admin/attendance.php** (ATTENDANCE RECORDS)
- **File Number:** 20/27
- **Type:** HTML/PHP template
- **Size:** ~180 lines
- **Purpose:** View attendance records for events
- **Features:**

  - **Event Selector** - Dropdown of active events
  - **Attendance Table** - When event selected:
    - Columns: #, Name, Student Number, Course, Year, Scan Time
    - Rows: All students who attended
    - Sorted: Most recent first (scanned_at DESC)

  - **Attendance Count Badge** - Shows total attendees
  - **Empty State** - Message when no event selected

- **Data Source:** AttendanceController.getByEvent()
- **Rich Data:** Includes joined student information

### **frontend/pages/admin/archived.php** (ARCHIVED EVENTS)
- **File Number:** 21/27
- **Type:** HTML/PHP template
- **Size:** ~220 lines
- **Purpose:** View past/archived events with attendance details
- **Features:**

  - **Search Form** - Filter by:
    - Event name
    - Location
    - Date range

  - **Collapsible Event Cards** - Per archived event:
    - Event name, date, location
    - Attendance count badge
    - Expand/collapse arrow
    - Expandable attendance table with student details

  - **Attendance Details:**
    - Student name
    - Student number
    - Course, year
    - Scan timestamps

- **Data Source:** EventModel.getArchived()
- **JavaScript:** Collapsible functionality with smooth animation

---

### **frontend/css/styles.css** (STYLESHEET)
- **File Number:** 22/27
- **Type:** CSS stylesheet
- **Size:** ~600 lines
- **Purpose:** Complete responsive design system
- **Architecture:** Utility-first with component classes

  **Color Palette:**
  ```css
  --primary: rgb(39, 107, 69);      /* Forest green */
  --secondary: rgb(234, 179, 8);    /* Gold */
  --success: rgb(91, 168, 107);     /* Green */
  --destructive: rgb(239, 68, 68);  /* Red */
  --muted: rgb(229, 231, 235);      /* Light gray */
  ```

  **Spacing Scale:**
  ```css
  --space-1: 0.25rem    /* 4px */
  --space-2: 0.5rem    /* 8px */
  --space-3: 0.75rem   /* 12px */
  --space-4: 1rem      /* 16px */
  --space-6: 1.5rem    /* 24px */
  /* ... up to space-12: 3rem */
  ```

  **Typography:**
  - Font: Inter (Google Fonts)
  - Weights: 400, 500, 600, 700, 800
  - Sizes: 0.75rem to 2.25rem scale

  **Utility Classes:**
  - Layout: `flex`, `grid`, `block`, `inline-block`
  - Spacing: `m-4`, `p-6`, `gap-2`, `space-y-4`, `space-x-2`
  - Text: `text-sm`, `text-lg`, `font-bold`, `text-center`
  - Colors: `bg-primary`, `text-destructive`, `border-secondary`
  - Display: `hidden`, `block`, `flex`, `grid`
  - Responsive: `hide-mobile` (< 768px), `hide-desktop` (>= 768px)

  **Component Classes:**
  - `.card` - Card container with shadow and border
  - `.btn` - Button base
  - `.btn-primary`, `.btn-outline`, `.btn-destructive`, `.btn-ghost`
  - `.input`, `.select`, `.textarea` - Form elements
  - `.table` - Table styling
  - `.table-zebra` - Alternating row colors
  - `.badge`, `.badge-success`, `.badge-destructive`
  - `.modal`, `.modal-open` - Modal dialogs
  - `.tab`, `.tab-active` - Tab interface
  - `.collapsible` - Collapsible/accordion items
  - `.toast` - Notification messages
  - `.spinner`, `.spinner-sm`, `.spinner-lg` - Loading indicators

  **Responsive Design:**
  - **Mobile First:** Styles start at mobile (< 768px)
  - **Desktop Breakpoint:** 768px
  - **Sidebar Behavior:** Full width mobile → fixed 240px desktop
  - **Table Behavior:** Scroll on mobile → full display desktop
  - **Font Sizes:** Smaller on mobile for space efficiency

  **Animations:**
  - `@keyframes pulse` - Used for scanning line (face registration)
  - `@keyframes spin` - Loading spinner animation
  - Transitions on hover/focus for interactive elements

- **Coverage:** All frontend components styled (login, dashboard, tables, modals, etc.)

---

### **frontend/js/ui.js** (CLIENT-SIDE LOGIC)
- **File Number:** 23/27
- **Type:** JavaScript (Vanilla JS, no framework)
- **Size:** ~500 lines
- **Purpose:** All interactive features and integrations

  **Libraries Integrated:**
  - `face-api.js` - Facial recognition
  - `qrcode` - QR code generation
  - `Html5Qrcode` - QR code scanning
  - `html5-qrcode` (scanning library)

  **Function Categories:**

  **Authentication UI**
  - `switchTab(tabName)` - Switch between Student/Admin login tabs
  - `switchStudentMode(mode)` - Toggle Student login/register

  **QR Operations**
  - `toggleQR(eventId)` - Show/hide QR code for event
  - `generateQR(studentId, eventId)` - Create QR with qrcode.js
  - `startScanning(eventId)` - Initialize Html5Qrcode
  - `stopScanning()` - Stop camera
  - `onScanSuccess(decodedText)` - Handle QR detection
  - `showScanResult(success, message)` - Display result

  **Face Registration**
  - `startFaceCamera()` - Initialize webcam for face capture
  - `captureFace()` - Attempt face detection and descriptor extraction
  - `loadFaceModels()` - Load face-api.js models from CDN
  - `uploadFaceDescriptor(descriptor)` - Send to server via AJAX

  **Modal & UI**
  - `openModal(modalId)` - Show modal dialog
  - `closeModal(modalId)` - Hide modal dialog
  - `toggleCollapsible(header)` - Expand/collapse sections
  - `showToast(message, type)` - Notification toasts
  - `confirmAction(message)` - Confirmation dialogs
  - `redirect(url)` - Page navigation

  **Utilities**
  - `getJsonInput()` - Parse JSON request data
  - `isValidJSON(str)` - Validate JSON strings
  - Camera permission and error handling

  **Error Handling:**
  - Camera access denied
  - No camera available
  - Camera in use by another app
  - Face detection failures
  - QR format validation
  - Network error handling

- **Dependencies:** All external libs loaded from CDN in index.php
- **Browser Compatibility:** Modern browsers with:
  - getUserMedia API (camera access)
  - Canvas API
  - Fetch API
  - ES6 JavaScript

---

## 📄 Documentation Files

### **README.md** (MAIN DOCUMENTATION)
- **File Number:** 24/27
- **Type:** Markdown documentation
- **Size:** ~400 lines
- **Contents:**
  - Project overview
  - Feature list
  - File structure tree
  - Setup instructions (quick version)
  - Default credentials
  - Architecture overview
  - Key features guide
  - Database schema summary
  - API endpoints overview
  - Styling system
  - Troubleshooting
  - Future enhancements
  - Development notes
  - Security practices

### **SETUP.md** (INSTALLATION GUIDE)
- **File Number:** 25/27
- **Type:** Markdown documentation
- **Size:** ~500 lines
- **Contents:**
  - Prerequisites checklist
  - Database setup (phpMyAdmin & command line)
  - Configuration file editing
  - File placement instructions
  - Verification steps
  - Customization options
  - Security hardening
  - File permissions
  - Testing all features
  - Troubleshooting guide
  - Support information

### **API_REFERENCE.md** (DEVELOPER DOCUMENTATION)
- **File Number:** 26/27
- **Type:** Markdown documentation
- **Size:** ~700 lines
- **Contents:**
  - AJAX endpoint documentation (5 endpoints)
  - Request/response formats
  - Error codes
  - Controller method reference
  - Model method documentation
  - Helper function reference
  - Request/response flow diagrams
  - Error handling patterns
  - Extending the system
  - Code examples

### **FILELIST.md** (THIS FILE)
- **File Number:** 27/27
- **Type:** Markdown documentation
- **Size:** ~800 lines
- **Contents:** Complete file-by-file reference you're reading now

---

## 📊 Statistics

**Total Files Created:** 27
- **PHP Files:** 14 (backend/frontend logic and pages)
- **Configuration:** 2 (config.php, Database.php)
- **Database:** 1 (init.sql)
- **Frontend:** 10 (pages + styles + JavaScript)
- **Documentation:** 4 (README, SETUP, API_REFERENCE, FILELIST)
- **Backup:** 1 (index.php.backup)

**Total Lines of Code:** ~8,500+
- **PHP Backend:** ~3,500 lines
- **HTML/Frontend:** ~2,000 lines
- **CSS Styling:** ~600 lines
- **JavaScript:** ~500 lines
- **SQL Schema:** ~150 lines
- **Config:** ~100 lines
- **Documentation:** ~1,750 lines

**Directories Created:** 11
```
config/
database/
backend/
  models/
  controllers/
  helpers/
  routes/
frontend/
  pages/
    admin/
  css/
  js/
```

**Database Tables:** 4
- students
- events
- attendance
- audit_logs (optional)

**Database Indexes:** 10+
- Email lookups
- Student number lookups
- Date-based filtering
- Archive status filtering
- Timestamp sorting

**AJAX Endpoints:** 5
- scan_qr
- save_face_descriptor
- get_students
- get_face_descriptor
- record_attendance

**Model Methods:** 45+
- Student model: 14 methods
- Event model: 16 methods
- Attendance model: 18 methods

**Controller Methods:** 22+
- AuthController: 5 methods
- EventController: 8 methods
- AttendanceController: 9+ methods

**Helper Functions:** 32+
Security, validation, formatting, authentication utilities

**CSS Classes:** 100+
- Utility classes
- Component classes
- Responsive classes
- Animation classes

**JavaScript Functions:** 25+
UI interactions, face recognition, QR operations

---

## 🔄 Object Relationships

```
User
├── Student (via login/register)
│   ├── Face Descriptor (face-api.js data)
│   └── Attendance Records (multiple)
│       └── Event (references)
│
└── Admin (via admin login)
    └── Manages: Events, Students, Attendance

Event
├── Created by: Admin
├── Has many: Attendance Records
├── Referenced by: QR codes
└── Can transition: Active → Archived → Deleted

Attendance
├── Links: Student ↔ Event
├── Contains: Timestamps (check-in, check-out)
├── Unique: (student_id, event_id) constraint
└── Cascades: Delete when Student or Event deleted
```

---

## 🎯 Navigation Map

**For Student Users:**
1. Login/Register (`frontend/pages/login.php`)
2. Face Registration (`frontend/pages/face_register.php`)
3. Student Dashboard (`frontend/pages/student_dashboard.php`)
4. View upcoming events
5. Display QR code for scanning

**For Admin Users:**
1. Admin Login (`frontend/pages/login.php` - Admin tab)
2. Admin Dashboard (`frontend/pages/admin/dashboard.php`)
3. Event Management (`frontend/pages/admin/events.php`)
4. QR Operations:
   - Generate: `frontend/pages/admin/qr_generator.php`
   - Scan: `frontend/pages/admin/qr_scanner.php`
5. Attendance Tracking:
   - Current: `frontend/pages/admin/attendance.php`
   - Historical: `frontend/pages/admin/archived.php`

---

## 🚀 Deployment Checklist

- [ ] All 27 files created
- [ ] Database schema imported (init.sql)
- [ ] Configuration values set (config/config.php)
- [ ] Admin password changed
- [ ] File permissions set correctly
- [ ] .htaccess in place (optional but recommended)
- [ ] HTTPS enabled (production)
- [ ] Error logging configured
- [ ] Regular backups scheduled
- [ ] Users trained on system
- [ ] Test all features working
- [ ] Monitor error logs
- [ ] Plan maintenance schedule

---

## 📞 File Interaction Diagram

```
Browser Request
    ↓
index.php (entry point)
    ├─ Load config/config.php
    ├─ Load config/Database.php
    ├─ Load all Models & Controllers
    ├─ Load backend/routes/api.php
    ├─ Load frontend/js/ui.js
    ├─ Load frontend/css/styles.css
    │
    ├─ If AJAX Request
    │   └─ ApiRouter→route() → Controller → Model → Database
    │       └─ Return JSON
    │
    ├─ If POST Action
    │   └─ handlePostAction() → Controller → Model → Database
    │       └─ Redirect or set flash message
    │
    └─ If Page Request
        └─ checkPageAccess() → include frontend/pages/*.php
            └─ Render HTML with CSS/JS
```

---

**Documentation Version:** 2.0.0  
**Last Updated:** 2026-03-18  
**Total File Count:** 27 files  
**Total Size:** ~8,500+ lines of code & documentation
