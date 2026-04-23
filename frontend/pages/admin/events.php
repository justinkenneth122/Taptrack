<!-- Admin Events Management -->
<?php
    $events = $EventModel->getActive();
?>

<div class="space-y-6 max-w-4xl">
    <div class="flex justify-between items-center">
        <h2 style="font-size:1.5rem;" class="font-bold">Events Management</h2>
        <button class="btn btn-primary" onclick="openModal('add-event-modal')">➕ Add Event</button>
    </div>

    <?php if (empty($events)): ?>
        <div class="card">
            <div class="card-content py-12 text-center text-muted">
                <p style="font-size:3rem;opacity:0.3;margin-bottom:0.5rem;">📅</p>
                <p>No active events. Click "Add Event" to create one.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Active Events (<?php echo count($events); ?>)</div>
                <p class="card-desc">These events are visible to students.</p>
            </div>
            <div class="card-content">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th class="hide-mobile">Description</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($events as $evt): ?>
                        <tr>
                            <td class="font-medium"><?php echo e($evt['name']); ?></td>
                            <td class="text-sm">📅 <?php echo e($evt['date']); ?></td>
                            <td class="text-sm">📍 <?php echo e($evt['location']); ?></td>
                            <td class="text-muted text-sm truncate hide-mobile" style="max-width:200px;"><?php echo e($evt['description'] ?? '—'); ?></td>
                            <td class="text-right">
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Archive &quot;<?php echo e($evt['name']); ?>&quot;?')">
                                    <input type="hidden" name="action" value="archive_event">
                                    <input type="hidden" name="event_id" value="<?php echo e($evt['id']); ?>">
                                    <button type="submit" class="btn btn-outline btn-sm">📦 Archive</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Add Event Modal -->
<div class="modal-overlay" id="add-event-modal">
    <div class="modal">
        <h3>Create New Event</h3>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="add_event">
            <div>
                <label class="label">Event Name</label>
                <input class="input" name="name" placeholder="e.g. FEU Tech Week" required>
            </div>
            <div>
                <label class="label">Date</label>
                <input class="input" type="date" name="date" required>
            </div>
            <div>
                <label class="label">Location</label>
                <input class="input" name="location" placeholder="e.g. Auditorium" required>
            </div>
            <div>
                <label class="label">Description (optional)</label>
                <textarea class="textarea" name="description" placeholder="Event details..."></textarea>
            </div>
            <div class="flex gap-2 justify-between" style="margin-top:1rem;">
                <button type="button" class="btn btn-outline" onclick="closeModal('add-event-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Event</button>
            </div>
        </form>
    </div>
</div>
