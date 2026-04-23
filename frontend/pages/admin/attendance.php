<!-- Admin Attendance Records -->
<?php
    $events = $EventModel->getActive();
    $selectedEvent = $_GET['event'] ?? '';

    $attendees = [];
    $eventName = '';
    if ($selectedEvent) {
        $attendees = $AttendanceController->getByEvent($selectedEvent);
        $event = $EventModel->getById($selectedEvent);
        $eventName = $event['name'] ?? '';
    }
?>

<div class="space-y-6 max-w-3xl">
    <h2 style="font-size:1.5rem;" class="font-bold">Attendance Records</h2>
    <div>
        <label class="label">Filter by Event</label>
        <select class="select" style="max-width:24rem;" onchange="location.href='?page=admin_attendance&event='+this.value">
            <option value="">Select an event...</option>
            <?php foreach ($events as $ev): ?>
                <option value="<?php echo e($ev['id']); ?>" <?php echo $selectedEvent === $ev['id'] ? 'selected' : ''; ?>><?php echo e($ev['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($selectedEvent): ?>
        <div class="card">
            <div class="card-header flex justify-between items-center" style="flex-direction:row;">
                <div class="card-title flex items-center gap-2">📋 <?php echo e($eventName); ?></div>
                <span class="badge badge-secondary">👥 <?php echo count($attendees); ?> attended</span>
            </div>
            <div class="card-content">
                <?php if (empty($attendees)): ?>
                    <p class="text-sm text-muted py-12 text-center">No attendance records yet for this event.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Student Number</th>
                                <th class="hide-mobile">Course</th>
                                <th class="hide-mobile">Year</th>
                                <th>Scanned At</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($attendees as $i => $r): ?>
                            <tr>
                                <td class="font-medium"><?php echo $i + 1; ?></td>
                                <td><?php echo e($r['first_name'] . ' ' . $r['last_name']); ?></td>
                                <td class="font-mono text-sm"><?php echo e($r['student_number']); ?></td>
                                <td class="hide-mobile"><?php echo e($r['course']); ?></td>
                                <td class="hide-mobile"><?php echo e($r['year_level']); ?></td>
                                <td class="text-muted text-sm"><?php echo formatTimestamp($r['scanned_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-content py-12 text-center text-muted">
                <p style="font-size:3rem;opacity:0.3;margin-bottom:0.5rem;">📋</p>
                <p>Select an event to view attendance records.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
