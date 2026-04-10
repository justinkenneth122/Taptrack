<?php
/**
 * Face Recognition Module
 * Handles face descriptor validation, similarity matching, and duplicate detection
 */

/**
 * Calculate Euclidean distance between two face descriptors
 * Lower distance = more similar faces
 * 
 * @param array $descriptor1 - Array of 128 float values
 * @param array $descriptor2 - Array of 128 float values
 * @return float - Euclidean distance
 */
function calculateFaceDistance($descriptor1, $descriptor2) {
    if (!is_array($descriptor1) || !is_array($descriptor2)) {
        return PHP_FLOAT_MAX;
    }
    
    if (count($descriptor1) !== 128 || count($descriptor2) !== 128) {
        return PHP_FLOAT_MAX;
    }
    
    $sumSquares = 0;
    for ($i = 0; $i < 128; $i++) {
        $diff = (float)$descriptor1[$i] - (float)$descriptor2[$i];
        $sumSquares += $diff * $diff;
    }
    
    return sqrt($sumSquares);
}

/**
 * Calculate similarity score between two face descriptors
 * Returns value between 0 and 1 (1 = identical, 0 = completely different)
 * 
 * @param array $descriptor1
 * @param array $descriptor2
 * @return float - Similarity score (0-1)
 */
function calculateFaceSimilarity($descriptor1, $descriptor2) {
    $distance = calculateFaceDistance($descriptor1, $descriptor2);
    // Convert distance to similarity score
    // Typical threshold for face-api.js is 0.6 (distance of ~0.4)
    return max(0, 1 - ($distance / 1.5));
}

/**
 * Check if a face descriptor matches any existing registered faces
 * 
 * @param PDO $pdo - Database connection
 * @param array $newDescriptor - New face descriptor to check
 * @param string $excludeStudentId - Student ID to exclude from check (for re-registration)
 * @return array - ['match' => bool, 'student_id' => string, 'confidence' => float, 'student_name' => string]
 */
