# Attendance Section - Testing & Verification Guide

**Last Updated**: April 9, 2026  
**Status**: Use this guide to verify the feature is working correctly

---

## 🎯 Pre-Testing Checklist

Before testing, ensure:

- [ ] All files are uploaded to server
- [ ] Database is running
- [ ] Admin user is created
- [ ] Test events exist in database
- [ ] Test attendance records exist
- [ ] PHP error logging is enabled
- [ ] JavaScript console is checked
- [ ] Cookies/sessions are cleared

### Quick DB Verification

```sql
-- Check if events exist
SELECT COUNT(*) as event_count FROM events WHERE archived = 0;

-- Check if attendance records exist
SELECT COUNT(*) as attendance_count FROM attendance;

-- Count students by program
SELECT course, COUNT(*) FROM students GROUP BY course;

-- Count students by year level
SELECT year_level, COUNT(*) FROM students GROUP BY year_level;
```

Expected: Should see > 0 for all queries

---

## 🧪 Test Cases

### TEST 1: Access Control

#### Test 1.1: Non-admin Cannot Access
**Objective**: Verify only admins can access attendance

**Steps**:
1. Log out if logged in
2. Try to access: `?page=admin_attendance`
3. OR: Try to access: `?page=use_attendance_advanced`

**Expected Result**:
- [ ] Redirected to login page
- [ ] OR shown "403 Access Denied" error

#### Test 1.2: Student Cannot Access
**Objective**: Verify students are blocked

**Steps**:
1. Log in as student
2. Try to access: `?page=admin_attendance`
3. Try to access API: `?page=api_attendance&event=1`

**Expected Result**:
- [ ] Both attempts denied
- [ ] Redirected or shown error

---

### TEST 2: Event Filtering

#### Test 2.1: Event Dropdown Loads
**Objective**: Verify all events load in dropdown

**Steps**:
1. Log in as admin
2. Navigate to: Admin Panel → Attendance
3. Click on "Event" dropdown

**Expected Result**:
- [ ] Dropdown opens
- [ ] Shows all active events (non-archived)
- [ ] Shows event name and date
- [ ] Default shows "-- Select an Event --"

#### Test 2.2: Select Event Shows Attendance
**Objective**: Verify selecting event displays records

**Steps**:
1. From Test 2.1, select an event
2. Observe page/API response

**Expected Result**:
- [ ] Page shows attendance records (standard version)
- [ ] OR AJAX loads records (advanced version)
- [ ] Table populates with data
- [ ] Statistics show attendee count
- [ ] No errors in console

---

### TEST 3: Program Filter

#### Test 3.1: Program Dropdown Loads
**Objective**: Verify all programs show in dropdown

**Steps**:
1. Select an event
2. Look at "Program" dropdown
3. Click to open

**Expected Result**:
- [ ] Shows "-- All Programs --" as default
- [ ] Lists all programs (IT, Psychology, etc.)
- [ ] Programs match those in database
- [ ] No duplicate programs

#### Test 3.2: Program Filter Works
**Objective**: Verify filtering by program

**Steps**:
1. Select event
2. Select a specific program (e.g., "BS IT")
3. Observe results

**Expected Result**:
- [ ] Table updates (standard: page reloads, advanced: live)
- [ ] Only records from selected program shown
- [ ] Record count decreases
- [ ] All displayed students have selected program

#### Test 3.3: All Programs Option
**Objective**: Verify "All Programs" removes filter

**Steps**:
1. Select "BS IT" program
2. Note the record count
3. Change to "-- All Programs --"
4. Observe results

**Expected Result**:
- [ ] Record count increases (if it was filtered)
- [ ] Shows all programs again
- [ ] Equal to "no program filter" state

---

### TEST 4: Year Level Filter

#### Test 4.1: Year Level Dropdown Loads
**Objective**: Verify all year levels show

**Steps**:
1. Select an event
2. Look at "Year Level" dropdown
3. Click to open

**Expected Result**:
- [ ] Shows "-- All Years --" as default
- [ ] Lists all year levels (1st, 2nd, 3rd, 4th)
- [ ] No duplicates
- [ ] Matches database values

