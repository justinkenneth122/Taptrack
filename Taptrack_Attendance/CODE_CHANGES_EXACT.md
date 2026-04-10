# 🔧 EXACT CODE CHANGES: Copy-Paste Guide

This document shows the **exact lines** to change in each file. Copy and paste these changes to fix the bug.

---

## FILE 1: `pages/student-dashboard.php`

### CHANGE: Filtering Logic (Lines 1-26)

**Location:** At the top of the file, after getting student info

**REPLACE THIS:**
```php
<?php
/**
 * Student Dashboard Page
 */

$student_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();
if (!$student) { header('Location: ?page=login'); exit; }

$events = $pdo->query("SELECT * FROM events WHERE archived = 0 ORDER BY date")->fetchAll();
$stmt = $pdo->prepare("SELECT event_id FROM attendance WHERE student_id = ?");
$stmt->execute([$student_id]);
$attended = array_column($stmt->fetchAll(), 'event_id');
?>
```

**WITH THIS:**
```php
<?php
/**
 * Student Dashboard Page
 */

$student_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();
if (!$student) { header('Location: ?page=login'); exit; }

// ============ FIXED: Filter events by student program ============
// Get student's program (stored in 'course' field)
$student_program = trim($student['course'] ?? '');

// BACKEND FILTERING: Only fetch events where student is eligible
// Include events where:
// 1. Event.programs contains "ALL" (open to all)
// 2. Event.programs contains student's program
$stmt = $pdo->prepare("
    SELECT * FROM events 
    WHERE archived = 0 
    AND (
        JSON_CONTAINS(programs, JSON_QUOTE('ALL'), '$')
        OR JSON_CONTAINS(programs, JSON_QUOTE(?), '$')
    )
    ORDER BY date
");
$stmt->execute([$student_program]);
$events = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT event_id FROM attendance WHERE student_id = ?");
$stmt->execute([$student_id]);
$attended = array_column($stmt->fetchAll(), 'event_id');
?>
```

**Key Changes:**
- Line 13: `trim()` added to get clean program name
- Lines 15-28: New database query with JSON filtering
- Line 29-31: Attendance query moved after event filtering

---

## FILE 2: `modules/handlers.php`

### CHANGE #1: Student Program Trimming (Around Line 251)

**FIND THIS SECTION:**
```php
            // Use 'course' as program (map from database schema)
            $student_program = $student['course'] ?? '';
            $student_name = $student['first_name'] . ' ' . $student['last_name'];
```

**REPLACE WITH:**
```php
            // Use 'course' as program (map from database schema)
            // FIX: Trim whitespace to prevent matching issues
            $student_program = trim($student['course'] ?? '');
            $student_name = $student['first_name'] . ' ' . $student['last_name'];
```

**What Changed:**
- Added `trim()` around the program name to remove extra spaces

---

### CHANGE #2: Add Whitespace Normalization (Around Line 293)

**FIND THIS SECTION:**
```php
            // ======================== STEP 3: Program Authorization ========================
            $allowed_programs = json_decode($event['programs'] ?? '["ALL"]', true);
            
            // Check if event is open to all programs or if student's program is in the list
            $is_authorized = false;
```

**REPLACE WITH:**
```php
            // ======================== STEP 3: Program Authorization ========================
            $allowed_programs = json_decode($event['programs'] ?? '["ALL"]', true);
            
            // FIX: Normalize program names by trimming whitespace
            // This ensures "BS Information Technology" matches regardless of extra spaces
            if (is_array($allowed_programs)) {
                $allowed_programs = array_map('trim', $allowed_programs);
            }
            
            // Check if event is open to all programs or if student's program is in the list
            $is_authorized = false;
```

**What Changed:**
- Added lines to trim whitespace from each allowed program
- Prevents matching failures due to extra spaces in JSON

---

## FILE 3: `pages/admin/debug-events.php` (NEW FILE - OPTIONAL)

**Action:** Create a new file with the debug tool for verification

**Create:** `pages/admin/debug-events.php`

Use the content from the debug tool file (see previous section).

**Purpose:** Helps verify the fix is working correctly

**Access:** `/index.php?page=debug_events` (Admin only)

---

## FILE 4: Documentation (NEW FILES - OPTIONAL BUT RECOMMENDED)

Create these new documentation files:

1. **`BUG_FIX_PROGRAM_VISIBILITY.md`** - Detailed bug analysis
2. **`FIX_SUMMARY.md`** - Quick summary of the fix

Use the content from the previous sections.

---

## ✅ VERIFICATION CHECKLIST

After making the changes, verify:

### Code Changes
- [ ] `pages/student-dashboard.php` - Updated event query with JSON filtering
- [ ] `modules/handlers.php` - Added trim() on line ~251
- [ ] `modules/handlers.php` - Added trim() normalization on line ~293
- [ ] Optional: Added `pages/admin/debug-events.php` for verification

