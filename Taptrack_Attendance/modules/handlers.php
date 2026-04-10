<?php
/**
 * Action and AJAX Handlers
 */

// Include face recognition module
require_once __DIR__ . '/../includes/FaceRecognition.php';

function handleAction($pdo, $action) {
    global $ADMIN_USER, $ADMIN_PASS;

    switch ($action) {
        case 'unified_login':
            // Unified login for admin, organizer, and students
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!$email || !$password) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Email and password required.'];
                break;
            }

            // Check if admin credentials match (admin@feuroosevelt.edu.ph)
            if ($email === $ADMIN_USER && $password === $ADMIN_PASS) {
                $_SESSION['user_id'] = 'admin-1';
                $_SESSION['user_role'] = 'admin';
                $_SESSION['user_name'] = 'Administrator';
                $_SESSION['username'] = 'Administrator';
                header('Location: ?page=admin');
                exit;
            }

            // Check users table (Admins and Organizers created in the system)
            try {
                $stmt = $pdo->prepare("
                    SELECT u.id, u.username, u.password, u.email, u.first_name, u.last_name, u.is_active, r.name as role
                    FROM users u
                    JOIN roles r ON u.role_id = r.id
                    WHERE u.email = ? AND u.is_active = 1
                ");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    
                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header('Location: ?page=admin');
                    } elseif ($user['role'] === 'organizer') {
                        header('Location: ?page=organizer');
                    } else {
                        header('Location: ?page=login');
                    }
                    exit;
                }
            } catch (Exception $e) {
                error_log("Error checking users table: " . $e->getMessage());
            }

            // Check student table
            if (preg_match('/^R\d{8,}@feuroosevelt\.edu\.ph$/i', $email)) {
                $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
                $stmt->execute([$email]);
                $student = $stmt->fetch();
                
                if ($student && $student['password'] === $password) {
                    $_SESSION['user_id'] = $student['id'];
                    $_SESSION['user_role'] = 'student';
                    $_SESSION['user_name'] = $student['first_name'] . ' ' . $student['last_name'];
                    header('Location: ?page=student');
                    exit;
                }
            }

            // User not found or password incorrect
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid email or password.'];
            break;

        case 'student_login':
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            if (!preg_match('/^R\d{8,}@feuroosevelt\.edu\.ph$/i', $email)) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid email format. Use R[Number]@feuroosevelt.edu.ph'];
                break;
            }
            $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
            $stmt->execute([$email]);
            $student = $stmt->fetch();
            if (!$student) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'No account found. Please register first.'];
                break;
            }
            if ($student['password'] !== $password) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Wrong password.'];
                break;
            }
            $_SESSION['user_id'] = $student['id'];
            $_SESSION['user_role'] = 'student';
            $_SESSION['user_name'] = $student['first_name'] . ' ' . $student['last_name'];
            header('Location: ?page=student');
            exit;

        case 'student_register':
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $course = $_POST['course'] ?? '';
            $year_level = $_POST['year_level'] ?? '';

            if (!preg_match('/^R\d{8,}@feuroosevelt\.edu\.ph$/i', $email)) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid email format.'];
                break;
            }
            if (!$first_name || !$last_name || !$course || !$year_level || !$password) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Please fill in all fields.'];
                break;
            }
            $stmt = $pdo->prepare("SELECT id FROM students WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Email already registered. Please log in.'];
                break;
            }
            $student_number = explode('@', $email)[0];
            $id = generateUUID();
            $stmt = $pdo->prepare("INSERT INTO students (id, email, first_name, last_name, student_number, course, year_level, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $email, $first_name, $last_name, $student_number, $course, $year_level, $password]);
            $_SESSION['face_reg_student_id'] = $id;
            $_SESSION['user_name'] = "$first_name $last_name";
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Account created! Now register your face to complete setup.'];
            header('Location: ?page=face_register');
            exit;

        case 'skip_face_reg':
            $student_id = $_SESSION['face_reg_student_id'] ?? '';
            if ($student_id) {
                $_SESSION['user_id'] = $student_id;
                $_SESSION['user_role'] = 'student';
                unset($_SESSION['face_reg_student_id']);
                header('Location: ?page=student');
                exit;
            }
            header('Location: ?page=login');
            exit;

        case 'admin_login':
            $user = $_POST['username'] ?? '';
            $pass = $_POST['password'] ?? '';
            if ($user === $ADMIN_USER && $pass === $ADMIN_PASS) {
                $_SESSION['user_id'] = 'admin-1';
                $_SESSION['user_role'] = 'admin';
                $_SESSION['user_name'] = 'Admin';
                header('Location: ?page=admin');
                exit;
            }
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid credentials. Use admin / admin123'];
            break;

        case 'logout':
            session_destroy();
            header('Location: ?page=login');
            exit;

        case 'add_event':
            requireAdmin();
            $name = trim($_POST['name'] ?? '');
            $date = $_POST['date'] ?? '';
            $location = trim($_POST['location'] ?? '');
            $description = trim($_POST['description'] ?? '') ?: null;
            
            // MODIFIED: Handle program restrictions
            $program_restriction = $_POST['program_restriction'] ?? 'ALL';
            $programs = ['ALL'];
            
            if ($program_restriction === 'SPECIFIC') {
                $selected_programs = $_POST['programs'] ?? [];
                if (!empty($selected_programs)) {
                    $programs = array_values(array_map('trim', $selected_programs));
                } else {
                    // If specific was selected but no programs chosen, default to ALL
                    $programs = ['ALL'];
                }
            }
            
            if (!$name || !$date || !$location) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Please fill in name, date, and location.'];
                header('Location: ?page=admin_events');
                exit;
            }
            
            // Generate event ID and QR token
            $id = generateUUID();
            require_once __DIR__ . '/../database/migrations/002_add_program_support.php';
            $qr_token = generateQRToken();
            
            // Ensure uniqueness of QR token
            while (true) {
                $check = $pdo->prepare("SELECT id FROM events WHERE QR_token = ?");
                $check->execute([$qr_token]);
                if (!$check->fetch()) {
                    break;
                }
                $qr_token = generateQRToken();
            }
            
            // Store programs as JSON
            $programs_json = json_encode($programs);
            
            // MODIFIED: Include programs and QR_token in INSERT
            $stmt = $pdo->prepare("INSERT INTO events (id, name, date, location, description, programs, QR_token, archived) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
            $stmt->execute([$id, $name, $date, $location, $description, $programs_json, $qr_token]);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => "Event \"$name\" created with program restrictions."];
            header('Location: ?page=admin_events');
            exit;

        case 'archive_event':
            requireAdmin();
            $id = $_POST['event_id'] ?? '';
            $pdo->prepare("UPDATE events SET archived = 1 WHERE id = ?")->execute([$id]);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Event archived.'];
            header('Location: ?page=admin_events');
            exit;

        case 'record_attendance':
            requireAdmin();
            $student_id = $_POST['student_id'] ?? '';
            $event_id = $_POST['event_id'] ?? '';
            $stmt = $pdo->prepare("SELECT id FROM attendance WHERE student_id = ? AND event_id = ?");
            $stmt->execute([$student_id, $event_id]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Already recorded']);
                exit;
            }
            $id = generateUUID();
            $pdo->prepare("INSERT INTO attendance (id, student_id, event_id) VALUES (?, ?, ?)")->execute([$id, $student_id, $event_id]);
            $stmt = $pdo->prepare("SELECT first_name, last_name FROM students WHERE id = ?");
            $stmt->execute([$student_id]);
            $stu = $stmt->fetch();
            echo json_encode(['success' => true, 'message' => ($stu ? $stu['first_name'] . ' ' . $stu['last_name'] : 'Student') . ' — Attendance recorded!']);
            exit;
    }
}

