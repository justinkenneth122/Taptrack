# QR Code System - Quick Troubleshooting Guide

## 🆘 Common Issues & Quick Fixes

### **Issue 1: QR Code Not Appearing on Student Dashboard**

#### Symptoms:
- Click event but no QR code shows
- No error message displayed
- Card stays closed

#### Quick Check:
```
1. Open DevTools: F12
2. Click Console tab
3. Click an event on dashboard
4. Look for log: "Generating QR code for: EVENT_X|USER_Y"
```

#### Fix:

| If log shows | Problem | Solution |
|---|---|---|
| Nothing in console | toggleQR not called | Check if onclick handler exists in HTML |
| `QRCode is not defined` | Library not loaded | Refresh page (Ctrl+R), check internet connection |
| `Cannot find element` | HTML structure wrong | Verify `<div id="qr-X">` exists in page |
| Error displayed on page | Canvas issue | Check browser console for specific error |

#### Nuclear Option (Last Resort):
```
1. F12 → Console tab
2. Run: document.location.reload(true)
3. Opens page fresh from server
4. Clear all cache
```

---

### **Issue 2: Admin QR Generator Doesn't Show QR**

#### Symptoms:
- Select student and event but no QR appears
- Output div stays hidden

#### Quick Check:
```javascript
// Paste into console:
document.getElementById('gen-student').value
document.getElementById('gen-event').value
// Both should show numbers, not empty
```

#### Fix:

| Check | If empty | If filled |
|---|---|---|
| Student dropdown | No students in DB | Call generateQR() manually |
| Event dropdown | No events in DB | Open DevTools and check console |

#### Manual Test:
```javascript
// Paste into console:
document.getElementById('gen-student').value = '1';
document.getElementById('gen-event').value = '1';
generateQR();
// Should show QR code
```

---

### **Issue 3: Camera/Scanner Not Working**

#### Symptoms:
- Camera doesn't open when clicking "Start Scanning"
- Error message: "Camera access denied"
- Camera opens but shows black screen

#### Quick Check:
```
1. Check browser already granted camera permission
2. Look for camera icon in address bar
3. If shows "X" → Permission denied
```

#### Fix:

**For Chrome/Chromium:**
```
1. Click camera icon (or info icon) in address bar
2. Select camera → Allow
3. Try scanning again
```

**For Firefox:**
```
1. Click lock icon in address bar
2. Find camera permission
3. Change to "Always Allow"
4. Refresh page
```

**For Safari:**
```
1. Check System Preferences → Security & Privacy → Camera
2. Ensure browser is in approved list
3. Refresh page
```

---

### **Issue 4: Scanned QR Code Not Recognized**

#### Symptoms:
- Camera scans something but shows error
- Error: "Invalid QR code"
- Error: "Could not read QR data"

#### Quick Check:
```
1. Open DevTools → Console
2. Scan a QR code
3. Look for: "Generating admin QR code for: EVENT_X|USER_Y"
4. Or error: "Invalid QR code — not a Taptrack code"
```

#### Fix:

**If showing "Invalid QR code":**
- The QR scanned is NOT in format: EVENT_X|USER_Y
- Solutions:
  1. Make sure scanning a Taptrack QR (from student dashboard or admin generator)
  2. Don't use other QR codes
  3. Don't try scanning QR from different system

**If showing "Could not read QR":**
- QR too blurry or too far away
- Solutions:
  1. Hold phone closer to QR
  2. Ensure good lighting
  3. Keep QR code centered in camera view
  4. Try different angle

---

### **Issue 5: Database Not Recording Attendance**

#### Symptoms:
- QR scans successfully ("✅ Marked attendance" shows)
- But student doesn't show as attended in admin panel

#### Quick Check:
```
1. Admin panel → Attendance
2. Look for student in list
3. Check if event date is correct
```

#### Fix:

| Issue | Solution |
|---|---|
| Attendance recorded with different date | Check system time/date on server |
| Attendance not saved at all | Check database connection (login test) |
| Not showing in filtered results | Check filter by date/event |

#### Test Database:
```
Admin → Attendance → Check if ANY attendance recorded
If yes: Filter issue
If no: Database issue → check server logs
```

---

## 🔧 Browser Console Commands

### **Test QRCode Library**
```javascript
// Should return: function
console.log(typeof QRCode);

// Should return: object
console.log(typeof Html5Qrcode);
```

### **Test QR Generation**
```javascript
// Create temporary canvas
const c = document.createElement('canvas');
c.width = 200;
c.height = 200;

// Generate test QR
QRCode.toCanvas(c, 'TEST_QR_DATA', {}, (err) => {
    console.log(err ? 'Error: ' + err : 'Success!');
});
```

### **Check DOM Elements**
```javascript
// Check if QR container exists
document.getElementById('qr-1'); // Should show <div> element

// Check if canvas exists
document.getElementById('qr-canvas-1'); // Should show <canvas> element

// Check if admin QR container exists
document.getElementById('gen-output'); // Should show <div> element
```

