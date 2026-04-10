<?php
/**
 * Admin Events Management
 * 
 * For Organizers: Read-only view of events (cannot create/edit/delete)
 * For Admins: Full event management capabilities
 */

// Check if user is organizer - restrict write operations
$isOrganizer = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'organizer';
$canCreateEvents = !$isOrganizer;

$events = $pdo->query("SELECT * FROM events WHERE archived = 0 ORDER BY date")->fetchAll();

// Fetch distinct programs from registered students
$stmt = $pdo->query("SELECT DISTINCT TRIM(course) as program FROM students WHERE course IS NOT NULL AND course != '' ORDER BY program");
$available_programs = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<div class="space-y-6 max-w-4xl">
    <div class="flex justify-between items-center">
        <h2 style="font-size:1.5rem;" class="font-bold">Events Management</h2>
        <?php if ($canCreateEvents): ?>
            <button class="btn btn-primary" onclick="document.getElementById('add-event-modal').classList.add('open')">➕ Add Event</button>
        <?php else: ?>
            <div class="badge" style="background:#fef3c7; color:#92400e;">📖 View Only</div>
        <?php endif; ?>
    </div>

    <?php if (empty($events)): ?>
        <div class="card"><div class="card-content py-12 text-center text-muted"><p style="font-size:3rem;opacity:0.3;">📅</p><p>No active events. <?php if ($canCreateEvents) echo 'Click "Add Event" to create one.'; ?></p></div></div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Active Events (<?= count($events) ?>)</div>
                <p class="card-desc">These events are visible to students.</p>
            </div>
            <div class="card-content">
                <table class="table">
                    <thead><tr><th>Event Name</th><th>Date</th><th>Location</th><th class="hide-mobile">Programs</th><?php if ($canCreateEvents): ?><th class="text-right">Actions</th><?php endif; ?></tr></thead>
                    <tbody>
                    <?php foreach ($events as $evt): ?>
                        <?php 
                            $programs = json_decode($evt['programs'] ?? '["ALL"]', true);
                            $programsDisplay = (is_array($programs) && in_array('ALL', $programs)) ? 'All Programs' : implode(', ', $programs ?? ['ALL']);
                        ?>
                        <tr>
                            <td class="font-medium"><?= e($evt['name']) ?></td>
                            <td class="text-sm">📅 <?= e($evt['date']) ?></td>
                            <td class="text-sm">📍 <?= e($evt['location']) ?></td>
                            <td class="text-muted text-sm hide-mobile"><?= e($programsDisplay) ?></td>
                            <?php if ($canCreateEvents): ?>
                            <td class="text-right">
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Archive &quot;<?= e($evt['name']) ?>&quot;? It will be moved to the archive section.')">
                                    <input type="hidden" name="action" value="archive_event">
                                    <input type="hidden" name="event_id" value="<?= e($evt['id']) ?>">
                                    <button type="submit" class="btn btn-outline btn-sm">📦 Archive</button>
                                </form>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Add Event Modal - Admin Only -->
<?php if ($canCreateEvents): ?>
<div class="modal-overlay" id="add-event-modal">
    <div class="modal">
        <h3>Create New Event</h3>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="add_event">
            <div><label class="label">Event Name</label><input class="input" name="name" placeholder="e.g. FEU Tech Week" required></div>
            <div><label class="label">Date</label><input class="input" type="date" name="date" required></div>
            <div><label class="label">Location</label><input class="input" name="location" placeholder="e.g. Auditorium" required></div>
            <div><label class="label">Description (optional)</label><textarea class="textarea" name="description" placeholder="Event details..."></textarea></div>
            
            <!-- MODIFIED: Program Selection -->
            <div>
                <label class="label">📚 Eligible Programs</label>
                <div style="display:flex;flex-direction:column;gap:0.5rem;">
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                        <input type="radio" name="program_restriction" value="ALL" checked onchange="document.getElementById('program-list').style.display='none'">
                        <span>All Programs (No Restriction)</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                        <input type="radio" name="program_restriction" value="SPECIFIC" onchange="document.getElementById('program-list').style.display='flex'">
                        <span>Specific Programs Only</span>
                    </label>
                </div>
                
                <div id="program-list" style="display:none;flex-direction:column;gap:0.5rem;padding-top:1rem;border-top:1px solid var(--border);">
                    <?php if (empty($available_programs)): ?>
                        <p style="color:#999;font-size:0.875rem;">No programs found. Students need to register first.</p>
                    <?php else: ?>
                        <?php foreach ($available_programs as $program): ?>
                            <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                <input type="checkbox" name="programs[]" value="<?= e($program) ?>"> 
                                <?= e($program) ?>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="flex gap-2 justify-between" style="margin-top:1rem;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('add-event-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Event</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
