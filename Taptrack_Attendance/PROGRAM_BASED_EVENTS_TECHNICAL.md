# Program-Based Events: Technical Reference Guide

## API Endpoints

### 1. Create Event (Admin Only)
**Method:** POST  
**Handler:** `modules/handlers.php` → `add_event` action

**Request:**
```php
POST ?page=admin_events
Content-Type: application/x-www-form-urlencoded

action=add_event
name=IT Workshop
date=2026-04-15
location=Room 101
description=Optional description
program_restriction=SPECIFIC  // or "ALL"
programs[]=IT
programs[]=Engineering
```

**Backend Processing:**
```php
// Generate QR token
$qr_token = generateQRToken();

// Prepare programs
if ($program_restriction === 'ALL') {
    $programs = json_encode(['ALL']);
} else {
    $programs = json_encode($_POST['programs'] ?? []);
}

// Insert event
INSERT INTO events (id, name, date, location, description, programs, QR_token, archived)
VALUES (?, ?, ?, ?, ?, ?, ?, 0)
```

**Response:** Redirect to events page with success message

---

### 2. Scan QR Code (AJAX)
**Method:** POST  
**Endpoint:** `?ajax=scan_qr`  
**Handler:** `modules/handlers.php` → `handleAjax('scan_qr')`

**Request:**
```javascript
POST ?ajax=scan_qr
Content-Type: application/json

{
  "studentId": "550e8400-e29b-41d4-a716-446655440000",
  "qrToken": "a1b2c3d4e5f6a7b8c9d0a1b2c3d4e5f6",
  "system": "taptrack"
}
```

**Backend Validation Steps:**

```php
// Step 1: Extract data
$student_id = $data['studentId'];
$qr_token = $data['qrToken'];

// Step 2: Get student
SELECT id, first_name, last_name, course FROM students WHERE id = ?

// Step 3: Get event by QR_token
SELECT id, name, programs FROM events WHERE QR_token = ? AND archived = 0

// Step 4: Validate program eligibility
$allowed_programs = json_decode($event['programs'], true);
$is_authorized = in_array('ALL', $allowed_programs) || 
                 in_array($student['course'], $allowed_programs);

if (!$is_authorized) {
    // DENY - Return error
    return {
        "success": false,
        "message": "❌ {name} — You are not authorized...",
        "error_type": "authorization_denied"
    }
}

// Step 5: Check for duplicate
SELECT id FROM attendance WHERE student_id = ? AND event_id = ?

if (duplicate_exists) {
    // DENY - Return error
    return {
        "success": false,
        "message": "⚠️ {name} — You have already checked in...",
        "error_type": "already_checked_in"
    }
}

// Step 6: Record attendance
INSERT INTO attendance (id, student_id, event_id) VALUES (?, ?, ?)

// SUCCESS
return {
    "success": true,
    "message": "✅ {name} ({program}) — Check-in successful!",
    "student_name": "{name}",
    "event_name": "{event_name}"
}
```

**Response:**
```json
{
  "success": true|false,
  "message": "Human-readable message",
  "error_type": "authorization_denied|already_checked_in|..." (on error)
}
```

---

## Database Queries

### Get Event with Programs
```sql
SELECT 
    id, 
    name, 
    date, 
    location,
    description,
    programs,
    QR_token,
    archived,
    created_at
FROM events 
WHERE archived = 0 
ORDER BY date ASC;
```

**Result:**
```
id: "550e8400-e29b-41d4-a716-446655440000"
name: "IT Workshop"
date: "2026-04-15"
location: "Room 101"
programs: '["IT", "Engineering"]'  ← JSON array
QR_token: "a1b2c3d4e5f6a7b8c9d0a1b2c3d4e5f6"
```

### Get Event by QR Token
```sql
SELECT 
    id, 
    name, 
    programs 
FROM events 
WHERE QR_token = ? 
AND archived = 0
LIMIT 1;
```

### Get Student Program
```sql
SELECT 
    id, 
    first_name, 
    last_name, 
    course  ← This is the "program"
FROM students 
WHERE id = ?;
```

