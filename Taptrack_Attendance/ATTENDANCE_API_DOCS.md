# Attendance API Documentation

## Base URL

```
/pages/admin/api_attendance.php
```

## Authentication

**Required**: Admin user must be logged in
- If not authenticated, user is redirected to login page
- Session-based authentication

## Endpoint

### GET /api_attendance

Retrieves filtered attendance records with statistics.

## Request Parameters

```
GET /pages/admin/api_attendance.php?event=1&program=BS%20IT&year_level=2nd%20Year&search=juan
```

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `event` | integer | YES | Event ID to filter by |
| `program` | string | NO | Program/Course name. Use "ALL" to include all programs |
| `year_level` | string | NO | Year level. Use "ALL" to include all years |
| `search` | string | NO | Search term (searches name, email, student number) |

## Request Examples

### Example 1: Basic Event Query
```
GET ?page=api_attendance&event=1
```

### Example 2: With Program Filter
```
GET ?page=api_attendance&event=1&program=BS%20Information%20Technology
```

### Example 3: Multiple Filters
```
GET ?page=api_attendance&event=1&program=BS%20IT&year_level=2nd%20Year&search=juan
```

### Example 4: URL Encoded
```
GET ?page=api_attendance&event=1&program=BS+Information+Technology&year_level=2nd+Year&search=Juan%20Dela
```

## Response

### Success Response (200 OK)

```json
{
  "success": true,
  "event": {
    "id": 1,
    "name": "Tech Innovation Summit",
    "date": "2026-04-15"
  },
  "stats": {
    "total": 45,
    "verified": 38,
    "byProgram": {
      "BS Information Technology": 25,
      "BS Psychology": 12,
      "BS Business Administration": 8
    },
    "byYearLevel": {
      "1st Year": 10,
      "2nd Year": 20,
      "3rd Year": 12,
      "4th Year": 3
    }
  },
  "records": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "student_id": "123e4567-e89b-12d3-a456-426614174000",
      "event_id": 1,
      "scanned_at": "2026-04-15T09:30:00+00:00",
      "face_verified": 1,
      "first_name": "Juan",
      "last_name": "Dela Cruz",
      "student_number": "R202012345",
      "email": "R202012345@feuroosevelt.edu.ph",
      "course": "BS Information Technology",
      "year_level": "2nd Year"
    },
    {
      "id": "550e8400-e29b-41d4-a716-446655440001",
      "student_id": "123e4567-e89b-12d3-a456-426614174001",
      "event_id": 1,
      "scanned_at": "2026-04-15T09:32:00+00:00",
      "face_verified": 0,
      "first_name": "Maria",
      "last_name": "Santos",
      "student_number": "R202012346",
      "email": "R202012346@feuroosevelt.edu.ph",
      "course": "BS Psychology",
      "year_level": "3rd Year"
    }
  ],
  "filters": {
    "event": "1",
    "search": "juan",
    "program": "BS Information Technology",
    "yearLevel": "2nd Year"
  }
}
```

### Response Fields

#### Root Object
| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Whether the request was successful |
| `event` | object | Information about the selected event |
| `stats` | object | Attendance statistics |
| `records` | array | Array of attendance records |
| `filters` | object | Active filters used in the query |

#### Event Object
| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Event ID |
| `name` | string | Event name |
| `date` | string | Event date (YYYY-MM-DD) |

#### Stats Object
| Field | Type | Description |
|-------|------|-------------|
| `total` | integer | Total number of attendees |
| `verified` | integer | Number of face-verified attendees |
| `byProgram` | object | Attendance count grouped by program |
| `byYearLevel` | object | Attendance count grouped by year level |

#### Record Object
| Field | Type | Description |
|-------|------|-------------|
| `id` | string | Record UUID |
| `student_id` | string | Student UUID |
| `event_id` | integer | Event ID |
| `scanned_at` | string | Timestamp of attendance (ISO 8601) |
| `face_verified` | integer | 1 if face-verified, 0 if QR-only |
| `first_name` | string | Student's first name |
| `last_name` | string | Student's last name |
| `student_number` | string | Student ID number |
| `email` | string | Student email |
| `course` | string | Program/Course name |
| `year_level` | string | Academic year level |

#### Filters Object
| Field | Type | Description |
|-------|------|-------------|
| `event` | string | Event ID used in query |
| `search` | string | Search term (if provided) |
| `program` | string | Program filter (or "ALL") |
| `yearLevel` | string | Year level filter (or "ALL") |

### Error Responses

#### 400 Bad Request - Missing Event ID
```json
{
  "error": "Event ID is required"
}
```

**Status**: 400

**Cause**: Event ID parameter missing or invalid

**Solution**: Include valid `event` parameter