#### Test 4.2: Year Level Filter Works
**Objective**: Verify filtering by year

**Steps**:
1. Select event
2. Select "2nd Year"
3. Note record count

**Expected Result**:
- [ ] Table updates
- [ ] Only 2nd year students shown
- [ ] Record count changes
- [ ] All displayed students have year_level = "2nd Year"

#### Test 4.3: Test Each Year Level
**Objective**: Verify each year level filters correctly

**Steps**:
1. Select each year level one at a time
2. Compare record counts
3. Spot-check a few records

**Expected Result**:
- [ ] Each year shows different count
- [ ] Year level value matches filter selection
- [ ] No cross-year mixing

---

### TEST 5: Search Functionality

#### Test 5.1: Search by Student Name
**Objective**: Verify searching by name works

**Steps**:
1. Select an event
2. In search box, type: "juan"
3. Observe results

**Expected Result**:
- [ ] Table filters to show "Juan" (or similar names)
- [ ] Only matching names displayed
- [ ] Result count decreases

#### Test 5.2: Search by Email
**Objective**: Verify searching by email

**Steps**:
1. Select event
2. Type partial email: "R2020"
3. Observe results

**Expected Result**:
- [ ] Shows students matching that email prefix
- [ ] Case-insensitive matching works
- [ ] Only matching records shown

#### Test 5.3: Search by Student Number
**Objective**: Verify searching by ID

**Steps**:
1. Select event
2. Type student number: "R202012345" (or similar)
3. Observe results

**Expected Result**:
- [ ] Finds that specific student
- [ ] Or finds students with similar numbers
- [ ] Displays matching record(s)

#### Test 5.4: Search with No Results
**Objective**: Verify "no results" message

**Steps**:
1. Select event
2. Type random text: "xyzabc123xyz"
3. Observe results

**Expected Result**:
- [ ] Table becomes empty
- [ ] "No records match your search criteria" message shows
- [ ] Statistics update (0 records)

#### Test 5.5: Clear Search
**Objective**: Verify clearing search restores results

**Steps**:
1. From Test 5.4, delete search text
2. Leave search box empty

**Expected Result**:
- [ ] All records reappear
- [ ] Count returns to original

---

### TEST 6: Combined Filtering

#### Test 6.1: Program + Year Level
**Objective**: Verify multiple filters work together

**Steps**:
1. Select event: "Tech Summit"
2. Select program: "BS IT"
3. Select year: "2nd Year"
4. Note results

**Expected Result**:
- [ ] Only 2nd Year IT students shown
- [ ] Record count lower than single filter
- [ ] All records match all criteria

**Verification**:
- [ ] Student program = "BS IT"
- [ ] Student year_level = "2nd Year"

#### Test 6.2: Program + Year + Search
**Objective**: Verify all three filters together

**Steps**:
1. Select event
2. Select program: "BS Psychology"
3. Select year: "3rd Year"
4. Search: "maria"

**Expected Result**:
- [ ] Only Psychology 3rd-year students with "maria" in name
- [ ] Very specific subset displayed
- [ ] Count is 1 or few records

#### Test 6.3: Reset Filters
**Objective**: Verify reset clears all filters

**Steps**:
1. Apply multiple filters (program, year, search)
2. Click "Reset Filters" button

**Expected Result**:
- [ ] All dropdowns reset to default ("All Programs", "All Years")
- [ ] Search box clears
- [ ] Event selection clears
- [ ] Page returns to initial state

---

### TEST 7: Table Display

#### Test 7.1: All Columns Present
**Objective**: Verify all required columns show

**Steps**:
1. Select an event with attendance records
2. Look at table headers

**Expected Result**:
- [ ] # (row number)
- [ ] Student Name
- [ ] Student #
- [ ] Email
- [ ] Program
- [ ] Year Level
- [ ] Time Scanned
- [ ] Status

#### Test 7.2: Data Display Accuracy
**Objective**: Verify data displays correctly

**Steps**:
1. Select a record you know about
2. Verify each field matches database
3. Check a few records

**Expected Result**:
- [ ] Names match exactly
- [ ] Student numbers correct
- [ ] Email addresses correct
- [ ] Program names correct
- [ ] Year levels match

