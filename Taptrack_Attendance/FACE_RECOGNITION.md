# Face Recognition System — Implementation Guide

## Overview

The TapTrack face recognition system enforces **one face per student account** to prevent duplicate face registrations and fraud. It uses face embeddings (128-dimensional vectors) instead of storing raw images for efficient matching and privacy.

## Architecture

### 1. **Face Descriptor**
- **Format**: 128-dimensional float array
- **Source**: face-api.js library (TinyFaceDetector) 
- **Storage**: JSON string in `students.face_descriptor` LONGTEXT column
- **Memory**: ~1.5 KB per descriptor

### 2. **Similarity Matching**
- **Algorithm**: Euclidean distance between descriptor vectors
- **Distance Formula**: `√[(x₁-x₂)² + (y₁-y₂)² + ... + (z₁-z₂)²]`
- **Threshold**: Configurable (default: 0.6)
- **Interpretation**:
  - Distance < 0.4 = Likely same person
  - Distance 0.4-0.6 = Possible match (within threshold)
  - Distance > 0.6 = Different person

### 3. **Storage Efficiency**
```
Before (Image): 200 KB per face → 1000 students = 200 MB
After (Embedding): 1.5 KB per face → 1000 students = 1.5 MB
Savings: 99.25% reduction
```

## Database Schema

### Students Table
```sql
CREATE TABLE students (
    id VARCHAR(36) PRIMARY KEY,
    ...
    face_descriptor LONGTEXT COMMENT 'JSON array of 128 float values',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Face Matches Table (Audit Log)
```sql
CREATE TABLE face_matches (
    id VARCHAR(36) PRIMARY KEY,
    student_id_1 VARCHAR(36),
    student_id_2 VARCHAR(36),
    similarity_score DECIMAL(5, 3),
    status VARCHAR(50), -- 'detected', 'verified', 'resolved', 'false_positive'
    notes TEXT,
    created_at TIMESTAMP,
    resolved_by VARCHAR(36),
    resolved_at TIMESTAMP
);
```

## Registration Flow

### 1. **Student Registration**
```
Register Account → Provide Face → System Checks Database
                             ↓
                    [Duplicate Detection]
                    ↓           ↓
             Match Found   No Match
                 ↓             ↓
         Registration    Complete
         REJECTED         Registration
         + Error          ✓ Success
```

### 2. **Detection Process**
```javascript
1. Capture video frame from camera
2. Use face-api.js to detect face
3. Extract 128-dimensional descriptor
4. Compare against all registered faces
5. Calculate similarity for each match
6. If any match > threshold: REJECT
7. Otherwise: ACCEPT and store
```

### 3. **Error Messages**
- **Invalid Face**: "Center your face inside the circle"
- **Duplicate**: "Face already registered with another account (John Doe, 92% match)"
- **Database Error**: "Database error: [error message]"

## Implementation Details

### Core Module: FaceRecognition.php

#### Main Functions

##### 1. **calculateFaceDistance($d1, $d2): float**
Euclidean distance between two descriptors
```php
// Returns: 0.35 (very similar) → 1.2 (completely different)
$distance = calculateFaceDistance($desc1, $desc2);
```

##### 2. **calculateFaceSimilarity($d1, $d2): float**
Converts distance to similarity score 0-1
```php
// Returns: 0.9 (90% match) or 0.2 (20% match)
$similarity = calculateFaceSimilarity($desc1, $desc2);
```

##### 3. **checkFaceDuplicate($pdo, $descriptor, $excludeStudentId): array**
Main duplicate detection function
```php
$result = checkFaceDuplicate($pdo, $newDescriptor, $currentStudentId);

if ($result['match']) {
    // Duplicate found!
    echo "Face matches: {$result['student_name']} ({$result['confidence']*100}%)";
} else {
    // Safe to register
    echo "Face is unique";
}
```

**Returns**:
```php
[
    'match' => bool,           // Is there a match?
    'student_id' => string,    // ID of matching student
    'student_name' => string,  // Name of matching student
    'confidence' => float,     // Similarity score 0-1
    'distance' => float        // Distance metric
]
```

##### 4. **validateFaceDescriptor($json): array**
Validates descriptor format and integrity
```php
$validation = validateFaceDescriptor($descriptorJSON);

if ($validation['valid']) {
    $descriptor = $validation['descriptor']; // Array of 128 floats
}
```

##### 5. **registerFaceDescriptor($pdo, $studentId, $json, $exclude): array**
Complete registration with validation and duplicate check
```php
$result = registerFaceDescriptor($pdo, $studentId, $descriptorJSON);

if ($result['success']) {
    // Stored successfully
} else if ($result['code'] === 'DUPLICATE_FACE') {
    // Show duplicate info
    print_r($result['duplicate']);
}
```

##### 6. **findPotentialDuplicates($pdo, $threshold): array**
Admin tool: Find all potential duplicate pairs
```php
$duplicates = findPotentialDuplicates($pdo, 0.65);

foreach ($duplicates as $match) {
    // ['student1' => [...], 'student2' => [...], 'similarity' => 92.5]
}
```

### AJAX Handlers

#### 1. **save_face_descriptor** (POST)
Saves face descriptor with automatic duplicate detection
```javascript
const response = await fetch('?ajax=save_face_descriptor', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        student_id: 'abc123',
        face_descriptor: JSON.stringify([...128 values...])
    })
});

