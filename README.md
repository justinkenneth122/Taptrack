# Taptrack — QR Code Attendance System
## Refactored Multi-File Architecture (v2.0.0)

### 📋 Project Overview

**Taptrack** is a modern QR Code-based attendance tracking system designed for FEU Roosevelt Marikina. This is the refactored version with a professional, scalable multi-file architecture that replaces the original single-file application.

**Key Features:**
- ✅ Student registration and login with FEU email validation
- ✅ QR code generation and scanning for attendance tracking
- ✅ Event management (create, archive, view)
- ✅ Attendance analytics and reporting
- ✅ Admin dashboard with comprehensive statistics
- ✅ Responsive design for mobile and desktop

---

## 📁 Project Structure

```
Taptrack/
├── index.php                          # Main application entry point
├── config/
│   ├── config.php                     # Configuration (DB, admin credentials, settings)
│   └── Database.php                   # Database connection manager (Singleton pattern)
│
├── backend/
│   ├── helpers/
│   │   └── helpers.php               # Utility functions (UUID, validation, auth checks)
│   ├── models/
│   │   ├── Student.php               # Student database operations
│   │   ├── Event.php                 # Event database operations
│   │   └── Attendance.php            # Attendance database operations
│   ├── controllers/
│   │   ├── AuthController.php        # Login/registration logic
│   │   ├── EventController.php       # Event management logic
│   │   └── AttendanceController.php  # Attendance recording logic
│   └── routes/
│       └── api.php                   # AJAX API endpoints
│
├── frontend/
│   ├── pages/
│   │   ├── login.php                 # Login/register page
│   │   ├── face_register.php         # Face registration page
│   │   ├── student_dashboard.php     # Student dashboard
│   │   └── admin/
│   │       ├── admin_panel.php       # Admin layout & navigation
│   │       ├── dashboard.php         # Admin dashboard (statistics)
│   │       ├── events.php            # Event management
│   │       ├── qr_generator.php      # QR generation tool
│   │       ├── qr_scanner.php        # QR scanning tool
│   │       ├── attendance.php        # Attendance records view
│   │       └── archived.php          # Archived events view
│   ├── css/
│   │   └── styles.css               # All styling (Tailwind-like utilities + custom)
│   └── js/
│       └── ui.js                    # Client-side interactions & face recognition
│
├── database/
│   └── init.sql                     # Database schema (tables & structure)
│
└── index.php.backup                 # Original single-file version (backup)
```

---

## 🚀 Setup & Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL/MariaDB
- Web server (XAMPP, WAMP, or hosting with PHP support)

### Step 1: Database Setup

1. **Open phpMyAdmin** or your MySQL client
2. **Create database** named `taptrack`:
   ```sql
   CREATE DATABASE taptrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   USE taptrack;
   ```

3. **Import the schema** from `database/init.sql`:
   - Copy all SQL from the file and execute it
   - Or: In phpMyAdmin, go to Import tab and select `database/init.sql`

### Step 2: Configuration

Edit `config/config.php` if needed:
```php
$DB_HOST = 'localhost';     // MySQL host
$DB_NAME = 'taptrack';       // Database name
$DB_USER = 'root';          // Database user
$DB_PASS = '';              // Database password

$ADMIN_USER = 'admin';      // Admin username
$ADMIN_PASS = 'admin123';   // Admin password (CHANGE IN PRODUCTION)
```

### Step 3: Access the Application

Open your browser and navigate to:
```
http://localhost/Taptrack/
```

Or wherever you placed the application.

---

## 🔐 Default Login Credentials

### Student Account
- **Email:** `R20260101001@feuroosevelt.edu.ph` (or any R-number format)
- **Password:** Create during registration
- **Note:** Must match pattern: `R[8+ digits]@feuroosevelt.edu.ph`

### Admin Account  
- **Username:** `admin`
- **Password:** `admin123`
- **⚠️ IMPORTANT:** Change this in production!

---

## 🏗️ Architecture Overview

### Design Patterns Used

1. **Singleton Pattern** - Database connection (single instance across app)
2. **MVC Pattern** - Models, Controllers, Views separation
3. **Layered Architecture** - Config → Models → Controllers → Views
4. **Helper Functions** - Reusable utility functions (helpers.php)

### Code Organization

**Models** (`backend/models/`)
- Encapsulate database operations
- Methods for CRUD operations
- Database queries in one place

**Controllers** (`backend/controllers/`)
- Business logic and validation
- Interact with models
- Return results/errors in consistent format

