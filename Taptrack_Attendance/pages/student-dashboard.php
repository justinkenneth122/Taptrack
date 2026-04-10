<?php
/**
 * Student Dashboard Page
 */

$student_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();
if (!$student) { header('Location: ?page=login'); exit; }

// ============ FIXED: Filter events by student program ============
// Get student's program (stored in 'course' field)
$student_program = trim($student['course'] ?? '');

// BACKEND FILTERING: Only fetch events where student is eligible
// Include events where:
// 1. Event.programs contains "ALL" (open to all)
// 2. Event.programs contains student's program
$stmt = $pdo->prepare("
    SELECT * FROM events 
    WHERE archived = 0 
    AND (
        JSON_CONTAINS(programs, JSON_QUOTE('ALL'), '$')
        OR JSON_CONTAINS(programs, JSON_QUOTE(?), '$')
    )
    ORDER BY date
");
$stmt->execute([$student_program]);
$events = $stmt->fetchAll();
$stmt = $pdo->prepare("SELECT event_id FROM attendance WHERE student_id = ?");
$stmt->execute([$student_id]);
$attended = array_column($stmt->fetchAll(), 'event_id');
?>
<div class="student-header">
    <div class="flex items-center gap-2">
        <div style="width:32px;height:32px;border-radius:6px;background:var(--primary);display:flex;align-items:center;justify-content:center;">
            <span style="color:var(--primary-foreground);font-size:0.875rem;">▣</span>
        </div>
        <span class="font-bold">Tap<span class="text-gold">track</span></span>
    </div>
    <div class="flex items-center gap-3">
        <span class="text-sm text-muted hide-mobile"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></span>
        <form method="POST" style="display:inline;"><input type="hidden" name="action" value="logout"><button type="submit" class="btn btn-ghost btn-sm">⬅ Logout</button></form>
    </div>
</div>

<main style="max-width:48rem;margin:0 auto;padding:1.5rem;" class="space-y-6">
    <div>
        <h2 style="font-size:1.5rem;" class="font-bold">Welcome, <?= e($student['first_name']) ?>!</h2>
        <p class="text-sm text-muted"><?= e($student['email']) ?> · <?= e($student['course']) ?> · <?= e($student['year_level']) ?></p>
    </div>

    <?php if (empty($student['face_descriptor'])): ?>
    <div class="card" style="border-color:hsl(45 90% 55% / 0.5);background:hsl(45 90% 55% / 0.05);">
        <div class="card-header">
            <div class="card-title flex items-center gap-2">⚠️ Face Not Registered</div>
            <p class="card-desc">You haven't completed face registration yet. Face verification may be required during events.</p>
        </div>
    </div>
    <?php endif; ?>

    <div class="space-y-3">
        <h3 style="font-size:1.125rem;" class="font-bold">Upcoming Events</h3>
        <?php foreach ($events as $evt):
            $isAttended = in_array($evt['id'], $attended);
        ?>
        <div class="card" style="cursor:pointer;" onclick="toggleQR('<?= e($evt['id']) ?>', '<?= e($student_id) ?>')">
            <div class="card-header">
                <div class="flex justify-between" style="align-items:flex-start;">
                    <div>
                        <div class="card-title"><?= e($evt['name']) ?></div>
                        <p class="card-desc"><?= e($evt['description'] ?? '') ?></p>
                    </div>
                    <?php if ($isAttended): ?><span class="badge badge-success">✓ Attended</span><?php endif; ?>
                </div>
            </div>
            <div class="card-content" style="padding-top:0;">
                <div class="flex gap-4 text-xs text-muted">
                    <span>📅 <?= e($evt['date']) ?></span>
                    <span>📍 <?= e($evt['location']) ?></span>
                </div>
                <div id="qr-<?= e($evt['id']) ?>" class="qr-preview mt-4" style="display:none;">
                    <p class="text-sm font-medium">Your QR Code for this event</p>
                    <div class="qr-box" style="display:flex;align-items:center;justify-content:center;min-height:220px;padding:1rem;border:1px solid var(--border);border-radius:4px;">
                        <canvas id="qr-canvas-<?= e($evt['id']) ?>" width="200" height="200" style="border:1px solid #ccc;border-radius:4px;"></canvas>
                    </div>
                    <div id="qr-error-<?= e($evt['id']) ?>" class="text-xs text-destructive" style="display:none;margin-top:0.5rem;"></div>
                    <p class="text-xs text-muted text-center">Show this QR code to the event organizer to record your attendance.</p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($events)): ?>
            <div class="card"><div class="card-content py-12 text-center text-muted">No upcoming events.</div></div>
        <?php endif; ?>
    </div>
</main>
