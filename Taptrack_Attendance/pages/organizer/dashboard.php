<?php
/**
 * Organizer Dashboard
 * 
 * Shows organizer with:
 * - Events assigned to them
 * - Attendance records for their events
 * - Limited management capabilities
 */

$organizerId = $_SESSION['user_id'] ?? null;
$organizerName = $_SESSION['username'] ?? 'Organizer';

if (!$organizerId) {
    echo '<div class="card"><p class="text-center text-muted">Session expired. Please log in again.</p></div>';
    return;
}

// Get organizer's events (for now, show all events - can be enhanced later to show only assigned ones)
try {
    $stmt = $pdo->query("SELECT id, program_name, event_date, status FROM events ORDER BY event_date DESC LIMIT 10");
    $events = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error fetching events: " . $e->getMessage());
    $events = [];
}

// Get recent attendance records
try {
    $stmt = $pdo->query("
        SELECT a.id, a.student_id, a.event_id, a.scanned_at, a.status, 
               e.program_name, s.first_name, s.last_name
        FROM attendance a
        JOIN events e ON a.event_id = e.id
        JOIN students s ON a.student_id = s.id
        ORDER BY a.scanned_at DESC
        LIMIT 20
    ");
    $attendanceRecords = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error fetching attendance: " . $e->getMessage());
    $attendanceRecords = [];
}

// Get statistics
$eventCount = count($events);
$attendanceCount = count($attendanceRecords);
?>

<div class="space-y-6">
    <!-- Header -->
    <div>
        <h2 style="font-size: 1.875rem;" class="font-bold">👋 Welcome, <?= e($organizerName) ?>!</h2>
        <p class="text-muted" style="margin-top: 0.5rem;">Manage your assigned events and attendance records</p>
    </div>

    <!-- Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
        <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="card-content">
                <p style="font-size: 0.875rem; opacity: 0.9;">Total Events</p>
                <p style="font-size: 2rem; font-weight: bold; margin-top: 0.5rem;"><?= $eventCount ?></p>
            </div>
        </div>

        <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <div class="card-content">
                <p style="font-size: 0.875rem; opacity: 0.9;">Recent Attendance</p>
                <p style="font-size: 2rem; font-weight: bold; margin-top: 0.5rem;"><?= $attendanceCount ?></p>
            </div>
        </div>

        <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
            <div class="card-content">
                <p style="font-size: 0.875rem; opacity: 0.9;">Quick Action</p>
                <p style="margin-top: 0.5rem;">
                    <a href="?page=organizer_attendance" class="btn btn-white" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                        View →
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Upcoming Events -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📅 Upcoming Events</div>
            <p class="card-desc">Events you're managing</p>
        </div>
        <div class="card-content">
            <?php if (empty($events)): ?>
                <p class="text-center text-muted py-8">No events scheduled</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Program</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($events, 0, 5) as $event): ?>
                            <tr>
                                <td class="font-medium"><?= e($event['program_name']) ?></td>
                                <td class="text-sm text-muted"><?= date('M j, Y', strtotime($event['event_date'])) ?></td>
                                <td>
                                    <span class="badge" style="background:<?= $event['status'] === 'active' ? '#dcfce7' : '#f3f4f6' ?>; color:<?= $event['status'] === 'active' ? '#22c55e' : '#6b7280' ?>;">
                                        <?= ucfirst($event['status']) ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="?page=organizer_attendance&event_id=<?= $event['id'] ?>" class="btn btn-sm btn-outline">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Attendance -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📋 Recent Attendance Records</div>
            <p class="card-desc">Latest scanned attendance</p>
        </div>
        <div class="card-content">
            <?php if (empty($attendanceRecords)): ?>
                <p class="text-center text-muted py-8">No attendance records yet</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Event</th>
                            <th>Scanned At</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($attendanceRecords, 0, 10) as $record): ?>
                            <tr>
                                <td class="font-medium"><?= e($record['first_name'] . ' ' . $record['last_name']) ?></td>
                                <td class="text-sm text-muted"><?= e($record['program_name']) ?></td>
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
            <?php endif; ?>
        </div>
    </div>

    <!-- Help Section -->
    <div class="card" style="background: #f0f9ff; border-left: 4px solid #3b82f6;">
        <div class="card-content">
            <p style="color: #1e40af; margin: 0;">
                <strong>💡 Tip:</strong> As an Organizer, you have access to limited features. You can view and manage attendance for your assigned events. 
                Contact your Administrator for additional permissions.
            </p>
        </div>
    </div>
</div>