#### 500 Internal Server Error - Database Error
```json
{
  "success": false,
  "error": "Database error: [error details]"
}
```

**Status**: 500

**Cause**: Database query error

**Solution**: Check database connection and schema

## Usage Examples

### JavaScript (Fetch API)

```javascript
// Basic fetch
async function getAttendance(eventId) {
  const response = await fetch(`?page=api_attendance&event=${eventId}`);
  const data = await response.json();
  
  if (data.success) {
    console.log(`Total attendees: ${data.stats.total}`);
    console.log('Records:', data.records);
  }
}

// With filters
async function getFilteredAttendance(eventId, program, year, search) {
  const params = new URLSearchParams({
    page: 'api_attendance',
    event: eventId,
    program: program || 'ALL',
    year_level: year || 'ALL',
    search: search || ''
  });
  
  const response = await fetch(`?${params.toString()}`);
  const data = await response.json();
  return data;
}

// Called from Advanced attendance page
```

### jQuery

```javascript
$.ajax({
  url: '?page=api_attendance',
  data: {
    event: 1,
    program: 'BS IT',
    year_level: '2nd Year'
  },
  success: function(data) {
    if (data.success) {
      // Process data
      $('#totalCount').text(data.stats.total);
      $('#verifiedCount').text(data.stats.verified);
      renderTable(data.records);
    }
  }
});
```

### Python (Requests)

```python
import requests

url = 'http://localhost/Taptrack_Attendance/?page=api_attendance'
params = {
    'event': 1,
    'program': 'BS Information Technology',
    'year_level': '2nd Year',
    'search': 'juan'
}

response = requests.get(url, params=params)
data = response.json()

if data['success']:
    print(f"Total attendees: {data['stats']['total']}")
    for record in data['records']:
        print(f"{record['first_name']} {record['last_name']}")
```

### cURL

```bash
# Basic query
curl "http://localhost/Taptrack_Attendance/?page=api_attendance&event=1"

# With filters
curl "http://localhost/Taptrack_Attendance/?page=api_attendance&event=1&program=BS%20IT&year_level=2nd%20Year"

# With search
curl "http://localhost/Taptrack_Attendance/?page=api_attendance&event=1&search=juan"
```

## Data Types & Formats

### Timestamps
```
Format: ISO 8601
Example: "2026-04-15T09:30:00+00:00"
Timezone: Server timezone (usually UTC)
PHP equivalent: date('c', strtotime($timestamp))
```

### Dates
```
Format: YYYY-MM-DD
Example: "2026-04-15"
```

### IDs
```
Student ID: UUID string (e.g., "550e8400-e29b-41d4-a716-446655440000")
Event ID: Integer (e.g., 1, 2, 3)
Record ID: UUID string
```

### Boolean Values
```
1 = true (face_verified)
0 = false
```

## Rate Limiting

**Current**: No rate limiting implemented

**Recommendation**: Implement rate limiting for production:
- Max 100 requests/minute per session
- Based on admin user session ID

## Caching

**Current**: No caching implemented

**Recommendation**: Consider adding:
- Redis caching for large datasets
- Cache key: "attendance:{event_id}:{program}:{year}:{search}"
- TTL: 5 minutes

## Performance Considerations

### Query Performance
- Indexed columns: student_id, event_id, course, year_level
- For events with 10,000+ records, consider pagination
- Search uses LIKE queries (can be slow on large datasets)

### Response Size
- Average record size: ~500 bytes
- 1000 records ≈ 500KB JSON
- Consider pagination for large result sets

### Recommended Optimization
```sql
-- Ensure indexes exist
CREATE INDEX idx_event_student ON attendance(event_id, student_id);
CREATE INDEX idx_course ON students(course);
CREATE INDEX idx_year_level ON students(year_level);
FULLTEXT INDEX idx_fulltext ON students(first_name, last_name, email);
```

## CORS Headers

**Current**: Not set (same-origin request only)

**For cross-origin access**, add:
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
```

## Pagination (Future Enhancement)

Suggested parameters for pagination:
```
?page=api_attendance&event=1&offset=0&limit=50
```

Suggested response field:
```json
{
  "success": true,
  ...
  "pagination": {
    "total": 1000,
    "offset": 0,
    "limit": 50,
    "pages": 20
  }
}
```

## Changelog

### v1.0 (April 9, 2026)
- Initial API release
- Event filter (required)
- Program filter
- Year level filter
- Search functionality
- Statistics calculation

### Future Versions
- Pagination support
- Sorting options
- Custom date range filtering
- Export endpoints (CSV, PDF)
- Rate limiting
- Caching

---

**Last Updated**: April 9, 2026  
**Status**: Production Ready  
**Version**: 1.0