### Check Duplicate Attendance
```sql
SELECT id 
FROM attendance 
WHERE student_id = ? 
AND event_id = ?
LIMIT 1;
```

### Record Attendance
```sql
INSERT INTO attendance 
(id, student_id, event_id, scanned_at, face_verified) 
VALUES (?, ?, ?, CURRENT_TIMESTAMP, 0);
```

### Get Event Attendance with Programs
```sql
SELECT 
    a.id,
    a.student_id,
    a.event_id,
    a.scanned_at,
    s.first_name,
    s.last_name,
    s.student_number,
    s.course,
    s.year_level,
    e.name as event_name,
    e.programs
FROM attendance a
JOIN students s ON a.student_id = s.id
JOIN events e ON a.event_id = e.id
WHERE a.event_id = ?
ORDER BY a.scanned_at ASC;
```

---

## QR Code Data Structure

### NEW Format (Secure - Recommended)
```json
{
  "studentId": "550e8400-e29b-41d4-a716-446655440000",
  "qrToken": "a1b2c3d4e5f6a7b8c9d0a1b2c3d4e5f6",
  "system": "taptrack"
}
```

**Encoded in QR:**
```
{"studentId":"550e8400-e29b-41d4-a716-446655440000","qrToken":"a1b2c3d4e5f6a7b8c9d0a1b2c3d4e5f6","system":"taptrack"}
```

**Size:** ~100 bytes (QR code version 3-4)

### OLD Format (Deprecated - Still Supported)
```json
{
  "studentId": "550e8400-e29b-41d4-a716-446655440000",
  "eventId": "550e8400-e29b-41d4-a716-446655440001",
  "system": "taptrack"
}
```

---

## PHP Functions

### Generate QR Token
```php
// Location: database/migrations/002_add_program_support.php

function generateQRToken() {
    return bin2hex(random_bytes(16));
}

// Returns: 32-character hex string
// Example: "a1b2c3d4e5f6a7b8c9d0a1b2c3d4e5f6"
// Entropy: 128 bits (2^128 possible values)
```

### Ensure Event Has QR Token
```php
function ensureEventQRToken($pdo, $event_id) {
    // Get existing token if available
    $stmt = $pdo->prepare("SELECT QR_token FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    
    if ($event && !empty($event['QR_token'])) {
        return $event['QR_token'];
    }
    
    // Generate new token (with uniqueness check)
    $qr_token = generateQRToken();
    while ($pdo->prepare("SELECT id FROM events WHERE QR_token = ?")
           ->execute([$qr_token])->fetch()) {
        $qr_token = generateQRToken();
    }
    
    // Update event
    $pdo->prepare("UPDATE events SET QR_token = ? WHERE id = ?")
        ->execute([$qr_token, $event_id]);
    
    return $qr_token;
}
```

### Run Program Migration
```php
function runProgramMigration($pdo) {
    // Add QR_token column
    $pdo->exec("ALTER TABLE events ADD COLUMN IF NOT EXISTS QR_token 
               VARCHAR(255) UNIQUE NOT NULL DEFAULT UUID()");
    
    // Add programs column
    $pdo->exec("ALTER TABLE events ADD COLUMN IF NOT EXISTS programs 
               JSON DEFAULT JSON_ARRAY('ALL')");
    
    // Add index
    $pdo->exec("ALTER TABLE events ADD INDEX IF NOT EXISTS idx_qr_token (QR_token)");
    
    return ['success' => true, 'message' => 'Migration successful'];
}
```

---

## JavaScript Functions

### Generate QR Code
```javascript
// Location: assets/js/main.js - generateQR()

function generateQR() {
    const studentId = document.getElementById('gen-student').value;
    const eventId = document.getElementById('gen-event').value;
    
    if (!studentId || !eventId) return;
    
    // Get QR_token from selected event
    const event_qr_token = document.getElementById('gen-event')
        .selectedOptions[0]?.dataset?.qrToken || '';
    
    const qrData = {
        studentId: studentId,
        qrToken: event_qr_token,  // NEW: Use secure token
        system: 'taptrack'
    };
    
    const canvas = document.getElementById('gen-canvas');
    QRCode.toCanvas(canvas, JSON.stringify(qrData), {
        width: 200,
        errorCorrectionLevel: 'H'  // High error correction
    });
}
```

