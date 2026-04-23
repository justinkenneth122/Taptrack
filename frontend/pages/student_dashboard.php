<!-- Student Dashboard Page -->
<?php
    requireStudent();
    
    $student = $StudentModel->getById($_SESSION['user_id']);
    if (!$student) {
        header('Location: ?page=login');
        exit;
    }

    $events = $EventModel->getActive();
    $attendedEventIds = $AttendanceController->getStudentAttendedEvents($_SESSION['user_id']);
?>

<div class="student-header">
    <div class="flex items-center gap-2">
        <div style="width:32px;height:32px;border-radius:6px;background:var(--primary);display:flex;align-items:center;justify-content:center;">
            <span style="color:var(--primary-foreground);font-size:0.875rem;">▣</span>
        </div>
        <span class="font-bold">Tap<span class="text-gold">track</span></span>
    </div>
    <div class="flex items-center gap-3">
        <span class="text-sm text-muted hide-mobile"><?php echo e($student['first_name'] . ' ' . $student['last_name']); ?></span>
        <form method="POST" style="display:inline;"><input type="hidden" name="action" value="logout"><button type="submit" class="btn btn-ghost btn-sm">⬅ Logout</button></form>
    </div>
</div>

<main style="max-width:48rem;margin:0 auto;padding:1.5rem;" class="space-y-6">
    <div>
        <h2 style="font-size:1.5rem;" class="font-bold">Welcome, <?php echo e($student['first_name']); ?>!</h2>
        <p class="text-sm text-muted"><?php echo e($student['email']); ?> · <?php echo e($student['course']); ?> · <?php echo e($student['year_level']); ?></p>
    </div>


    <div class="space-y-3">
        <h3 style="font-size:1.125rem;" class="font-bold">Upcoming Events</h3>
        <?php foreach ($events as $evt):
            $isAttended = in_array($evt['id'], $attendedEventIds);
        ?>
        <div class="card" style="cursor:pointer;" onclick="toggleQR('<?php echo e($evt['id']); ?>')">
            <div class="card-header">
                <div class="flex justify-between" style="align-items:flex-start;">
                    <div>
                        <div class="card-title"><?php echo e($evt['name']); ?></div>
                        <p class="card-desc"><?php echo e($evt['description'] ?? ''); ?></p>
                    </div>
                    <?php if ($isAttended): ?><span class="badge badge-success">✓ Attended</span><?php endif; ?>
                </div>
            </div>
            <div class="card-content" style="padding-top:0;">
                <div class="flex gap-4 text-xs text-muted">
                    <span>📅 <?php echo e($evt['date']); ?></span>
                    <span>📍 <?php echo e($evt['location']); ?></span>
                </div>
                <div id="qr-<?php echo e($evt['id']); ?>" style="display:none;" class="qr-preview mt-4">
                    <p class="text-sm font-medium">Your QR Code for this event</p>
                    <div class="qr-box"><canvas id="qr-canvas-<?php echo e($evt['id']); ?>"></canvas></div>
                    <p class="text-xs text-muted text-center">Show this QR code to the event organizer to record your attendance.</p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($events)): ?>
            <div class="card"><div class="card-content py-12 text-center text-muted"><p style="font-size:3rem;opacity:0.3;margin-bottom:0.5rem;">📅</p><p>No upcoming events.</p></div></div>
        <?php endif; ?>
    </div>
</main>