function handleAjax($pdo, $type) {
    switch ($type) {
        case 'scan_qr':
            // =========================== QR ATTENDANCE CHECK-IN ===========================
            // This is the secure backend validation for program-based event check-in
            
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
                return;
            }
            
            // UPDATED: Extract event_id and student_id from request
            $event_id = $data['eventId'] ?? '';
            $student_id = $data['studentId'] ?? '';
            
            // Validate required fields
            if (!$event_id || !$student_id) {
                echo json_encode(['success' => false, 'message' => 'Missing required QR data (event_id or student_id).']);
                return;
            }
            
            // Ensure they are numeric
            if (!is_numeric($event_id) || !is_numeric($student_id)) {
                echo json_encode(['success' => false, 'message' => 'Invalid QR data format.']);
                return;
            }
            
            // ======================== STEP 1: Get Student Info ========================
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, course FROM students WHERE id = ?");
            $stmt->execute([$student_id]);
            $student = $stmt->fetch();
            
            if (!$student) {
                echo json_encode(['success' => false, 'message' => 'Student not found.']);
                return;
            }
            
            // Use 'course' as program (map from database schema)
            // FIX: Trim whitespace to prevent matching issues
            $student_program = trim($student['course'] ?? '');
            $student_name = $student['first_name'] . ' ' . $student['last_name'];
            
            // ======================== STEP 2: Get Event Info ========================
            $stmt = $pdo->prepare("SELECT id, name, programs FROM events WHERE id = ? AND archived = 0");
            $stmt->execute([$event_id]);
            $event = $stmt->fetch();
            
            if (!$event) {
                echo json_encode(['success' => false, 'message' => 'Event not found or is archived.']);
                return;
            }
            
            $event_name = $event['name'];
            
            // ======================== STEP 3: Program Authorization ========================
            $allowed_programs = json_decode($event['programs'] ?? '["ALL"]', true);
            
            // FIX: Normalize program names by trimming whitespace
            // This ensures "BS Information Technology" matches regardless of extra spaces
            if (is_array($allowed_programs)) {
                $allowed_programs = array_map('trim', $allowed_programs);
            }
            
            // Check if event is open to all programs or if student's program is in the list
            $is_authorized = false;
            
            if (is_array($allowed_programs)) {
                if (in_array('ALL', $allowed_programs)) {
                    // Event is open to all programs
                    $is_authorized = true;
                } else if (in_array($student_program, $allowed_programs)) {
                    // Student's program is in the allowed list
                    $is_authorized = true;
                }
            }
            
            // VALIDATE: If NOT authorized, DENY check-in
            if (!$is_authorized) {
                echo json_encode([
                    'success' => false,
                    'message' => "❌ $student_name — You are not authorized to attend this event based on your program ($student_program).",
                    'error_type' => 'authorization_denied'
                ]);
                return;
            }
            
            // ======================== STEP 4: Duplicate Check-In Prevention ========================
            $stmt = $pdo->prepare("SELECT id FROM attendance WHERE student_id = ? AND event_id = ?");
            $stmt->execute([$student_id, $event_id]);
            
            if ($stmt->fetch()) {
                echo json_encode([
                    'success' => false,
                    'message' => "⚠️ $student_name — You have already checked in to this event.",
                    'error_type' => 'already_checked_in'
                ]);
                return;
            }
            
            // ======================== STEP 5: Record Attendance ========================
            $attendance_id = generateUUID();
            $stmt = $pdo->prepare("INSERT INTO attendance (id, student_id, event_id) VALUES (?, ?, ?)");
            $stmt->execute([$attendance_id, $student_id, $event_id]);
            
            // SUCCESS: Return confirmation
            echo json_encode([
                'success' => true,
                'message' => "✅ $student_name ($student_program) — Check-in successful!",
                'student_name' => $student_name,
                'event_name' => $event_name
            ]);
            return;

        case 'save_face_descriptor':
            $data = json_decode(file_get_contents('php://input'), true);
            $student_id = $data['student_id'] ?? '';
            $descriptor = $data['face_descriptor'] ?? '';
            
            if (!$student_id || !$descriptor) {
                echo json_encode(['success' => false, 'message' => 'Missing data.']);
                return;
            }
            
            // Use FaceRecognition module to validate and register
            // This includes duplicate face detection
            $result = registerFaceDescriptor($pdo, $student_id, $descriptor);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                // Extract duplicate info if available
                $response = [
                    'success' => false,
                    'message' => $result['message']
                ];
                
                if (!empty($result['code']) && $result['code'] === 'DUPLICATE_FACE') {
                    $response['duplicate'] = $result['duplicate'];
                }
                
                echo json_encode($response);
            }
            return;

        case 'check_face_duplicate':
            $data = json_decode(file_get_contents('php://input'), true);
            $descriptor = $data['face_descriptor'] ?? '';
            $student_id = $data['student_id'] ?? '';
            
            if (!$descriptor) {
                echo json_encode(['success' => false, 'message' => 'Missing descriptor.']);
                return;
            }
            
            // Validate descriptor format
            $validation = validateFaceDescriptor($descriptor);
            if (!$validation['valid']) {
                echo json_encode(['success' => false, 'message' => 'Invalid face data.']);
                return;
            }
            
            // Check for duplicates
            $duplicate = checkFaceDuplicate($pdo, $validation['descriptor'], $student_id);
            
            echo json_encode([
                'success' => true,
                'is_duplicate' => $duplicate['match'],
                'confidence' => round($duplicate['confidence'] * 100, 1),
                'matched_student' => $duplicate['student_name'],
                'threshold' => floatval(defined('FACE_SIMILARITY_THRESHOLD') ? FACE_SIMILARITY_THRESHOLD : 0.6)
            ]);
            return;

        case 'get_students':
            $stmt = $pdo->query("SELECT id, first_name, last_name, student_number FROM students ORDER BY created_at");
            echo json_encode($stmt->fetchAll());
            return;

        case 'get_face_descriptor':
            $student_id = $_GET['student_id'] ?? '';
            if (!$student_id) {
                echo json_encode(['success' => false, 'message' => 'Missing student ID.']);
                return;
            }
            $stmt = $pdo->prepare("SELECT face_descriptor FROM students WHERE id = ?");
            $stmt->execute([$student_id]);
            $row = $stmt->fetch();
            echo json_encode(['success' => true, 'face_descriptor' => $row['face_descriptor'] ?? null]);
            return;
    }
}