#### Test 7.3: Time Format
**Objective**: Verify timestamp format is correct

**Steps**:
1. Look at "Time Scanned" column
2. Check format

**Expected Result**:
- [ ] Format: "Mon j, Y · g:i A"
- [ ] Example: "Apr 15, 2026 · 9:30 AM"
- [ ] All dates follow same format

#### Test 7.4: Status Badge Colors
**Objective**: Verify status badges display correctly

**Steps**:
1. Look for records with different verification status
2. Observe badge colors and text

**Expected Result**:
- [ ] ✓ Verified - green badge
- [ ] QR Scanned - blue badge
- [ ] Badge colors are distinct
- [ ] Text is readable

---

### TEST 8: Statistics Dashboard

#### Test 8.1: Total Attendees Count
**Objective**: Verify total count matches records

**Steps**:
1. Select event
2. Look at "Total Attendees" stat
3. Count table rows manually

**Expected Result**:
- [ ] Count matches number of records in table
- [ ] Updates when filters change

#### Test 8.2: Verified Count
**Objective**: Verify "Verified" count is accurate

**Steps**:
1. Look at "Verified" stat
2. Manually count records with face_verified=1

**Expected Result**:
- [ ] Count matches verified records
- [ ] Count <= Total Attendees
- [ ] Updates with filters

#### Test 8.3: Filtered Results Count (Advanced)
**Objective**: Verify "Filtered Results" updates

**Steps**:
1. Use advanced version
2. Perform searches
3. Observe "Filtered Results" count

**Expected Result**:
- [ ] Count updates as you search
- [ ] Matches number of visible rows
- [ ] Shows "0" when no matches

---

### TEST 9: Advanced Version (AJAX)

#### Test 9.1: Real-Time Search
**Objective**: Verify search works without page reload

**Steps**:
1. Open advanced version: `?page=attendance_advanced`
2. Select an event
3. Type in search box
4. Watch network tab in dev tools

**Expected Result**:
- [ ] Results update WITHOUT page reload
- [ ] Table updates in place
- [ ] No full page refresh
- [ ] Network tab shows API call (~500ms delay due to debounce)

#### Test 9.2: Debouncing Works
**Objective**: Verify search doesn't hammer server

**Steps**:
1. Type quickly: "j-u-a-n" (one letter at a time)
2. Watch network tab

**Expected Result**:
- [ ] Only ONE API call after you stop typing
- [ ] NOT multiple calls for each letter
- [ ] Waits ~500ms before sending

#### Test 9.3: Loading Indicator
**Objective**: Verify loading state shows during API call

**Steps**:
1. Use slow network (Chrome DevTools: Throttle)
2. Select event
3. Watch for loading indicator