**Frontend** (`frontend/pages/`)
- Clean HTML/PHP templates
- Minimal logic (mostly display)
- Separated by feature/module

**Helpers** (`backend/helpers/`)
- Security functions (e.g(), HTML escaping)
- Validation (email, password)
- Authentication checks (requireAdmin(), hasRole())
- Utilities (UUID generation, date formatting)

---

## 🔑 Key Features & Usage

### For Students

1. **Registration**
   - Enter FEU email (R-number format)
   - Set password
   - Enter personal details and program

2. **Face Registration**
   - Webcam-based face capture
   - Using face-api.js library
   - Stored as face descriptor (128-dim array)

3. **Attendance**
   - View upcoming events
   - Display personal QR code
   - Show to organizer for scanning

### For Administrators

1. **Dashboard**
   - View key metrics (events, students, scans, attendees)
   - Real-time statistics

2. **Event Management**
   - Create new events
   - Set date, location, description
   - Archive past events

3. **QR Tools**
   - **Generator:** Create QR codes for printing/sharing
   - **Scanner:** Real-time QR code scanning with camera

4. **Attendance Tracking**
   - View per-event attendance records
   - See student details (name, number, course)
   - Access archived attendance history
   - Search and filter events

---

## 📊 Database Schema

### Students Table
```
Stores student account information
- id: UUID primary key
- email: FEU email (unique)
- first_name, last_name: Student name
- student_number: R-number from email
- course: Academic program
- year_level: 1st-5th year
- password: Login password
- face_descriptor: JSON face recognition data
- created_at, updated_at: Timestamps
```

### Events Table
```
Stores event information
- id: UUID primary key
- name: Event name
- date: Event date
- location: Venue/location
- description: Optional details
- archived: 0=active, 1=archived
- created_at, updated_at: Timestamps
```

### Attendance Table
```
Stores attendance records
- id: UUID primary key
- student_id: FK to students
- event_id: FK to events
- scanned_at: Check-in timestamp
- scanned_out_at: Optional check-out timestamp
- Unique constraint: per (student, event)
```

### Audit Logs Table (Optional)
```
Stores administrative actions for auditing
- action: Action type
- entity_type: What was modified
- entity_id: Which entity
- user_id: Who performed action
- details: Additional info
- ip_address: Source IP
```

---

## 🔄 API Endpoints (AJAX)

All endpoints require JSON request with `Content-Type: application/json`

### `?ajax=scan_qr` (POST)
**Scans QR code and records attendance**
```json
{
  "studentId": "uuid",
  "eventId": "uuid",
  "system": "taptrack"
}
```

### `?ajax=save_face_descriptor` (POST)
**Saves face recognition data for student**
```json
{
  "student_id": "uuid",
  "face_descriptor": "[array of 128 values]"
}
```

### `?ajax=get_students` (GET)
**Retrieves list of all students** (Admin only)
Returns JSON array of student objects

### `?ajax=get_face_descriptor` (GET)
**Retrieves face descriptor for a student**
```
?ajax=get_face_descriptor&student_id=uuid
```

---

## 🎨 Styling & UI

### CSS System
- **Variables:** CSS custom properties for theming
- **Utility Classes:** Tailwind-like utilities (flex, gap, text-sm, etc.)
- **Component Classes:** Cards, buttons, forms, tables
- **Responsive:** Mobile-first design, breakpoint at 768px

### Color Scheme
```css
--primary: Forest green (RGB: 39, 107, 69)
--secondary: Gold/Yellow (RGB: 234, 179, 8)
--success: Green (RGB: 91, 168, 107)
--destructive: Red (RGB: 239, 68, 68)
--muted: Light gray (RGB: 229, 231, 235)
```

### Responsive Breakpoints
```css
Mobile: < 768px (sidebar collapses, hide non-essential columns)
Desktop: >= 768px (full layout)
```

---

## 🔒 Security Features

1. **Session Management**
   - PHP sessions for user authentication
   - Role-based access control (student/admin)

2. **Input Validation**
   - Email format validation (FEU domain)
   - Required field checks
   - Type validation

3. **Output Escaping**
   - `e()` function for HTML escaping
   - Prevents XSS attacks

4. **SQL Injection Prevention**
   - Prepared statements with PDO
   - Parameter binding

5. **Security Headers**
   - `X-Content-Type-Options: nosniff`
   - `X-Frame-Options: SAMEORIGIN`
   - `X-XSS-Protection`

### Recommendations for Production

