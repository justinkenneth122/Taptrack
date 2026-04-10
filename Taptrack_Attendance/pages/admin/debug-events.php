<?php
/**
 * Program-Based Event Visibility: Debug & Verification Tool
 * 
 * Use this to test and verify that event filtering is working correctly.
 * Run after deploying the fix to ensure everything is working.
 * 
 * Access at: ?page=debug_events (when logged in as admin)
 */

// Only allow admin access
if (!isAdmin()) {
    echo '<p style="color:red;font-weight:bold;">❌ Access Denied - Admin only</p>';
    exit;
}
?>
<div style="max-width:56rem;margin:0 auto;padding:1.5rem;">
    <h1 style="margin-bottom:1rem;">🔍 Event Visibility Debug Tool</h1>
    <p style="color:#666;margin-bottom:2rem;">Verify that program-based event filtering is working correctly.</p>

    <!-- Database State -->
    <div class="card" style="margin-bottom:2rem;">
        <div class="card-header">
            <div class="card-title">📊 Database State</div>
            <p class="card-desc">Events and their program restrictions</p>
        </div>
        <div class="card-content">
            <table class="table" style="font-size:0.875rem;">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Programs (JSON)</th>
                        <th>QR Token</th>
                        <th>Valid?</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $events_query = $pdo->query("SELECT id, name, date, programs, QR_token FROM events ORDER BY date");
                        $events_all = $events_query->fetchAll();

                        if (empty($events_all)) {
                            echo '<tr><td colspan="5" style="text-align:center;color:#999;">No events found</td></tr>';
                        } else {
                            foreach ($events_all as $evt) {
                                $programs = json_decode($evt['programs'] ?? '["ALL"]', true);
                                $is_valid_json = is_array($programs);
                                $programs_display = $is_valid_json ? json_encode($programs) : '⚠️ INVALID JSON';
                                $programs_display = htmlspecialchars($programs_display);
                                
                                echo '<tr>';
                                echo '<td><strong>' . e($evt['name']) . '</strong></td>';
                                echo '<td>' . e($evt['date']) . '</td>';
                                echo '<td style="font-size:0.8rem;font-family:monospace;background:#f5f5f5;padding:0.5rem;border-radius:4px;">' . $programs_display . '</td>';
                                echo '<td style="font-size:0.8rem;font-family:monospace;word-break:break-all;">' . e(substr($evt['QR_token'], 0, 16) . '...') . '</td>';
                                echo '<td>' . ($is_valid_json ? '✅' : '❌') . '</td>';
                                echo '</tr>';
                            }
                        }
                    } catch (Exception $e) {
                        echo '<tr><td colspan="5" style="color:red;"><strong>Error:</strong> ' . e($e->getMessage()) . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Student Programs -->
    <div class="card" style="margin-bottom:2rem;">
        <div class="card-header">
            <div class="card-title">👥 Student Programs</div>
            <p class="card-desc">Programs stored for each student</p>
        </div>
        <div class="card-content">
            <table class="table" style="font-size:0.875rem;">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Program (course)</th>
                        <th>Trimmed?</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $students_query = $pdo->query("SELECT id, first_name, last_name, email, course FROM students LIMIT 20");
                        $students_all = $students_query->fetchAll();

                        if (empty($students_all)) {
                            echo '<tr><td colspan="4" style="text-align:center;color:#999;">No students found</td></tr>';
                        } else {
                            foreach ($students_all as $stud) {
                                $course = $stud['course'] ?? 'NOT SET';
                                $trimmed = trim($course);
                                $has_spaces = strlen($course) !== strlen($trimmed);
                                
                                echo '<tr>';
                                echo '<td><strong>' . e($stud['first_name'] . ' ' . $stud['last_name']) . '</strong></td>';
                                echo '<td style="font-size:0.8rem;">' . e($stud['email']) . '</td>';
                                echo '<td style="font-family:monospace;background:#f5f5f5;padding:0.25rem 0.5rem;border-radius:4px;">' . e($course) . '</td>';
                                echo '<td>' . ($has_spaces ? '⚠️ HAS SPACES' : '✅') . '</td>';
                                echo '</tr>';
                            }
                        }
                    } catch (Exception $e) {
                        echo '<tr><td colspan="4" style="color:red;"><strong>Error:</strong> ' . e($e->getMessage()) . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Test Scenarios -->
    <div class="card" style="margin-bottom:2rem;">
        <div class="card-header">
            <div class="card-title">🧪 Test Scenarios</div>
            <p class="card-desc">Check if filtering works for each student</p>
        </div>
        <div class="card-content">
            <?php
            try {
                // Get a sample student
                $stmt = $pdo->prepare("SELECT id, first_name, course FROM students LIMIT 1");
                $stmt->execute();
                $sample_student = $stmt->fetch();

                if ($sample_student) {
                    $student_program = trim($sample_student['course'] ?? '');
                    $student_name = $sample_student['first_name'];

                    // Test query
                    $stmt = $pdo->prepare("
                        SELECT id, name FROM events 
                        WHERE archived = 0 
                        AND (
                            JSON_CONTAINS(programs, JSON_QUOTE('ALL'), '$')
                            OR JSON_CONTAINS(programs, JSON_QUOTE(?), '$')
                        )
                        ORDER BY date
                    ");
                    $stmt->execute([$student_program]);
                    $visible_events = $stmt->fetchAll();

                    // Get all events
                    $all_events = $pdo->query("SELECT id, name, programs FROM events WHERE archived = 0")->fetchAll();

                    echo '<p style="margin-bottom:1rem;"><strong>Test for:</strong> ' . e($student_name) . ' (Program: <code style="background:#f5f5f5;padding:0.25rem 0.5rem;">' . e($student_program) . '</code>)</p>';

                    echo '<table class="table" style="font-size:0.875rem;">';
                    echo '<thead><tr><th>Event</th><th>Programs</th><th>Should See?</th><th>Actual</th><th>Status</th></tr></thead>';
                    echo '<tbody>';

                    $visible_ids = array_column($visible_events, 'id');

                    foreach ($all_events as $evt) {
                        $programs = json_decode($evt['programs'] ?? '["ALL"]', true);
                        $should_see = in_array('ALL', $programs ?? []) || in_array($student_program, $programs ?? []);
                        $does_see = in_array($evt['id'], $visible_ids);
                        $match = $should_see === $does_see;

                        echo '<tr>';
                        echo '<td><strong>' . e($evt['name']) . '</strong></td>';
                        echo '<td style="font-size:0.75rem;font-family:monospace;">' . htmlspecialchars(json_encode($programs)) . '</td>';
                        echo '<td>' . ($should_see ? '✅ YES' : '❌ NO') . '</td>';
                        echo '<td>' . ($does_see ? '✅ YES' : '❌ NO') . '</td>';
                        echo '<td>' . ($match ? '<span style="color:green;font-weight:bold;">✅ PASS</span>' : '<span style="color:red;font-weight:bold;">❌ FAIL</span>') . '</td>';
                        echo '</tr>';
                    }

                    echo '</tbody>';
                    echo '</table>';
                } else {
                    echo '<p style="color:#999;">No students found to test.</p>';
                }
            } catch (Exception $e) {
                echo '<p style="color:red;"><strong>Error:</strong> ' . e($e->getMessage()) . '</p>';
            }
            ?>
        </div>
    </div>

    <!-- SQL Query Test -->
    <div class="card" style="margin-bottom:2rem;">
        <div class="card-header">
            <div class="card-title">💻 SQL Query Test</div>
            <p class="card-desc">Test the filtering query directly</p>
        </div>
        <div class="card-content">
            <?php
            if ($_GET['test_program'] ?? '') {
                $test_program = $_GET['test_program'];
                try {
                    $stmt = $pdo->prepare("
                        SELECT id, name, programs FROM events 
                        WHERE archived = 0 
                        AND (
                            JSON_CONTAINS(programs, JSON_QUOTE('ALL'), '$')
                            OR JSON_CONTAINS(programs, JSON_QUOTE(?), '$')
                        )
                        ORDER BY date
                    ");
                    $stmt->execute([$test_program]);
                    $results = $stmt->fetchAll();

                    echo '<p style="margin-bottom:1rem;"><strong>Results for program:</strong> <code style="background:#f5f5f5;padding:0.25rem 0.5rem;">' . e($test_program) . '</code></p>';
                    echo '<p style="margin-bottom:1rem;color:#666;">Found <strong>' . count($results) . '</strong> event(s)</p>';

                    if (count($results) > 0) {
                        echo '<ul style="list-style:none;padding:0;">';
                        foreach ($results as $evt) {
                            echo '<li style="padding:0.5rem 0;border-bottom:1px solid #eee;">';
                            echo '  📅 <strong>' . e($evt['name']) . '</strong>';
                            echo '  <br><small style="color:#666;">Programs: ' . htmlspecialchars(json_encode(json_decode($evt['programs']))) . '</small>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p style="color:#999;">No events match this program.</p>';
                    }
                } catch (Exception $e) {
                    echo '<p style="color:red;"><strong>Error:</strong> ' . e($e->getMessage()) . '</p>';
                }
            } else {
                echo '<p style="margin-bottom:1rem;">Select a program to test the SQL query:</p>';
                echo '<form method="GET" style="display:flex;gap:0.5rem;">';
                echo '<input type="hidden" name="page" value="debug_events">';
                echo '<select name="test_program" class="select">';
                echo '<option value="">Choose a program...</option>';

                try {
                    $programs_stmt = $pdo->query("
                        SELECT DISTINCT programs FROM events 
                        WHERE programs IS NOT NULL AND archived = 0
                    ");
                    $programs_results = $programs_stmt->fetchAll();

                    $all_programs = [];
                    foreach ($programs_results as $row) {
                        $progs = json_decode($row['programs'] ?? '[]', true);
                        if (is_array($progs)) {
                            foreach ($progs as $p) {
                                if (!in_array($p, $all_programs)) {
                                    $all_programs[] = $p;
                                }
                            }
                        }
                    }

                    sort($all_programs);
                    foreach ($all_programs as $prog) {
                        echo '<option value="' . e($prog) . '">' . e($prog) . '</option>';
                    }
                } catch (Exception $e) {
                    echo '<option value="">Error loading programs</option>';
                }

                echo '</select>';
                echo '<button type="submit" class="btn btn-primary">Test Query</button>';
                echo '</form>';
            }
            ?>
        </div>
    </div>

    <!-- Performance Stats -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">⚡ Performance Stats</div>
            <p class="card-desc">Query performance metrics</p>
        </div>
        <div class="card-content">
            <?php
            try {
                // Test unfiltered query
                $start = microtime(true);
                $pdo->query("SELECT * FROM events WHERE archived = 0")->fetchAll();
                $time_unfiltered = (microtime(true) - $start) * 1000;

                // Test filtered query
                $start = microtime(true);
                $stmt = $pdo->prepare("
                    SELECT * FROM events 
                    WHERE archived = 0 
                    AND (
                        JSON_CONTAINS(programs, JSON_QUOTE('ALL'), '$')
                        OR JSON_CONTAINS(programs, JSON_QUOTE(?), '$')
                    )
                ");
                $stmt->execute(['BS Information Technology']);
                $stmt->fetchAll();
                $time_filtered = (microtime(true) - $start) * 1000;

                echo '<table class="table" style="font-size:0.875rem;">';
                echo '<thead><tr><th>Query Type</th><th>Time</th><th>Notes</th></tr></thead>';
                echo '<tbody>';
                echo '<tr>';
                echo '<td><strong>Unfiltered (BEFORE)</strong></td>';
                echo '<td>' . number_format($time_unfiltered, 2) . ' ms</td>';
                echo '<td style="color:#666;">Returns all events</td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td><strong>Filtered (AFTER)</strong></td>';
                echo '<td>' . number_format($time_filtered, 2) . ' ms</td>';
                echo '<td style="color:#666;">Returns only matching events</td>';
                echo '</tr>';
                echo '</tbody>';
                echo '</table>';
                echo '<p style="font-size:0.875rem;color:#666;margin-top:1rem;">Filter query uses indexed QR_token for fast lookups of individual events.</p>';
            } catch (Exception $e) {
                echo '<p style="color:red;">Could not run performance test: ' . e($e->getMessage()) . '</p>';
            }
            ?>
        </div>
    </div>

    <div style="margin-top:2rem;padding:1rem;background:#f0f0f0;border-radius:8px;font-size:0.875rem;">
        <strong>ℹ️ How to Use This Tool:</strong>
        <ul style="margin:0.5rem 0;padding-left:1.5rem;">
            <li><strong>Database State:</strong> Verify events have valid JSON programs</li>
            <li><strong>Student Programs:</strong> Check that student programs have no extra spaces</li>
            <li><strong>Test Scenarios:</strong> See if filtering is working for a sample student</li>
            <li><strong>SQL Query Test:</strong> Run the filtering query with a specific program</li>
            <li><strong>Performance:</strong> Compare query performance before and after fix</li>
        </ul>

        <p style="margin-top:1rem;"><strong>✅ All Green?</strong> Your fix is working correctly!</p>
        <p style="margin:0.5rem 0;"><strong>❌ Something Red?</strong> Check the BUG_FIX_PROGRAM_VISIBILITY.md for troubleshooting.</p>
    </div>
</div>

<?php
/**
 * Helper function to escape HTML
 */
if (!function_exists('e')) {
    function e($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}
?>
