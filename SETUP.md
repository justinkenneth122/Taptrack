# Taptrack Setup Guide
## Complete Installation & Configuration

---

## ⚙️ Step 1: Prerequisites Check

Verify you have the following installed:

### PHP
```bash
php -v
# Expected output: PHP 7.4.0 or higher
```

### MySQL/MariaDB
```bash
mysql -V
# Expected output: MySQL 5.7+ or MariaDB 10.3+
```

### XAMPP Users
XAMPP comes with Apache, PHP, and MySQL. Just ensure:
1. **Apache** is running (use XAMPP Control Panel)
2. **MySQL** is running (use XAMPP Control Panel)
3. **PHP directory** is in PATH (optional, for command-line operations)

---

## 🗄️ Step 2: Database Setup (Detailed)

### Option A: Using phpMyAdmin (Recommended for XAMPP)

1. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Click "Start" next to Apache
   - Click "Start" next to MySQL

2. **Open phpMyAdmin**
   - Navigate to `http://localhost/phpmyadmin/`
   - You should see phpMyAdmin interface

3. **Create Database**
   - Click "New" in left sidebar
   - Enter database name: `taptrack`
   - Character set: **utf8mb4**
   - Collation: **utf8mb4_unicode_ci**
   - Click "Create"

4. **Import Tables**
   - Click on `taptrack` database
   - Click "Import" tab
   - Click "Choose File" and select `database/init.sql` from your project
   - Click "Import" button
   - You should see success message

5. **Verify Tables**
   - Click on `taptrack` database
   - You should see 4 tables:
     - `students`
     - `events`
     - `attendance`
     - `audit_logs`

### Option B: Using Command Line (Terminal/PowerShell)

1. **Connect to MySQL**
   ```bash
   mysql -u root -p
   # Press Enter if no password (default for XAMPP)
   ```

2. **Create Database**
   ```sql
   CREATE DATABASE taptrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   USE taptrack;
   ```

3. **Import Schema**
   ```sql
   source C:\xampp\htdocs\Taptrack\database\init.sql;
   ```
   
   Or paste the entire contents of `database/init.sql`

4. **Verify**
   ```sql
   SHOW TABLES;
   ```
   You should see: `attendance`, `audit_logs`, `events`, `students`

---

## 🔧 Step 3: Configure Application

### Edit `config/config.php`

Open the file in your text editor and update these settings:

```php
<?php
// === DATABASE CONFIGURATION ===
$DB_HOST = 'localhost';      // MySQL host (usually localhost)
$DB_NAME = 'taptrack';        // Your database name
$DB_USER = 'root';            // MySQL username
$DB_PASS = '';                // MySQL password (empty for XAMPP default)

// === ADMINISTRATOR CREDENTIALS ===
// IMPORTANT: Change these in production!
$ADMIN_USER = 'admin';        // Admin login username
$ADMIN_PASS = 'admin123';     // Admin login password

// === APPLICATION SETTINGS ===
$EMAIL_PATTERN = '/^R\d{8,}@feuroosevelt\.edu\.ph$/i';  // FEU email format
$TIMEZONE = 'Asia/Manila';    // Application timezone

// === COURSES OFFERED ===
$COURSES = [
    'Engineering' => 'BS in Civil Engineering',
    'IT' => 'BS in Information Technology',
    'ComSci' => 'BS in Computer Science',
    // ... add more as needed
];

// === YEAR LEVELS ===
$YEAR_LEVELS = [1, 2, 3, 4, 5];  // Academic year levels
```

### Security Recommendations

⚠️ **IMPORTANT for Production:**

1. **Change Admin Password**
   ```php
   $ADMIN_PASS = 'your_new_secure_password';  // Use strong password!
   ```

2. **Use Environment Variables** (for hosting)
   ```php
   // Instead of hardcoding, use:
   $DB_HOST = getenv('DB_HOST') ?: 'localhost';
   $DB_USER = getenv('DB_USER') ?: 'root';
   $DB_PASS = getenv('DB_PASS') ?: '';
   ```

3. **Create `.env` file** (do not commit to version control)
   ```
   DB_HOST=localhost
   DB_NAME=taptrack
   DB_USER=root
   DB_PASS=your_password
   ADMIN_USER=admin
   ADMIN_PASS=secure_password
   ```

---

## 🚀 Step 4: File Placement