### Testing
- [ ] Log in as IT student → Should see only IT events
- [ ] Log in as Psychology student → Should see only Psychology events
- [ ] Both should see "ALL" events
- [ ] Unauthorized students can't see restricted events
- [ ] QR scanning still works normally

### Database
- [ ] Programs field is valid JSON (e.g., `["IT", "Psychology"]`)
- [ ] No syntax errors in SQL queries
- [ ] Student course values match event program names

---

## 🧪 QUICK TEST

After making changes:

1. **Test Event Visibility:**
   ```bash
   # Log in as different student programs
   # Verify correct events show for each program
   ```

2. **Test QR Scanning:**
   ```bash
   # Scan QR code as authorized student → Should work ✅
   # Scan QR code as unauthorized student → Should fail ❌
   ```

3. **Run Debug Tool (Optional):**
   ```bash
   # Go to: /index.php?page=debug_events
   # Check all sections are green ✅
   ```

---

## 📊 Side-by-Side Comparison

### Query Before vs After

**BEFORE:**
```sql
SELECT * FROM events 
WHERE archived = 0 
ORDER BY date
-- Returns: ALL events to ALL students
```

**AFTER:**
```sql
SELECT * FROM events 
WHERE archived = 0 
AND (
    JSON_CONTAINS(programs, JSON_QUOTE('ALL'), '$')
    OR JSON_CONTAINS(programs, JSON_QUOTE('BS Information Technology'), '$')
)
ORDER BY date
-- Returns: Only matching events to each student
```

---

## 🔒 Security

These changes ensure:
- ✅ Only eligible students see events
- ✅ Backend filtering (can't be bypassed)
- ✅ Program validation on QR scanning (extra layer)
- ✅ Clear error messages if unauthorized

---

## 📝 Change Summary

| File | Line(s) | Type | Impact |
|------|---------|------|--------|
| `student-dashboard.php` | 11-28 | Query | CRITICAL - Main fix |
| `handlers.php` | ~251 | Trim | Important - Robustness |
| `handlers.php` | ~293 | Normalize | Important - Robustness |
| `debug-events.php` | NEW | Tool | Optional - Verification |

---

## ⚠️ IMPORTANT NOTES

1. **Database Field Names:**
   - Student program is in: `students.course`
   - Event programs are in: `events.programs` (JSON array)
   - Make sure these field names match your database!

2. **JSON Format:**
   - Programs must be stored as JSON: `["IT", "Psychology"]`
   - NOT as string: `"IT,Psychology"`
   - NOT as plain text: `IT`

3. **Program Names:**
   - Must match exactly (case-sensitive)
   - "BS Information Technology" ≠ "BS IT"
   - Remove extra spaces when entering

4. **Backward Compatibility:**
   - Old QR codes still work (eventId fallback)
   - No database migration needed
   - Can be deployed immediately

---

## 🚀 Deployment Steps

1. **Backup Database** (Safety first!)
   ```bash
   mysqldump -u root -p taptrack > backup_before_fix.sql
   ```

2. **Update PHP Files:**
   - Edit `pages/student-dashboard.php`
   - Edit `modules/handlers.php`
   - Add `pages/admin/debug-events.php` (optional)

3. **Test Locally:**
   ```bash
   # Test with different user programs
   # Verify filtering works correctly
   ```

4. **Deploy to Production:**
   ```bash
   # Upload changed files
   # Clear browser cache (Ctrl+Shift+Delete)
   # Test with real users
   ```

5. **Verify:**
   - Test event visibility for each program
   - Test QR scanning
   - Run debug tool to verify all green ✅

---

## 🎓 How to Find the Lines

### In `pages/student-dashboard.php`
1. Open the file in your editor
2. Find the line: `$events = $pdo->query("SELECT * FROM events WHERE archived = 0 ORDER BY date")->fetchAll();`
3. This is around **line 11** in the file
4. Replace it with the new filtering query

### In `modules/handlers.php`
1. Open the file
2. Find the line: `case 'scan_qr':`
3. Inside the scan_qr case, find: `$student_program = $student['course'] ?? '';`
4. This should be around **line 251**
5. Add `trim()` around it
6. Further down (around line 293), add the normalization code after getting `$allowed_programs`

---

## ✨ Final Checklist

Before going live:

- [ ] Code changes applied to all files
- [ ] Files saved and syntax is correct
- [ ] No PHP errors in error log
- [ ] Tested with multiple student programs
- [ ] QR scanning still works
- [ ] Debug tool shows all green (if using)
- [ ] Database backup exists
- [ ] Ready for production deployment

---

**Questions?** Check:
- `FIX_SUMMARY.md` - High-level overview
- `BUG_FIX_PROGRAM_VISIBILITY.md` - Detailed explanation
- `QUICK_REFERENCE.md` - Quick lookup

Good luck with the fix! 🚀
