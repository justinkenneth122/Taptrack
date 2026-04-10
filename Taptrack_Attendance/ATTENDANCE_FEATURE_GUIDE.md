# Attendance Section Feature Documentation

## Overview

The **Attendance Section** is a comprehensive admin module for viewing, filtering, and analyzing attendance records. It provides multiple filtering options to help admins quickly find attendance data by event, student, program, and year level.

## Features

### 1. **Two Implementation Versions**

- **Standard Version** (`attendance.php`) - Server-side filtering with full page refresh
- **Advanced Version** (`attendance-advanced.php`) - AJAX-based real-time filtering without page reload

### 2. **Core Filtering Capabilities**

#### Event Filter (Required)
- Dropdown list of all active events
- Shows event name and date
- Required to view any attendance records
- **Use case**: Select a specific event to view who attended

#### Search Student Bar
- Real-time search input field
- Searches across:
  - Student Name (first and last name)
  - Student Email
  - Student ID/Number
- **Use case**: Quickly find a specific student or group of students

#### Program Filter
- Dropdown with all available programs/courses
- Options include "All Programs" (default)
- Programs available:
  - BS Information Technology
  - BS Psychology
  - BS Business Administration
  - And others based on your database
- **Use case**: View attendance for a specific program only

#### Year Level Filter
- Dropdown with all year levels
- Options include "All Years" (default)
- Standard options:
  - 1st Year
  - 2nd Year
  - 3rd Year
  - 4th Year
  - (Custom year levels from database)
- **Use case**: Analyze attendance by academic year

### 3. **Combined Filtering Logic**

All filters work together dynamically. Example scenarios:

```
Scenario 1: Single Filter
├─ Select Event: "Tech Summit 2026"
└─ Result: Show all 150 attendees from that event

Scenario 2: Multiple Filters
├─ Select Event: "Tech Summit 2026"
├─ Select Program: "BS Information Technology"
├─ Select Year Level: "2nd Year"
└─ Result: Show only 2nd Year IT students who attended

Scenario 3: Search Within Filters
├─ Select Event: "Tech Summit 2026"
├─ Program: "BS Information Technology"
├─ Search: "Juan"
└─ Result: IT students with "Juan" in name who attended
```

## Attendance Table Display

Each attendance record shows:

| Column | Description |
|--------|-------------|
| **#** | Row number |
| **Student Name** | First and Last name |
| **Student #** | Unique student identifier |
| **Email** | Student email address |
| **Program** | Course/Program name |
| **Year Level** | Academic year (1st, 2nd, etc.) |
| **Time Scanned** | Date and time of attendance (format: "Mon j, Y · g:i A") |
| **Status** | Verification status (QR Scanned or ✓ Verified via face recognition) |

## Statistics Dashboard

The dashboard displays real-time statistics:

- **Total Attendees**: Count of all matching records
- **Verified**: Count of attendance verified via face recognition
- **Filtered Results**: Live count updating as you search

## How to Use

### Standard Version (attendance.php)

1. Navigate to: **Admin Panel → Attendance**
2. Select an event from the "Event" dropdown (required)
3. (Optional) Select a Program filter
4. (Optional) Select a Year Level filter
5. (Optional) Enter search term in the search bar
6. Click **"Apply Filters"** or use the dropdown to apply
7. Results update on page load

### Advanced Version (attendance-advanced.php)

1. Navigate to: **Attendance Records - Advanced**
2. Select an event from the dropdown
3. Filters apply automatically as you:
   - Change dropdowns
   - Type in the search field (with debouncing)
4. Real-time results update without page reload
5. Click **"Export CSV"** to download attendance data
6. Click **"Reset Filters"** to clear all selections

## Use Cases

### Use Case 1: Daily Attendance Review
**Admin wants to check who attended today's event**

1. Select Today's Event
2. View the attendance list
3. Note: Total attendees displayed in stats
4. Export to CSV if needed

### Use Case 2: Program-Based Analysis
**Admin needs to analyze attendance by program**

1. Select Event
2. Filter by each Program one at a time
3. Compare attendance rates across programs
4. Example: "IT had 45 attendees, Psychology had 32"

### Use Case 3: Year Level Comparison
**Admin wants to see attendance trends by year level**

1. Select Event
2. Filter by "1st Year" → note count
3. Filter by "2nd Year" → note count
4. Filter by "3rd Year" → note count
5. Compare which years had best attendance

### Use Case 4: Find Specific Student
**Admin needs to verify if a student attended**

1. Select Event
2. Type student's name/email/ID in search
3. Immediately see if student appears in records
4. View exact check-in time

### Use Case 5: Export Attendance Report
**Admin needs a CSV file of attendance for records**

1. Apply desired filters (Event, Program, Year Level)
2. Click "Export CSV"
3. File downloads with complete attendance data
4. Open in Excel, Google Sheets, etc.

## Database Requirements

Your database must have these tables with the following structure:

### Students Table
```sql
CREATE TABLE students (
    id VARCHAR(36) PRIMARY KEY,
    email VARCHAR(255),
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    student_number VARCHAR(50),
    course VARCHAR(255),           -- Program/Course name
    year_level VARCHAR(50),        -- Year level (1st, 2nd, etc.)
    ...other fields...
);
```

### Events Table
```sql
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    date DATE,
    location VARCHAR(255),
    description TEXT,
    programs JSON,                 -- JSON array of program names
    QR_token VARCHAR(255),
    archived INT,
    ...other fields...
);
```

### Attendance Table
```sql
CREATE TABLE attendance (
    id VARCHAR(36) PRIMARY KEY,
    student_id VARCHAR(36),        -- Foreign key to students
    event_id INT,                  -- Foreign key to events
    scanned_at TIMESTAMP,          -- Check-in time
    face_verified TINYINT(1),      -- Boolean: verified via face
    ...other fields...
);
```

## Filter Combinations

### Valid Filter Combinations

| Filters | Result |
|---------|--------|
| Event only | All attendees from that event |
| Event + Program | All students from that program who attended |
| Event + Year Level | All students from that year who attended |
| Event + Program + Year Level | Specific cohort attendance |
| Event + Search | Search results within that event |
| Event + Program + Year Level + Search | Highly specific filtered results |

### Filter Behavior

- **"All Programs"** → No program restriction
- **"All Years"** → No year level restriction
- **Empty Search** → No search restriction
- **No Event** → No results (event is required)

## API Integration

### REST Endpoint: `/pages/admin/api_attendance.php`

**Parameters:**
```
GET ?page=api_attendance&event=1&program=BS%20IT&year_level=2nd%20Year&search=juan
```

**Response:**
```json
{
  "success": true,
  "event": {
    "id": 1,
    "name": "Tech Summit 2026",
    "date": "2026-04-15"
  },
  "stats": {
    "total": 45,
    "verified": 38,
    "byProgram": {
      "BS Information Technology": 25,
      "BS Computer Science": 20
    },
    "byYearLevel": {
      "1st Year": 10,
      "2nd Year": 20,
      "3rd Year": 15
    }
  },
  "records": [
    {
      "id": "uuid",
      "student_id": "uuid",
      "event_id": 1,
      "scanned_at": "2026-04-15 09:30:00",
      "face_verified": 1,
      "first_name": "Juan",
      "last_name": "Dela Cruz",
      "student_number": "R202012345",
      "email": "R202012345@feuroosevelt.edu.ph",
      "course": "BS Information Technology",
      "year_level": "2nd Year"
    },
    ...more records...
  ],
  "filters": {
    "event": "1",
    "search": "juan",
    "program": "BS Information Technology",
    "yearLevel": "2nd Year"
  }
}
```

## File Structure

```
pages/admin/
├── attendance.php              # Standard server-side filtering
├── attendance-advanced.php     # AJAX real-time filtering
└── api_attendance.php          # REST API for filtering

index.php                        # Main router (updated)
```

## Performance Considerations

### Standard Version
- ✓ Suitable for events with < 10,000 attendees
- ✓ Fast page loads even with large datasets
- ✗ Page reload required for each filter change
- ✓ Works without JavaScript

### Advanced Version
- ✓ Real-time filtering (with debouncing)
- ✓ No page reload
- ✓ Better UX for frequent filtering
- ✓ Suitable for high-frequency filtering
- ✓ Requires JavaScript enabled

## Security Notes

- ✓ Only authenticated admins can access
- ✓ Query parameters validated
- ✓ SQL injection prevention via prepared statements
- ✓ XSS prevention via HTML escaping

## Troubleshooting

### Issue: "Select an event to view attendance records"
**Solution**: Make sure you've selected an event from the Event dropdown (it's required)

### Issue: No results showing
**Possible causes**:
1. Event has no attendance records yet
2. Filters are too restrictive (check if combinations make sense)
3. Search term doesn't match any students

**Solution**: Try resetting filters and selecting just the event

### Issue: Search not working
**Solution in Advanced version**: 
- Wait for debounce delay (500ms)
- Check that search term matches student name, email, or ID
- Clear search and try again

### Issue: Export CSV not downloading
**Solution**:
1. Make sure you're using the Advanced version
2. Ensure you have at least one record displayed
3. Check browser's download settings

## Enhancement Opportunities

Possible future enhancements:

1. **Date Range Filtering**: Filter attendance by date range
2. **Export Formats**: Add Excel, PDF export options
3. **Charts & Graphs**: Visual attendance analytics
4. **Attendance Rates**: Calculate percentage attended by program/year
5. **Notifications**: Email reports to admins
6. **Attendance History**: View historical trends
7. **Bulk Operations**: Mark as verified, delete, etc.
8. **Custom Reports**: Schedule recurring reports

## Contact & Support

For issues, questions, or enhancement requests, contact the development team.

---

**Last Updated**: April 9, 2026  
**Version**: 1.0  
**Status**: Production Ready