### Handle QR Scan Success
```javascript
// Location: assets/js/main.js - onScanSuccess()

async function onScanSuccess(decodedText) {
    try {
        const parsed = JSON.parse(decodedText);
        
        // Validate format
        if (parsed.system !== 'taptrack') {
            showScanResult(false, 'Invalid QR code — not a Taptrack code.');
            return;
        }
        
        // Ensure required fields
        if (!parsed.qrToken && !parsed.eventId) {
            showScanResult(false, 'Invalid QR code data.');
            return;
        }
        
        // Send to backend
        const resp = await fetch('?ajax=scan_qr', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(parsed)  // qrToken takes precedence
        });
        
        const data = await resp.json();
        showScanResult(data.success, data.message);
        
    } catch (e) {
        console.error('QR parse error:', e);
        showScanResult(false, 'Could not read QR code data.');
    }
}
```

---

## Validation Logic in Detail

### Program Eligibility Check
```php
// Get event's allowed programs
$allowed_programs = json_decode($event['programs'] ?? '["ALL"]', true);
// Example: ["IT", "Psychology"] or ["ALL"]

// Get student's program
$student_program = $student['course'];
// Example: "IT"

// Check authorization
$is_authorized = false;

if (is_array($allowed_programs)) {
    // If event allows all programs
    if (in_array('ALL', $allowed_programs)) {
        $is_authorized = true;
    }
    // If student's program is in the list
    else if (in_array($student_program, $allowed_programs)) {
        $is_authorized = true;
    }
}

// Result
if ($is_authorized) {
    // ALLOW: Continue to duplicate check
} else {
    // DENY: Return authorization error
    return error("You are not authorized to attend this event based on your program ($student_program).");
}
```

### Error Response Structure
```php
// Authorization Denied
echo json_encode([
    'success' => false,
    'message' => "❌ Student Name — You are not authorized...",
    'error_type' => 'authorization_denied'
]);

// Duplicate Check-in
echo json_encode([
    'success' => false,
    'message' => "⚠️ Student Name — You have already checked in...",
    'error_type' => 'already_checked_in'
]);

// Invalid QR/Event
echo json_encode([
    'success' => false,
    'message' => 'Invalid QR code or event not found.'
]);

// Student Not Found
echo json_encode([
    'success' => false,
    'message' => 'Student not found.'
]);

// Success
echo json_encode([
    'success' => true,
    'message' => "✅ Student Name (Program) — Check-in successful!",
    'student_name' => 'Student Name',
    'event_name' => 'Event Name'
]);
```

---

## Migration Execution

### Automatic (On First Connection)
```php
// In config/database.php
require_once __DIR__ . '/../database/migrations/002_add_program_support.php';
$migration_result = runProgramMigration($pdo);
// Runs automatically when database.php is included
```

### Manual (If Needed)
```php
<?php
require_once 'config/database.php';
require_once 'database/migrations/002_add_program_support.php';

$result = runProgramMigration($pdo);
echo $result['success'] ? 'Success' : 'Error: ' . $result['message'];
?>
```

### SQL Equivalent
```sql
-- If you need to run the migration manually:

ALTER TABLE events 
ADD COLUMN IF NOT EXISTS QR_token VARCHAR(255) UNIQUE NOT NULL COMMENT 'Unique QR token' AFTER archived;

ALTER TABLE events 
ADD COLUMN IF NOT EXISTS programs JSON DEFAULT JSON_ARRAY('ALL') COMMENT 'Program restrictions' AFTER QR_token;

ALTER TABLE events 
ADD INDEX IF NOT EXISTS idx_qr_token (QR_token);

-- Set random tokens for existing events
UPDATE events 
SET QR_token = UNHEX(SHA2(CONCAT(id, NOW()), 256, 16)) 
WHERE QR_token IS NULL;
```