**Expected Result**:
- [ ] "⏳ Filtering records..." message appears
- [ ] Disappears after results load
- [ ] Blue background (#f0f7ff)

#### Test 9.4: CSV Export
**Objective**: Verify CSV download works

**Steps**:
1. Apply filters
2. Click "📥 Export CSV" button
3. Check downloaded file

**Expected Result**:
- [ ] File downloads (name: attendance_[timestamp].csv)
- [ ] File opens in Excel/Sheets
- [ ] Contains correct headers:
  - Name
  - Student #
  - Email
  - Program
  - Year Level
  - Time Scanned
  - Status
- [ ] Data matches displayed records
- [ ] All records included

---

### TEST 10: API Direct Testing

#### Test 10.1: Basic API Call
**Objective**: Verify API returns data

**Steps**:
1. Open URL in browser: `?page=api_attendance&event=1`
2. View page source (Ctrl+U)

**Expected Result**:
- [ ] JSON response displays
- [ ] `"success": true` appears
- [ ] Contains event info, stats, records array

#### Test 10.2: API with Filters
**Objective**: Verify API filters work

**Steps**:
1. Call with filters:
   `?page=api_attendance&event=1&program=BS%20IT&year_level=2nd%20Year`
2. Check JSON response

**Expected Result**:
- [ ] Successfully returns filtered data
- [ ] byProgram contains only BS IT counts
- [ ] Only 2nd Year students in records array

#### Test 10.3: API Error Handling
**Objective**: Verify API handles errors

**Steps**:
1. Call with invalid event: `?page=api_attendance&event=99999`
2. Call with no event: `?page=api_attendance`
3. Observe responses

**Expected Result**:
- [ ] Invalid event: Returns empty records array (or error)
- [ ] No event: Returns 400 error "Event ID is required"
- [ ] Proper HTTP status codes (200, 400, 500)

---

### TEST 11: Responsive Design

#### Test 11.1: Desktop View (>1024px)
**Objective**: Verify desktop layout

**Steps**:
1. Open on desktop (1920x1080)
2. Check layout

**Expected Result**:
- [ ] All columns visible (no horizontal scroll needed)
- [ ] Filters in 2-column grid
- [ ] Stats in 3-column layout
- [ ] Readable and well-spaced

#### Test 11.2: Tablet View (640-1024px)
**Objective**: Verify tablet layout

**Steps**:
1. Open on iPad/tablet resolution
2. Or use Chrome DevTools device emulation
3. Check if responsive

**Expected Result**:
- [ ] Layout adjusts to tablet size
- [ ] Still readable
- [ ] Table may scroll horizontally (acceptable)
- [ ] Filters stack appropriately

#### Test 11.3: Mobile View (<640px)
**Objective**: Verify mobile layout

**Steps**:
1. Open on phone/mobile size (375x667)
2. Check layout

**Expected Result**:
- [ ] Layout adapts to mobile
- [ ] Filters are readable
- [ ] Table scrolls horizontally (acceptable on mobile)
- [ ] Touch targets are large enough (>44px)

---

### TEST 12: Browser Compatibility

#### Test 12.1: Chrome
**Steps**:
1. Open in latest Chrome
2. Run Tests 1-11

**Expected Result**:
- [ ] All tests pass
- [ ] No console errors
- [ ] Smooth animations

#### Test 12.2: Firefox
**Steps**:
1. Open in latest Firefox
2. Run Tests 1-11

**Expected Result**:
- [ ] All tests pass
- [ ] No console errors
- [ ] Works the same as Chrome

#### Test 12.3: Safari
**Steps**:
1. Open in Safari (if available)
2. Run Tests 1-11

**Expected Result**:
- [ ] All tests pass
- [ ] No console errors
- [ ] Dates format correctly

#### Test 12.4: Edge
**Steps**:
1. Open in Microsoft Edge
2. Run Tests 1-11

**Expected Result**:
- [ ] All tests pass
- [ ] No console errors

---

### TEST 13: Accessibility

#### Test 13.1: Keyboard Navigation
**Objective**: Verify keyboard users can use feature

**Steps**:
1. Press Tab repeatedly
2. Navigate through filters
3. Use arrow keys in dropdowns
4. Press Enter to select

**Expected Result**:
- [ ] Can navigate with keyboard only
- [ ] Focus visible (blue outline)
- [ ] Can select options
- [ ] Can click buttons

#### Test 13.2: Screen Reader (Optional)
**Objective**: Test with accessibility tools

**Steps**:
1. Use browser accessibility checker
2. Or use NVDA/JAWS if available
3. Listen to labels and descriptions

**Expected Result**:
- [ ] Labels are announced
- [ ] Table structure is understood
- [ ] Buttons are identified
- [ ] No "unlabeled" elements

---

### TEST 14: Performance

#### Test 14.1: Page Load Speed
**Objective**: Verify page loads quickly

**Steps**:
1. Open Dev Tools → Network tab
2. Load attendance page
3. Check total time

**Expected Result**:
- [ ] Total load time < 2 seconds
- [ ] DOM content loaded < 1 second
- [ ] All resources load

#### Test 14.2: Search Speed
**Objective**: Verify search is responsive

**Steps**:
1. Select event with 1000+ records
2. Perform searches
3. Note response time

**Expected Result**:
- [ ] Search responds within 500ms
- [ ] No lag or freezing
- [ ] UI responsive

#### Test 14.3: Table Rendering
**Objective**: Verify large tables render

**Steps**:
1. Filter to show 500+ records
2. Observe table rendering

**Expected Result**:
- [ ] Table renders without lag
- [ ] Scrolling is smooth
- [ ] No JavaScript errors
- [ ] Browser doesn't freeze

---

## 🎯 Test Execution Checklist

### Phase 1: Basic Functionality
- [ ] Access control verified
- [ ] Event filter works
- [ ] Program filter works
- [ ] Year level filter works
- [ ] Search works (name, email, ID)
- [ ] Combined filters work
- [ ] Reset filters works

### Phase 2: Display & UI
- [ ] All table columns display
- [ ] Data accuracy verified
- [ ] Status badges correct
- [ ] Time format correct
- [ ] Statistics display correctly
- [ ] Responsive design works

### Phase 3: Advanced Features
- [ ] AJAX real-time search (if advanced version)
- [ ] CSV export works (if advanced version)
- [ ] Loading indicator shows (if advanced version)
- [ ] Debouncing works (if advanced version)

### Phase 4: API Testing
- [ ] Basic API returns data
- [ ] API with filters works
- [ ] API error handling works
- [ ] JSON format correct

### Phase 5: Cross-Browser & Accessibility
- [ ] Works in Chrome
- [ ] Works in Firefox
- [ ] Works in Safari/Edge
- [ ] Keyboard navigation works
- [ ] Mobile responsive

### Phase 6: Performance & Security
- [ ] Page loads < 2 seconds
- [ ] Search responds < 500ms
- [ ] Large datasets handled
- [ ] SQL injection prevented
- [ ] XSS prevented
- [ ] Admin-only access enforced

---

## 📊 Test Results Template

```
DATE: _______________
TESTER: ______________
BROWSER: ______________
OS: ______________

PHASE 1: BASIC FUNCTIONALITY
[ ] Test 1.1 - Passed / Failed / Not Tested
[ ] Test 1.2 - Passed / Failed / Not Tested
[ ] Test 2.1 - Passed / Failed / Not Tested
[ ] Test 2.2 - Passed / Failed / Not Tested
[ ] Test 3.1 - Passed / Failed / Not Tested
[ ] Test 3.2 - Passed / Failed / Not Tested
[ ] Test 3.3 - Passed / Failed / Not Tested
[ ] Test 4.1 - Passed / Failed / Not Tested
[ ] Test 4.2 - Passed / Failed / Not Tested
[ ] Test 4.3 - Passed / Failed / Not Tested
[ ] Test 5.1 - Passed / Failed / Not Tested
[ ] Test 5.2 - Passed / Failed / Not Tested
[ ] Test 5.3 - Passed / Failed / Not Tested
[ ] Test 5.4 - Passed / Failed / Not Tested
[ ] Test 5.5 - Passed / Failed / Not Tested

ISSUES FOUND:
1. ________________________
2. ________________________
3. ________________________

NOTES:
_________________________
_________________________

SIGN-OFF: _____________ DATE: _______
```

---

## 🐛 Debugging Tips

### If Tests Fail

**1. Check Console for Errors**
```
Browser DevTools → Console tab
Look for red error messages
```

**2. Check Network Tab**
```
DevTools → Network tab
Look at API requests
Check response codes (200, 404, 500)
```

**3. Check Database**
```sql
SELECT COUNT(*) FROM attendance;
SELECT * FROM students LIMIT 1;
SELECT * FROM events LIMIT 1;
```

**4. Check Server Logs**
```
Apache: /var/log/apache2/error.log
PHP: /var/log/php-errors.log
MySQL: /var/log/mysql/error.log
```

**5. Test Database Connection**
```php
<?php
require_once __DIR__ . '/config/database.php';
echo "Connected to: " . $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
?>
```

---

## ✅ Sign-Off

When ALL tests pass:

- [ ] Feature ready for production
- [ ] Admin training completed
- [ ] Documentation reviewed
- [ ] Backup verified
- [ ] Monitoring set up

**Approved By**: ________________  
**Date**: ________________________  
**Version**: 1.0

---

**Last Updated**: April 9, 2026  
**Status**: Ready to Test