- [ ] **Hash passwords:** Use `password_hash()` and `password_verify()`
- [ ] **HTTPS:** Enable SSL/TLS encryption
- [ ] **Change admin credentials:** Update in `config/config.php`
- [ ] **Rate limiting:** Add login attempt limits
- [ ] **CSRF tokens:** Add CSRF protection for forms
- [ ] **Audit logging:** Enable and monitor audit_logs table
- [ ] **Database backup:** Regular automated backups
- [ ] **Update dependencies:** Keep libraries current

---

## 📱 Face Recognition System

### How It Works

1. **Capture Phase**
   - User allows camera access
   - Live video stream to canvas
   - Guide circle overlay for positioning

2. **Detection Phase**
   - face-api.js processes video frames
   - Detects face and landmarks
   - Extracts 128-dimensional face descriptor

3. **Storage Phase**
   - Descriptor sent to server
   - Stored in JSON format in `students.face_descriptor`

4. **Verification Phase** (for future use)
   - Compare new descriptor with stored
   - Calculate similarity distance
   - Threshold-based matching

### Libraries
- **face-api.js:** Face detection & recognition
- **TensorFlow.js:** ML inference engine
- **HTML5 Canvas:** Webcam capture

---

## 🐛 Troubleshooting

### Database Connection Error
**Error:** "Unknown database 'taptrack'"
**Solution:** 
1. Run SQL from `database/init.sql` to create tables
2. Verify MySQL is running
3. Check credentials in `config/config.php`

### Camera Not Working
**Error:** "Camera access denied" or "No camera found"
**Solutions:**
- Check browser permissions (camera)
- Ensure using HTTPS or localhost
- Try different camera device
- Restart browser

### Face Recognition Failing
**Error:** "Could not capture a stable selfie frame"
**Solutions:**
- Ensure good lighting
- Center face in circle guide
- Remove glasses/face masks
- Ensure camera is clean

### QR Code Not Scanning
**Error:** "Invalid QR code — not a Taptrack code"
**Solutions:**
- Ensure QR is generated from Taptrack
- Check camera focus quality
- Avoid glare/reflections
- Try different distance/angle

---

## 🚀 Future Enhancements

1. **Features**
   - Real-time attendance statistics
   - Email notifications
   - SMS alerts
   - Report generation (PDF/Excel)
   - Attendance history by student
   - Bulk event import

2. **Security**
   - Two-factor authentication (2FA)
   - OAuth/LDAP integration
   - Encrypted database fields
   - API key authentication

3. **Performance**
   - Database indexing optimization
   - Caching layer (Redis)
   - Query optimization
   - Lazy loading of records

4. **UX**
   - Dark mode theme
   - Multi-language support
   - Progressive Web App (PWA)
   - Mobile native apps

5. **Integration**
   - Google Calendar sync
   - Google Classroom integration
   - Slack notifications
   - Webhooks for external systems

---

## 📝 Development Notes

### Adding a New Feature

1. **Create model** in `backend/models/NewModel.php`
2. **Create controller** in `backend/controllers/NewController.php`
3. **Create view** in `frontend/pages/new_page.php`
4. **Add route** in `index.php` (handle POST/GET)
5. **Add CSS** to `frontend/css/styles.css` if needed
6. **Add JavaScript** to `frontend/js/ui.js` if needed
7. **Test thoroughly** in both roles (student/admin)

### Code Style Guidelines

- **PHP:** PSR-12 (modern PHP standard)
- **Naming:** camelCase for variables/methods, PascalCase for classes
- **Functions:** Keep small and focused (single responsibility)
- **Comments:** Add for complex logic, not obvious code
- **Security:** Always escape output, validate input

---

## 📄 License & Attribution

**Original Concept:** QR Code Attendance System for FEU Roosevelt Marikina
**Refactored Architecture:** Professional multi-file scalable structure
**License:** Educational use only

---

## 👥 Support & Contribution

For issues, suggestions, or contributions:
1. Test thoroughly before reporting
2. Provide clear reproduction steps
3. Include error messages and browser console logs
4. Suggest specific improvements

---

## ✅ Checklist for Deployment

- [ ] Database created and tables imported
- [ ] `config/config.php` updated with correct credentials
- [ ] Admin password changed (recommendation)
- [ ] `frontend/` folder has read permissions
- [ ] `database/` folder is not web-accessible
- [ ] HTTPS enabled
- [ ] Error logging configured
- [ ] Backup strategy in place
- [ ] Tested on target server
- [ ] Users trained on system usage

---

**Version:** 2.0.0 (Refactored)  
**Last Updated:** 2026-03-18  
**PHP Version Required:** 7.4+  
**Database:** MySQL 5.7+ or MariaDB 10.3+
