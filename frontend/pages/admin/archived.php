<!-- Admin Archived Events -->
<?php
    $events = $EventModel->getArchived();
    $search = $_GET['search'] ?? '';

    if ($search) {
        $events = array_filter($events, function($evt) use ($search) {
            $s = strtolower($search);
            return strpos(strtolower($evt['name']), $s) !== false ||
                   strpos(strtolower($evt['location']), $s) !== false ||
                   strpos($evt['date'], $search) !== false;
        });
    }
?>

<div class="space-y-6 max-w-4xl">
    <h2 style="font-size:1.5rem;" class="font-bold">Archived Events</h2>
    <p class="text-sm text-muted">Past events and their attendance history are preserved here.</p>

    <form method="GET" style="max-width:24rem;position:relative;">
        <input type="hidden" name="page" value="admin_archived">
        <input class="input" name="search" placeholder="Search by name, location, or date..." value="<?php echo e($search); ?>" style="padding-left:2.25rem;">
        <span style="position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);color:var(--muted-foreground);">🔍</span>
    </form>

    <?php if (empty($events)): ?>
        <div class="card">
            <div class="card-content py-12 text-center text-muted">
                <p style="font-size:3rem;opacity:0.3;margin-bottom:0.5rem;">📦</p>
                <p><?php echo $search ? 'No events match your search.' : 'No archived events yet.'; ?></p>
            </div>
        </div>
    <?php else: ?>
        <div class="space-y-3">
        <?php foreach ($events as $evt):
            $attendees = $AttendanceController->getByEvent($evt['id']);
        ?>
            <div class="card">
                <div class="card-header collapsible-header" onclick="toggleCollapsible('arch-<?php echo e($evt['id']); ?>')">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <span id="arrow-arch-<?php echo e($evt['id']); ?>">▸</span>
                            <div>
                                <div class="card-title"><?php echo e($evt['name']); ?></div>
                                <p class="card-desc flex gap-3 mt-1">
                                    <span>📅 <?php echo e($evt['date']); ?></span>
                                    <span>📍 <?php echo e($evt['location']); ?></span>
                                </p>
                            </div>
                        </div>
                        <span class="badge badge-secondary">👥 <?php echo count($attendees); ?> attended</span>
                    </div>
                </div>
                <div class="collapsible-content" id="arch-<?php echo e($evt['id']); ?>">
                    <div class="card-content" style="padding-top:0;">
                        <?php if (empty($attendees)): ?>
                            <p class="text-sm text-muted py-4 text-center">No attendance records for this event.</p>
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
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