Ensure files are placed correctly in your web root:

### XAMPP Installation
```
C:\xampp\htdocs\Taptrack\
├── All project files here
```

Make sure `index.php` is at the root level.

### Other Hosting
```
/public_html/Taptrack/  (or your root directory)
├── All project files here
```

Check with your hosting provider for the correct base directory.

---

## ✅ Step 5: Verify Installation

### Open Application in Browser

Navigate to:
```
http://localhost/Taptrack/
```

You should see:
- Login page with two tabs: "Student" and "Admin"
- Student tab: Email and password fields
- Admin tab: Username and password fields

### Test Admin Login

1. Click "Admin" tab
2. Enter:
   - **Username:** `admin`
   - **Password:** `admin123`
3. Click "Login"
4. You should see the **Admin Dashboard** with navigation sidebar

### Test Student Registration

1. Click "Student" tab (the "Register" sub-tab)
2. Enter:
   - **Email:** `R20260101001@feuroosevelt.edu.ph`
   - **First Name:** `John`
   - **Last Name:** `Doe`
   - **Course:** Select any course
   - **Year Level:** Select any year
   - **Password:** Any password (should be 6+ characters)
3. Click "Register"
4. You should be redirected to face registration page

---

## 🎨 Step 6: Customize Configuration

### Change Application Name

Edit `config/config.php`:
```php
$APP_NAME = 'Taptrack';  // Add this if creating new variable
```

Update in HTML templates as needed.

### Add More Courses

Edit `config/config.php`:
```php
$COURSES = [
    'Engineering' => 'BS in Civil Engineering',
    'IT' => 'BS in Information Technology',
    'ComSci' => 'BS in Computer Science',
    'Business' => 'BS in Business Administration',
    'Nursing' => 'BS in Nursing',
    // Add more courses
];
```

### Change Email Validation Pattern

If not using FEU email format, edit `config/config.php`:
```php
// For any email:
$EMAIL_PATTERN = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

// For university domain:
$EMAIL_PATTERN = '/^[a-zA-Z0-9._%+-]+@youruniversity\.edu\.ph$/i';
```

---

## 🔒 Step 7: Security Hardening

### For Development (Local XAMPP)

1. ✅ Database created
2. ✅ Application accessible
3. ✅ Users can log in and register

### For Production (Live Server)

Before deploying, ensure:

1. **Hash Passwords**
   - Update `AuthController.php` to use password hashing:
   ```php
   // Instead of: $password_hash = md5($password);
   $password_hash = password_hash($password, PASSWORD_BCRYPT);
   
   // For verification:
   if (password_verify($password, $stored_hash)) { }
   ```

2. **Enable HTTPS**
   - Install SSL certificate
   - Redirect HTTP to HTTPS in `.htaccess`
   ```apache
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

3. **Hide Error Messages**
   Already done in `index.php`:
   ```php
   ini_set('display_errors', 0);  // Don't show errors to users
   ini_set('log_errors', 1);      // Log errors to file
   ```

4. **Strengthen Admin Credentials**
   - Use API keys for programmatic access
   - Add CSRF tokens to all forms
   - Implement 2FA (two-factor authentication)

5. **Database Security**
   - Remove default user accounts
   - Use strong database password
   - Restrict database access by IP
   - Regular automated backups

6. **File Permissions** (Linux/Unix hosting)
   ```bash
   chmod 755 .                    # Application directory
   chmod 644 *.php                # PHP files
   chmod 755 backend/             # Backend directory
   chmod 644 config/config.php    # Protect config
   chmod 600 database/init.sql    # Protect database
   ```

---

## 🗂️ Step 8: File Permissions & Folder Structure

### Required Directories

Ensure these folders exist and are writable:

```bash
C:\xampp\htdocs\Taptrack\
├── config/              # ✓ Readable (contains config.php)
├── backend/             # ✓ Readable
├── frontend/            # ✓ Readable
├── database/            # ✓ Readable (never web-accessible)
└── (temp, uploads, logs) # ✗ Not included, but create if needed
```

### Hide Sensitive Folders (Apache/XAMPP)

Create `.htaccess` file in root directory:
```apache
# Deny direct access to backend and database folders
<Directory "backend">
    Deny from all
</Directory>

<Directory "database">
    Deny from all
</Directory>

<Directory "config">
    Deny from all