### **Test Data Format**
```javascript
// Test QR data parsing (scanner code)
const qrText = 'EVENT_5|USER_2';
const match = qrText.match(/^EVENT_(\d+)\|USER_(\d+)$/);
console.log(match); // Should show: ['EVENT_5|USER_2', '5', '2']
```

---

## 📊 System Status Check

### **Complete System Verification**
```javascript
console.log("=== TapTrack System Status Check ===");

// 1. Libraries
console.log("QRCode Library:", typeof QRCode === 'function' ? '✓' : '✗');
console.log("Html5Qrcode Library:", typeof Html5Qrcode === 'function' ? '✓' : '✗');

// 2. DOM Elements - Student
console.log("Student QR Container:", document.querySelector('[id^="qr-"]') ? '✓' : '✗');
console.log("Student Canvas:", document.querySelector('[id^="qr-canvas-"]') ? '✓' : '✗');

// 3. DOM Elements - Admin
console.log("Admin Output Div:", document.getElementById('gen-output') ? '✓' : '✗');
console.log("Admin Canvas:", document.getElementById('gen-canvas') ? '✓' : '✗');

// 4. Functions
console.log("toggleQR Function:", typeof toggleQR === 'function' ? '✓' : '✗');
console.log("generateQR Function:", typeof generateQR === 'function' ? '✓' : '✗');
console.log("startScanning Function:", typeof startScanning === 'function' ? '✓' : '✗');

console.log("=== Check Complete ===");
```

Expected output: All items should show ✓

---

## 🚀 One-Click Debug Mode

### **Enable Full Logging**
```javascript
// Paste into console to enable detailed logging
window.QR_DEBUG = true;

// Modify toggle function to log everything
const originalToggleQR = toggleQR;
toggleQR = function(eventId, studentId) {
    console.log("[DEBUG] toggleQR called:", {eventId, studentId});
    console.time('toggleQR');
    originalToggleQR.call(this, eventId, studentId);
    console.timeEnd('toggleQR');
};

// Now click event again - should see detailed logs
```

---

## 📱 Mobile Testing

### **Test on Mobile Device**

**For QR Display (Student):**
```
1. Open on mobile: http://[your-ip]:80/Taptrack_Attendance
2. Login as student
3. Click event
4. QR should appear in 1-2 seconds
```

**For QR Scanner (Using Another Device):**
```
1. Device A: Student dashboard with QR showing
2. Device B: Admin scanner
3. Point Device B camera at Device A QR
4. Should scan successfully
```

---

## 🔍 Log File Locations

### **PHP Error Log**
On XAMPP:
```
C:\xampp\apache\logs\error.log
C:\xampp\php\logs\error.log
```

Check for PHP errors if system misbehaves.

### **Browser Console**
```
F12 → Console tab
This is your main debugging tool
```

---

## 🆘 When All Else Fails

### **Step 1: Hard Reset**
```
1. Ctrl+Shift+Delete (clear all cache)
2. Ctrl+F5 (hard refresh bypassing cache)
3. Close browser completely
4. Reopen browser
5. Login again
```

### **Step 2: Server Restart**
```
1. Open XAMPP Control Panel
2. Stop Apache
3. Stop MySQL
4. Wait 3 seconds
5. Start MySQL
6. Start Apache
7. Wait for green indicators
8. Refresh page
```

### **Step 3: Database Check**
```
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Login (default: root, no password)
3. Select database: taptrack
4. Check tables exist:
   - students ✓
   - events ✓
   - attendance ✓
```

### **Step 4: Contact Dev**
```
If still broken, provide:
1. Screenshot of error
2. Browser console output (F12)
3. What you were trying to do
4. Browser type and version
```

---

## ⚡ Performance Tips

### **Make QR Load Faster**
```javascript
// Pre-generate QRs on page load
document.addEventListener('DOMContentLoaded', () => {
    console.log('Page loaded, QR system ready');
});
```

### **Check Network Performance**
```
DevTools → Network tab
Watch for failed library loads
Should see qrcode.min.js and html5-qrcode.min.js
```

---

## Summary Table: Issue → Solution

| Issue | First Check | Quick Fix | If Still Broken |
|---|---|---|---|
| QR not showing | DevTools console logs | Refresh page F5 | Restart server |
| Camera not opening | Browser permissions | Check camera setting | Restart browser |
| QR not scanning | QR format correct | Improve lighting | Test with different QR |
| Attendance not saved | Check admin panel | Verify student exists | Check database |
| Layout looks broken | Clear browser cache | Hard refresh Ctrl+F5 | Report with screenshot |

---

**Most issues resolve with: F5 refresh + cache clear + server restart**

If you've done all three and still have problems, check the QR_TECHNICAL_REPORT.md for deeper analysis.