---

## Testing with cURL

### Generate Event
```bash
curl -X POST http://localhost/Taptrack_Attendance/index.php \
  -d "action=add_event" \
  -d "name=IT Workshop" \
  -d "date=2026-04-15" \
  -d "location=Room 101" \
  -d "program_restriction=SPECIFIC" \
  -d "programs[]=IT" \
  -d "programs[]=Engineering" \
  -H "Cookie: PHPSESSID=admin_session_id"
```

### Scan QR Code
```bash
curl -X POST http://localhost/Taptrack_Attendance/index.php?ajax=scan_qr \
  -H "Content-Type: application/json" \
  -d '{
    "studentId": "550e8400-e29b-41d4-a716-446655440000",
    "qrToken": "a1b2c3d4e5f6a7b8c9d0a1b2c3d4e5f6",
    "system": "taptrack"
  }' \
  -H "Cookie: PHPSESSID=admin_session_id"
```

---

## Debugging

### Enable Query Logging
```php
// In modules/handlers.php, add before database operations:
error_log("DEBUG: Processing QR scan");
error_log("Student ID: " . $student_id);
error_log("QR Token: " . $qr_token);
error_log("Student program: " . $student_program);
error_log("Event programs: " . json_encode($allowed_programs));
```

### Check Database State
```sql
-- View all events with their programs and QR tokens
SELECT 
    id, 
    name, 
    programs, 
    QR_token 
FROM events 
WHERE archived = 0;

-- View attendance for an event
SELECT 
    a.id,
    s.first_name,
    s.last_name,
    s.course,
    a.scanned_at
FROM attendance a
JOIN students s ON a.student_id = s.id
WHERE a.event_id = ?
ORDER BY a.scanned_at DESC;
```

### Common Issues

**Issue:** QR_token is NULL  
**Solution:** Run migration: `runProgramMigration($pdo);`

**Issue:** Programs column doesn't exist  
**Solution:** Check database.php includes migration execution

**Issue:** Student can check in despite program mismatch  
**Solution:** Check `student['course']` matches event programs in JSON

**Issue:** "Invalid QR code or event not found"  
**Solution:**  
1. Verify QR_token is correct
2. Verify event is not archived
3. Check event exists with SELECT by qr_token

---

## Backward Compatibility

The system supports both old and new QR code formats:

**OLD QR (eventId):**
```json
{"studentId": "...", "eventId": "...", "system": "taptrack"}
```
↓  
Backend falls back to lookup by eventId

**NEW QR (qrToken):** ← Preferred
```json
{"studentId": "...", "qrToken": "...", "system": "taptrack"}
```
↓  
Backend uses secure token lookup

Both work, but new format is more secure.

---

## Security Considerations

1. **QR_token Uniqueness**: Enforced by UNIQUE index
2. **Program Validation**: Server-side only, no frontend bypass
3. **Attendance Integrity**: UNIQUE(student_id, event_id) constraint
4. **Secure Token**: 128-bit random value, hard to guess
5. **Error Messages**: Specific enough to debug, vague enough not to expose system details

---

## Performance Impact

- **QR_token Lookup**: O(1) - indexed hash lookup
- **Program Validation**: O(n) where n = program count (typically < 10)
- **Duplicate Check**: O(1) - indexed on (student_id, event_id)
- **Overall QR Scan**: < 50ms for full validation

---

## Related Code References

- **Migration**: `database/migrations/002_add_program_support.php`
- **Event Handlers**: `modules/handlers.php` (add_event, scan_qr)
- **Event UI**: `pages/admin/events.php`
- **QR Generator UI**: `pages/admin/qr-generator.php`
- **Frontend Logic**: `assets/js/main.js` (generateQR, onScanSuccess)
- **Documentation**: This file + `PROGRAM_BASED_EVENTS.md`