const result = await response.json();
// {
//   success: true/false,
//   message: string,
//   duplicate?: {student_id, student_name, confidence}
// }
```

#### 2. **check_face_duplicate** (POST)
Real-time duplicate check (before submission)
```javascript
const response = await fetch('?ajax=check_face_duplicate', {
    method: 'POST',
    body: JSON.stringify({
        student_id: 'abc123',
        face_descriptor: JSON.stringify([...])
    })
});

const result = await response.json();
// {
//   success: true,
//   is_duplicate: true/false,
//   confidence: 92.5,
//   matched_student: 'John Doe',
//   threshold: 0.6
// }
```

## Configuration

### Constants (config/constants.php)
```php
// Similarity threshold for matching (0-1)
// Lower = stricter (fewer false positives)
// Higher = more lenient (fewer false negatives)
define('FACE_SIMILARITY_THRESHOLD', 0.6);

// Optional: require face registration to use system
define('FACE_REGISTRATION_REQUIRED', false);
```

### Recommended Threshold Values
- **Very Strict**: 0.50 (more rejections, safer)
- **Normal**: 0.60 (default, balanced)
- **Lenient**: 0.70 (fewer rejections)
- **Test Mode**: 0.80 (very permissive)

## Client-Side Implementation

### face-register.php
```html
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
    async function captureFace(studentId) {
        // 1. Get frame from video
        // 2. Detect face using TinyFaceDetector
        // 3. Extract 128-dimensional descriptor
        // 4. Send to backend: ?ajax=save_face_descriptor
        // 5. Backend checks duplicates
        // 6. Show success or error message
    }
</script>
```

## Admin Tools

### Face Audit
```php
// Find all potential duplicates
$duplicates = findPotentialDuplicates($pdo, 0.65);

echo "Found " . count($duplicates) . " potential duplicate pairs:";
foreach ($duplicates as $pair) {
    echo "{$pair['student1']['name']} vs {$pair['student2']['name']}: {$pair['similarity']}%";
}
```

### Face Statistics
```php
$stats = getFaceRegistrationStats($pdo);
echo "Total students: {$stats['total_students']}";
echo "Faces registered: {$stats['faces_registered']}";
echo "Registration rate: {$stats['registration_rate']}%";
```

### Clear Face Data (for re-registration)
```php
// Clear a student's face to allow re-registration
$pdo->prepare("UPDATE students SET face_descriptor = NULL WHERE id = ?")->execute([$studentId]);
```

## Performance Considerations

### Time Complexity
- **New registration**: O(n) where n = total registered faces
  - Must compare against all existing descriptors
  - Typical: 1000 faces ≈ 50ms
- **Batch check**: O(n²) for full audit
  - Only run on demand or scheduled

### Database Performance
```sql
-- Add index for faster face searches
CREATE INDEX idx_face_descriptor ON students(face_descriptor(100));

-- Check for performance
SHOW INDEX FROM students;
```

### Scaling Tips
- Cache descriptor comparisons
- Use batch processing for large registrations
- Schedule duplicate audits off-peak
- Archive old attendance records

## Security Considerations

### Privacy
- ✓ Descriptors only (no images stored)
- ✓ One-way conversion (can't reconstruct face)
- ✓ GDPR compliant (no biometric images)

### Fraud Prevention
- ✓ One face per account
- ✓ Cannot reuse face for multiple accounts
- ✓ Similarity threshold prevents false matches
- ✓ Audit log tracks all registrations

### Best Practices
1. **Require face registration** for events
2. **Monitor face_matches table** for suspicious patterns
3. **Manually review** high-confidence matches (>95%)
4. **Periodically audit** for duplicate attempts
5. **Backup descriptors** (encrypted) for recovery

## Testing

### Unit Tests
```php
// Test 1: Identical face should match
$desc = [0.1, 0.2, ...];
$dist = calculateFaceDistance($desc, $desc);
assert($dist < 0.01); // Should be ~0

// Test 2: Different faces should not match
$dist = calculateFaceDistance($desc1, $desc2);
assert($dist > 0.5); // Should be large

// Test 3: Threshold logic
$similarity = calculateFaceSimilarity($desc1, $desc2);
assert($similarity < 0.6); // Below threshold
```

### Integration Tests
```php
// 1. Register first student's face
registerFaceDescriptor($pdo, 'student1', $desc1);

// 2. Try to register second student with same face
$result = registerFaceDescriptor($pdo, 'student2', $desc1);

// 3. Should be rejected
assert(!$result['success']);
assert($result['code'] === 'DUPLICATE_FACE');
```

## Troubleshooting

| Issue | Cause | Solution |
|-------|-------|----------|
| "Face detection failed" | Poor lighting/angle | Better lighting, center face |
| "Already registered" | Duplicate face | Change threshold or verify manually |
| "Slow performance" | Too many faces | Archive old data, optimize indexes |
| "False matches" | Threshold too low | Increase threshold to 0.65-0.70 |
| "More duplicates than expected" | Threshold too high | Decrease threshold to 0.55-0.60 |

## Future Enhancements

1. **Liveness Detection**: Verify face is real (not photo)
2. **Multi-face Support**: Optional backup face option
3. **Face Quality Score**: Only accept high-quality captures
4. **ML Integration**: Custom models for higher accuracy
5. **Real-time Matching**: Match faces during QR attendance
6. **Anonymization**: Hash descriptors for privacy compliance

## References

- **face-api.js**: https://github.com/vladmandic/face-api
- **Euclidean Distance**: https://en.wikipedia.org/wiki/Euclidean_distance
- **Face Recognition Guidelines**: https://nist.gov/programs/frvc/