function checkFaceDuplicate($pdo, $newDescriptor, $excludeStudentId = '') {
    try {
        // Get all registered face descriptors
        $query = "SELECT id, first_name, last_name, face_descriptor FROM students WHERE face_descriptor IS NOT NULL AND face_descriptor != ''";
        $params = [];
        
        if ($excludeStudentId) {
            $query .= " AND id != ?";
            $params[] = $excludeStudentId;
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $students = $stmt->fetchAll();
        
        // Configuration
        $threshold = floatval(defined('FACE_SIMILARITY_THRESHOLD') ? FACE_SIMILARITY_THRESHOLD : 0.6);
        $bestMatch = [
            'match' => false,
            'student_id' => null,
            'confidence' => 0,
            'student_name' => null,
            'distance' => PHP_FLOAT_MAX
        ];
        
        // Check against each registered descriptor
        foreach ($students as $student) {
            try {
                $existingDescriptor = json_decode($student['face_descriptor'], true);
                
                if (!is_array($existingDescriptor)) {
                    continue;
                }
                
                $similarity = calculateFaceSimilarity($newDescriptor, $existingDescriptor);
                $distance = calculateFaceDistance($newDescriptor, $existingDescriptor);
                
                // Update best match if this is more similar
                if ($similarity > $bestMatch['confidence']) {
                    $bestMatch['confidence'] = $similarity;
                    $bestMatch['distance'] = $distance;
                    $bestMatch['student_id'] = $student['id'];
                    $bestMatch['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
                }
                
                // If exceeds threshold, return immediate match
                if ($similarity >= $threshold) {
                    $bestMatch['match'] = true;
                    return $bestMatch;
                }
            } catch (Exception $e) {
                // Skip invalid descriptors
                continue;
            }
        }
        
        return $bestMatch;
    } catch (Exception $e) {
        return [
            'match' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Validate face descriptor format and integrity
 * 
 * @param string $descriptorJSON - JSON string of face descriptor
 * @return array - ['valid' => bool, 'error' => string|null, 'descriptor' => array|null]
 */
function validateFaceDescriptor($descriptorJSON) {
    try {
        if (!is_string($descriptorJSON) || empty($descriptorJSON)) {
            return [
                'valid' => false,
                'error' => 'Face descriptor is empty'
            ];
        }
        
        $descriptor = json_decode($descriptorJSON, true);
        
        if (!is_array($descriptor)) {
            return [
                'valid' => false,
                'error' => 'Invalid descriptor format'
            ];
        }
        
        if (count($descriptor) !== 128) {
            return [
                'valid' => false,
                'error' => 'Descriptor must have 128 values'
            ];
        }
        
        // Verify all values are numeric
        foreach ($descriptor as $value) {
            if (!is_numeric($value)) {
                return [
                    'valid' => false,
                    'error' => 'Descriptor contains non-numeric values'
                ];
            }
        }
        
        return [
            'valid' => true,
            'descriptor' => $descriptor
        ];
    } catch (Exception $e) {
        return [
            'valid' => false,
            'error' => 'Error parsing descriptor: ' . $e->getMessage()
        ];
    }
}

/**
 * Register face descriptor for a student
 * Includes validation and duplicate checking
 * 
 * @param PDO $pdo - Database connection
 * @param string $studentId - Student ID
 * @param string $descriptorJSON - JSON string of face descriptor
 * @param string $excludeStudentId - Optional: Student ID to exclude from duplicate check
 * @return array - ['success' => bool, 'message' => string, 'duplicate' => array|null]
 */
function registerFaceDescriptor($pdo, $studentId, $descriptorJSON, $excludeStudentId = '') {
    // Validate descriptor format
    $validation = validateFaceDescriptor($descriptorJSON);
    if (!$validation['valid']) {
        return [
            'success' => false,
            'message' => 'Invalid face data: ' . $validation['error']
        ];
    }
    
    // Check for duplicate
    $duplicate = checkFaceDuplicate($pdo, $validation['descriptor'], $excludeStudentId ?: $studentId);
    
    if ($duplicate['match']) {
        return [
            'success' => false,
            'message' => 'Face already registered with another account',
            'duplicate' => [
                'student_id' => $duplicate['student_id'],
                'student_name' => $duplicate['student_name'],
                'confidence' => round($duplicate['confidence'] * 100, 1)
            ],
            'code' => 'DUPLICATE_FACE'
        ];
    }
    
    // Save descriptor
    try {
        $stmt = $pdo->prepare("UPDATE students SET face_descriptor = ? WHERE id = ?");
        $stmt->execute([$descriptorJSON, $studentId]);
        
        return [
            'success' => true,
            'message' => 'Face registered successfully'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}

/**
 * Get face registration statistics
 * 
 * @param PDO $pdo - Database connection
 * @return array - Stats about face registration
 */
function getFaceRegistrationStats($pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM students");
        $total = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as registered FROM students WHERE face_descriptor IS NOT NULL AND face_descriptor != ''");
        $registered = $stmt->fetch()['registered'];
        
        return [
            'total_students' => $total,
            'faces_registered' => $registered,
            'registration_rate' => $total > 0 ? round(($registered / $total) * 100, 1) : 0
        ];
    } catch (Exception $e) {
        return [
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Find potential duplicate faces
 * Useful for admin audit and data cleanup
 * 
 * @param PDO $pdo - Database connection
 * @param float $threshold - Similarity threshold (0-1, default 0.6)
 * @return array - List of potential duplicates
 */
function findPotentialDuplicates($pdo, $threshold = 0.6) {
    try {
        $stmt = $pdo->query("SELECT id, first_name, last_name, face_descriptor FROM students WHERE face_descriptor IS NOT NULL AND face_descriptor != ''");
        $students = $stmt->fetchAll();
        
        $duplicates = [];
        
        for ($i = 0; $i < count($students); $i++) {
            for ($j = $i + 1; $j < count($students); $j++) {
                try {
                    $desc1 = json_decode($students[$i]['face_descriptor'], true);
                    $desc2 = json_decode($students[$j]['face_descriptor'], true);
                    
                    if (!is_array($desc1) || !is_array($desc2)) {
                        continue;
                    }
                    
                    $similarity = calculateFaceSimilarity($desc1, $desc2);
                    
                    if ($similarity >= $threshold) {
                        $duplicates[] = [
                            'student1' => [
                                'id' => $students[$i]['id'],
                                'name' => $students[$i]['first_name'] . ' ' . $students[$i]['last_name']
                            ],
                            'student2' => [
                                'id' => $students[$j]['id'],
                                'name' => $students[$j]['first_name'] . ' ' . $students[$j]['last_name']
                            ],
                            'similarity' => round($similarity * 100, 1)
                        ];
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
        }
        
        return $duplicates;
    } catch (Exception $e) {
        return [
            'error' => $e->getMessage()
        ];
    }
}
