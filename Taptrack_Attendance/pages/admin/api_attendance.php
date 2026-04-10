<?php
/**
 * Attendance API - AJAX Handler for Real-time Filtering
 * 
 * This endpoint returns attendance data based on filters.
 * Supports JSON response format for dynamic frontend filtering.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';

// Get filter parameters
$eventId = $_GET['event'] ?? null;
$searchTerm = $_GET['search'] ?? '';
$program = $_GET['program'] ?? 'ALL';
$yearLevel = $_GET['year_level'] ?? 'ALL';

// Validate event ID
if (!$eventId || !is_numeric($eventId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Event ID is required']);
    exit;
}

try {
    // Check if face_verified column exists
    $columnCheckResult = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'attendance' AND COLUMN_NAME = 'face_verified'");
    $hasFaceVerified = $columnCheckResult->rowCount() > 0;
    
    // If column doesn't exist, try to create it
    if (!$hasFaceVerified) {
        try {
            $pdo->exec("ALTER TABLE attendance ADD COLUMN face_verified TINYINT(1) DEFAULT 0 AFTER scanned_at");
            $hasFaceVerified = true;
        } catch (Exception $e) {
            // Column creation failed, will use without it
            $hasFaceVerified = false;
        }
    }
    
    // Build query based on filters
    // Conditionally select face_verified column only if it exists
    $faceVerifiedColumn = $hasFaceVerified ? 'a.face_verified' : '0 as face_verified';
    
    $query = "SELECT 
                a.id, 
                a.student_id, 
                a.event_id, 
                a.scanned_at, 
                $faceVerifiedColumn,
                s.first_name, 
                s.last_name, 
                s.student_number, 
                s.email,
                s.course, 
                s.year_level
              FROM attendance a 
              JOIN students s ON a.student_id = s.id 
              WHERE a.event_id = ?";
    
    $params = [$eventId];
    
    // Add program filter
    if ($program !== 'ALL') {
        $query .= " AND s.course = ?";
        $params[] = $program;
    }
    
    // Add year level filter
    if ($yearLevel !== 'ALL') {
        $query .= " AND s.year_level = ?";
        $params[] = $yearLevel;
    }
    
    // Add search filter
    if ($searchTerm) {
        $query .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.email LIKE ? OR s.student_number LIKE ?)";
        $searchParam = "%" . $searchTerm . "%";
        array_push($params, $searchParam, $searchParam, $searchParam, $searchParam);
    }
    
    $query .= " ORDER BY a.scanned_at DESC";
    
    // Execute query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $records = $stmt->fetchAll();
    
    // Get event info
    $eventStmt = $pdo->prepare("SELECT name, date FROM events WHERE id = ?");
    $eventStmt->execute([$eventId]);
    $eventInfo = $eventStmt->fetch();
    
    // Calculate statistics
    $totalRecords = count($records);
    $verifiedCount = count(array_filter($records, fn($r) => $r['face_verified']));
    
    // Group by program
    $byProgram = [];
    foreach ($records as $record) {
        $program = $record['course'];
        if (!isset($byProgram[$program])) {
            $byProgram[$program] = 0;
        }
        $byProgram[$program]++;
    }
    
    // Group by year level
    $byYearLevel = [];
    foreach ($records as $record) {
        $year = $record['year_level'];
        if (!isset($byYearLevel[$year])) {
            $byYearLevel[$year] = 0;
        }
        $byYearLevel[$year]++;
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'event' => $eventInfo,
        'stats' => [
            'total' => $totalRecords,
            'verified' => $verifiedCount,
            'byProgram' => $byProgram,
            'byYearLevel' => $byYearLevel
        ],
        'records' => $records,
        'filters' => [
            'event' => $eventId,
            'search' => $searchTerm,
            'program' => $program,
            'yearLevel' => $yearLevel
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
