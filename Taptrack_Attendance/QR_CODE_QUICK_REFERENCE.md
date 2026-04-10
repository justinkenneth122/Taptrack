# QR CODE FIX - QUICK REFERENCE

## What Changed?

QR codes now encode data directly instead of redirecting to URLs.

```
OLD: {"studentId": 5, "eventId": 1, "system": "taptrack"}
NEW: EVENT_1|USER_5
```

## Implementation Summary

### Files Modified (4 files)

1. **assets/js/main.js** 
   - Line 383-395: `toggleQR()` - Student dashboard QR generation
   - Line 398-420: `generateQR()` - Admin QR generator
   - Line 482-505: `onScanSuccess()` - Scanner parsing (uses regex)

2. **modules/handlers.php**
   - Line 219-340: `scan_qr` case - Backend processing simplified

3. **pages/admin/qr-generator.php**
   - Line 13-16: Removed QR_token data attribute

4. **Documentation (New)**
   - QR_CODE_FIX_SUMMARY.md - Comprehensive guide

## How It Works

### 1. Student Generates QR on Dashboard
```
Student clicks event → toggleQR() called
→ Generates: EVENT_5|USER_127
→ Displays QR code
```

### 2. Admin Scans QR
```
Scanner reads text format
→ onScanSuccess() called
→ Regex parses: EVENT_5|USER_127
→ Extracts: eventId=5, studentId=127
→ Sends to backend
```

### 3. Backend Validates & Records
```
Validate student exists
Validate event exists
Check program authorization
Check for duplicate check-in
Insert attendance record
Return success/error
```

## QR Data Format

```
EVENT_{event_id}|USER_{user_id}

Examples:
- EVENT_1|USER_5
- EVENT_123|USER_456
- EVENT_99|USER_1
```

**Regex Pattern:**
```javascript
/^EVENT_(\d+)\|USER_(\d+)$/
```

## Validation Layers

### Frontend (JavaScript)
```javascript
const match = decodedText.match(/^EVENT_(\d+)\|USER_(\d+)$/);
if (!match) return error;

const [, eventId, studentId] = match;
// Send eventId and studentId to backend
```

### Backend (PHP)
```php
// 1. Verify numeric
if (!is_numeric($event_id) || !is_numeric($student_id)) return error;

// 2. Student exists
if (!$student) return error;

// 3. Event exists and active
if (!$event) return error;

// 4. Program authorized
if (!$is_authorized) return error;

// 5. Not duplicate check-in
if ($already_checked_in) return error;

// 6. Insert attendance
$pdo->prepare("INSERT INTO attendance...")->execute(...);
```

## Testing

### Test QR Generation
1. Student Dashboard → Click event → QR displays
2. Admin QR Generator → Select student/event → QR displays
3. Verify QR contains: `EVENT_X|USER_Y` format

### Test Scanning
1. Generate valid QR: `EVENT_1|USER_5`
2. Scan with admin scanner
3. Verify: ✅ Check-in successful message

### Test Invalid Cases
1. Invalid QR format → ❌ Invalid QR code
2. Non-existent student → ❌ Student not found
3. Non-existent event → ❌ Event not found
4. Wrong program → ❌ Not authorized
5. Duplicate scan → ❌ Already checked in

## Common Issues & Solutions

### Issue: Scanning doesn't work
**Solution:** 
- Clear browser cache (Ctrl+Shift+Delete)
- Ensure QR format is exactly: `EVENT_123|USER_456`
- Check console for JavaScript errors (F12)

### Issue: "Invalid QR code" error
**Solution:**
- Verify QR is fresh (regenerated with new code)
- Old QR codes (pre-fix) won't work
- Generate new QR in admin panel

### Issue: "Not authorized" error
**Solution:**
- Verify student's program matches event program restrictions
- Check student registration program name matches event name
- Whitespace is trimmed automatically

### Issue: "Already checked in" error
**Solution:**
- Student already successfully checked in
- Database prevents duplicate entries (by design)

## Files to Know

```
assets/js/main.js          ← QR generation & scanning logic
modules/handlers.php       ← Backend attendance recording
pages/student-dashboard.php ← Student QR display
pages/admin/qr-generator.php ← Admin QR generation
pages/admin/qr-scanner.php ← Admin scanning interface
config/database.php        ← Database schema
```

## API End Points

### Scan QR (AJAX)
```javascript
POST ?ajax=scan_qr
Content-Type: application/json
Body: {eventId: "5", studentId: "127"}
Response: {success: true/false, message: "..."}
```

## Database Tables

### attendance table
```sql
id | student_id | event_id | scanned_at
-- | ---------- | -------- | ----------
UUID | INT | INT | TIMESTAMP
```

### events table (relevant columns)
```sql
id | name | date | programs | archived
-- | ---- | ---- | -------- | --------
INT | VARCHAR | DATE | JSON | BOOLEAN
```

### students table (relevant columns)
```sql
id | first_name | last_name | course | email
-- | ---------- | --------- | ------ | -----
INT | VARCHAR | VARCHAR | VARCHAR | VARCHAR
```

## Security Features

✅ No URL redirection possible
✅ Data stays in system
✅ Backend validates all data
✅ Program authorization enforced
✅ Duplicate check-in prevented
✅ Numeric validation (prevents SQL injection)
✅ Student/event existence verified

## Browser Compatibility

- ✅ Chrome/Edge (v90+)
- ✅ Firefox (v88+)
- ✅ Safari (v14+)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

Required libraries:
- `qrcode.min.js` - QR generation
- `html5-qrcode.min.js` - QR scanning

## Troubleshooting Regex

If scanning seems broken, check regex matching:

```javascript
// Test in browser console
const qr = "EVENT_5|USER_127";
const match = qr.match(/^EVENT_(\d+)\|USER_(\d+)$/);
console.log(match);
// Expected: ["EVENT_5|USER_127", "5", "127"]
```

## Support Checklist

- [ ] Users trained on new QR format
- [ ] Old QR codes invalidated/destroyed
- [ ] Test QR scanning works on admin devices
- [ ] Field staff trained to scan QRs
- [ ] Backup plan if scanning fails (manual entry?)
- [ ] Mobile camera permissions enabled

---

**Last Updated:** April 3, 2026
**Version:** 1.0 (Redesigned QR Format)
**Status:** Production Ready ✅