</Directory>

# Only allow index.php to be accessed directly
<FilesMatch "\.php$">
    Deny from all
</FilesMatch>

<Files "index.php">
    Allow from all
</Files>
```

This prevents people from accessing `/backend/models/Student.php` directly — all access goes through `index.php`.

---

## 🧪 Step 9: Test All Features

### 1. **Admin Login**
   - [ ] Navigate to login page
   - [ ] Click "Admin" tab
   - [ ] Enter `admin` / `admin123`
   - [ ] Should see dashboard with stats

### 2. **Create Event**
   - [ ] In admin panel, click "Events"
   - [ ] Click "Add Event" button
   - [ ] Fill form (name, date, location)
   - [ ] Event appears in table

### 3. **Student Registration**
   - [ ] Logout from admin
   - [ ] Click "Student" tab (Register)
   - [ ] Fill all fields with valid data
   - [ ] Should be redirected to face registration

### 4. **Face Registration**
   - [ ] Allow camera access when prompted
   - [ ] Face appears in camera preview
   - [ ] Wait for "Face Captured Successfully"
   - [ ] Click "Continue to Dashboard"

### 5. **QR Generation**
   - [ ] In admin panel, click "QR Generator"
   - [ ] Select student from dropdown
   - [ ] Select event from dropdown
   - [ ] QR code appears
   - [ ] Can print or screenshot for testing

### 6. **QR Scanning**
   - [ ] In admin panel, click "QR Scanner"
   - [ ] Select event from dropdown
   - [ ] Click "Start Camera"
   - [ ] Allow camera access
   - [ ] Point at QR code
   - [ ] Should show "Attendance Recorded" message

### 7. **Student Dashboard**
   - [ ] Login as student (R-number email)
   - [ ] Should see upcoming events
   - [ ] QR code appears when clicking event
   - [ ] Should show as attended after admin scans

### 8. **Attendance Records**
   - [ ] In admin panel, click "Attendance"
   - [ ] Select event from dropdown
   - [ ] Should see list of students who attended
   - [ ] Shows name, student number, course, scan time

---

## 🐛 Step 10: Troubleshooting

### Database Connection Issues

**Problem:** "SQLSTATE[HY000]: General error: 1030 Got error..."

**Solution:**
1. Check MySQL is running (XAMPP Control Panel)
2. Verify database name matches `config.php`
3. Verify credentials are correct
4. Test connection:
   ```bash
   mysql -u root -p -e "USE taptrack; SHOW TABLES;"
   ```

### File Not Found Errors

**Problem:** "Fatal error: require_once(...) failed"

**Solution:**
1. Check all files are in correct directories
2. Verify file paths in `index.php` use correct case
3. Check `backend/` and `frontend/` folders exist
4. Ensure no spaces in folder names

### Camera Not Working

**Problem:** "Camera access denied in face registration"

**Solution:**
1. Check browser permissions (Chrome Settings → Privacy → Camera)
2. Must use HTTPS for production (localhost works on HTTP)
3. Try different browser (Firefox, Chrome, Edge)
4. Check camera works in other applications

### Session Issues

**Problem:** "Not logged in" after login

**Solution:**
1. Ensure PHP sessions are enabled
2. Check cookies are allowed in browser
3. Try incognito/private window
4. Clear browser cache and cookies

---

## 📞 When You Need Help

If something doesn't work:

1. **Check Error Logs**
   - XAMPP: `C:\xampp\apache\logs\error.log`
   - Check browser console (F12 → Console tab)

2. **Database Testing**
   - Verify tables exist in phpMyAdmin
   - Check data is being saved

3. **PHP Testing**
   - Create test file `test.php`:
   ```php
   <?php
   phpinfo();
   ?>
   ```
   - Navigate to `http://localhost/Taptrack/test.php`
   - Check PHP version and extensions

4. **Create Backup**
   - Export database regularly
   - Keep backup of config.php

---

## 🎯 Next Steps

Once installation is complete:

1. ✅ Customize configuration for your institution
2. ✅ Add your program/course list
3. ✅ Update admin password
4. ✅ Create test events
5. ✅ Test with sample students
6. ✅ Train users on system usage
7. ✅ Set up regular database backups
8. ✅ Monitor application performance

---

**Setup Version:** 2.0.0  
**Last Updated:** 2026-03-18
