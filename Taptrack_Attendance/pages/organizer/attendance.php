<?php
/**
 * Organizer Attendance Management
 * 
 * Shows organizer's ability to:
 * - View attendance for their events
 * - Filter by event and status
 * - Export attendance records
 */

$organizerId = $_SESSION['user_id'] ?? null;
$eventId = $_GET['event_id'] ?? null;

if (!$organizerId) {
    echo '<div class="card"><p class="text-center text-muted">Session expired. Please log in again.</p></div>';
    return;
}

// Get all events for filter dropdown
try {
    $stmt = $pdo->query("SELECT id, program_name FROM events ORDER BY event_date DESC");
    $events = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error fetching events: " . $e->getMessage());
    $events = [];
}

// Get attendance records
try {
    if ($eventId) {
        $stmt = $pdo->prepare("
            SELECT a.id, a.student_id, a.status, a.scanned_at, 
                   s.first_name, s.last_name, s.email, e.program_name
            FROM attendance a
            JOIN students s ON a.student_id = s.id
            JOIN events e ON a.event_id = e.id
            WHERE a.event_id = ?
            ORDER BY a.scanned_at DESC
        ");
        $stmt->execute([$eventId]);
    } else {
        $stmt = $pdo->query("
            SELECT a.id, a.student_id, a.status, a.scanned_at, 
                   s.first_name, s.last_name, s.email, e.program_name
            FROM attendance a
            JOIN students s ON a.student_id = s.id
            JOIN events e ON a.event_id = e.id
            ORDER BY a.scanned_at DESC
            LIMIT 100
        ");
    }
    $attendanceRecords = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error fetching attendance: " . $e->getMessage());
    $attendanceRecords = [];
}

// Statistics
$totalScanned = count($attendanceRecords);
$presentCount = count(array_filter($attendanceRecords, fn($r) => $r['status'] === 'present'));
$absentCount = count(array_filter($attendanceRecords, fn($r) => $r['status'] === 'absent'));
$presentRate = $totalScanned > 0 ? round(($presentCount / $totalScanned) * 100) : 0;
?>

<div class="space-y-6">
    <!-- Header -->
    <div>
        <h2 style="font-size: 1.5rem;" class="font-bold">📋 Attendance Management</h2>
        <p class="text-muted" style="margin-top: 0.5rem;">View and manage attendance records</p>
    </div>

    <!-- Statistics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
        <div class="card">
            <div class="card-content">
                <p style="font-size: 0.875rem; color: #6b7280;">Total Scanned</p>
                <p style="font-size: 1.875rem; font-weight: bold; margin-top: 0.5rem;"><?= $totalScanned ?></p>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <p style="font-size: 0.875rem; color: #22c55e;">Present</p>
                <p style="font-size: 1.875rem; font-weight: bold; margin-top: 0.5rem; color: #22c55e;"><?= $presentCount ?></p>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <p style="font-size: 0.875rem; color: #dc2626;">Absent</p>
                <p style="font-size: 1.875rem; font-weight: bold; margin-top: 0.5rem; color: #dc2626;"><?= $absentCount ?></p>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <p style="font-size: 0.875rem; color: #3b82f6;">Present Rate</p>
                <p style="font-size: 1.875rem; font-weight: bold; margin-top: 0.5rem; color: #3b82f6;"><?= $presentRate ?>%</p>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">🔍 Filter Records</div>
        </div>
        <div class="card-content">
            <form method="GET" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                <input type="hidden" name="page" value="organizer_attendance">
                
                <div style="flex: 1; min-width: 250px;">
                    <label class="label">Filter by Event</label>
                    <select name="event_id" class="input" onchange="this.form.submit()">
                        <option value="">All Events</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?= $event['id'] ?>" <?= $eventId == $event['id'] ? 'selected' : '' ?>>
                                <?= e($event['program_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <a href="?page=organizer_attendance" class="btn btn-outline">Clear Filter</a>
            </form>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📊 Attendance Records</div>
            <p class="card-desc"><?= count($attendanceRecords) ?> record(s) found</p>
        </div>
        <div class="card-content">
            <?php if (empty($attendanceRecords)): ?>
                <p class="text-center text-muted py-8">No attendance records found</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="table" style="width: 100%; min-width: 600px;">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Event</th>
                                <th>Email</th>
                                <th>Scanned At</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceRecords as $record): ?>
                                <tr>
                                    <td class="font-medium"><?= e($record['first_name'] . ' ' . $record['last_name']) ?></td>
                                    <td class="text-sm"><?= e($record['program_name']) ?></td>
                                    <td class="text-sm text-muted"><?= e($record['email']) ?></td>
                                    <td class="text-sm text-muted"><?= date('M j, Y g:i A', strtotime($record['scanned_at'])) ?></td>
                                    <td>
                                        <span class="badge" style="background:<?= $record['status'] === 'present' ? '#dcfce7' : '#fee2e2' ?>; color:<?= $record['status'] === 'present' ? '#22c55e' : '#dc2626' ?>;">
                                            <?= ucfirst($record['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Help Section -->
    <div class="card" style="background: #fef3c7; border-left: 4px solid #f59e0b;">
        <div class="card-content">
            <p style="color: #92400e; margin: 0;">
                <strong>ℹ️ Note:</strong> You can view attendance records for all events. 
                To export or manage specific event details, contact your Administrator.
            </p>
        </div>
    </div>
</div>
