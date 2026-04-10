<?php
/**
 * Admin Archived Events
 * 
 * For Organizers: Read-only view of archived events
 * For Admins: Full access with restore functionality (if needed)
 */

// Check if user is organizer - archived events are read-only for all
$isOrganizer = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'organizer';

$events = $pdo->query("SELECT * FROM events WHERE archived = 1 ORDER BY date DESC")->fetchAll();

// Get unique programs and year levels
$stmt = $pdo->query("SELECT DISTINCT TRIM(course) as program FROM students WHERE course IS NOT NULL AND course != '' ORDER BY program");
$programs = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->query("SELECT DISTINCT year_level FROM students WHERE year_level IS NOT NULL AND year_level != '' ORDER BY year_level");
$yearLevels = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<div class="space-y-6 max-w-6xl">
    <h2 style="font-size:1.5rem;" class="font-bold">Archived Events</h2>
    <p class="text-sm text-muted">Past events and their attendance history are preserved here.</p>

    <!-- Event Search Filter -->
    <div style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap;">
        <div style="flex:1; min-width:280px; position:relative;">
            <input type="text" id="eventSearchInput" class="input" placeholder="Search events by name, location, or date..." style="padding-left:2.5rem; width:100%; border-radius:0.5rem; font-size:0.95rem;">
            <span style="position:absolute; left:0.85rem; top:50%; transform:translateY(-50%); color:var(--muted-foreground); font-size:1.1rem; pointer-events:none;">🔍</span>
        </div>
        <button type="button" onclick="document.getElementById('eventSearchInput').value = ''; filterArchivedEvents();" class="btn btn-outline" style="white-space:nowrap; padding:0.5rem 1.25rem;">Clear Search</button>
    </div>

    <div id="noEventsFound" class="card" style="display:none;">
        <div class="card-content py-12 text-center text-muted">
            <p style="font-size:3rem;opacity:0.3;">🔍</p>
            <p>No events match your search.</p>
        </div>
    </div>

    <?php if (empty($events)): ?>
        <div class="card"><div class="card-content py-12 text-center text-muted"><p style="font-size:3rem;opacity:0.3;">📦</p><p>No archived events yet.</p></div></div>
    <?php else: ?>
        <div class="space-y-3" id="eventsContainer">
        <?php foreach ($events as $evt):
            $stmt = $pdo->prepare("SELECT a.*, s.first_name, s.last_name, s.student_number, s.course, s.year_level FROM attendance a JOIN students s ON a.student_id = s.id WHERE a.event_id = ? ORDER BY a.scanned_at");
            $stmt->execute([$evt['id']]);
            $attendees = $stmt->fetchAll();
        ?>
            <div class="card archived-event" data-event-name="<?= strtolower(e($evt['name'])) ?>" data-event-location="<?= strtolower(e($evt['location'])) ?>" data-event-date="<?= e($evt['date']) ?>">
                <div class="card-header collapsible-header" onclick="toggleCollapsible('arch-<?= e($evt['id']) ?>')">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <span id="arrow-arch-<?= e($evt['id']) ?>">▸</span>
                            <div>
                                <div class="card-title"><?= e($evt['name']) ?></div>
                                <p class="card-desc flex gap-3 mt-1">
                                    <span>📅 <?= e($evt['date']) ?></span>
                                    <span>📍 <?= e($evt['location']) ?></span>
                                </p>
                            </div>
                        </div>
                        <span class="badge badge-secondary">👥 <?= count($attendees) ?> attended</span>
                    </div>
                </div>
                <div class="collapsible-content" id="arch-<?= e($evt['id']) ?>">
                    <div class="card-content" style="padding-top:0;">
                        <!-- Filters Section - Horizontal Layout -->
                        <div style="display:flex; gap:2rem; align-items:flex-start; flex-wrap:wrap; padding-top:1rem; border-top:1px solid var(--border); margin-bottom:1.5rem;">
                            <div style="flex:1; min-width:250px;">
                                <label class="text-xs font-semibold text-muted uppercase">Search Student</label>
                                <input type="text" class="input mt-2 event-search" placeholder="Name, email, or ID..." style="width:100%;" data-event-id="<?= e($evt['id']) ?>">
                            </div>
                            <div style="flex:1; min-width:200px;">
                                <label class="text-xs font-semibold text-muted uppercase">Year Level</label>
                                <select class="input mt-2 event-year-filter" style="width:100%;" data-event-id="<?= e($evt['id']) ?>">
                                    <option value="">All</option>
                                    <?php foreach ($yearLevels as $yl): ?>
                                        <option value="<?= e($yl) ?>"><?= e($yl) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="flex:1; min-width:200px;">
                                <label class="text-xs font-semibold text-muted uppercase">Program</label>
                                <select class="input mt-2 event-program-filter" style="width:100%;" data-event-id="<?= e($evt['id']) ?>">
                                    <option value="">All</option>
                                    <?php foreach ($programs as $prog): ?>
                                        <option value="<?= e($prog) ?>"><?= e($prog) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <?php if (empty($attendees)): ?>
                            <p class="text-sm text-muted py-4 text-center">No attendance records for this event.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead><tr><th>#</th><th>Student Name</th><th>Student Number</th><th class="hide-mobile">Course</th><th class="hide-mobile">Year</th><th>Scanned At</th></tr></thead>
                                <tbody id="tableBody-<?= e($evt['id']) ?>">
                                <?php foreach ($attendees as $i => $r): ?>
                                    <tr class="attendance-row-arch" data-event-id="<?= e($evt['id']) ?>" data-name="<?= strtolower(e($r['first_name'] . ' ' . $r['last_name'])) ?>" data-email="<?= strtolower(e($r['student_number'])) ?>" data-year="<?= strtolower(e($r['year_level'])) ?>" data-program="<?= strtolower(e($r['course'])) ?>">
                                        <td class="font-medium"><?= $i + 1 ?></td>
                                        <td><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
                                        <td class="font-mono text-sm"><?= e($r['student_number']) ?></td>
                                        <td class="hide-mobile"><?= e($r['course']) ?></td>
                                        <td class="hide-mobile"><?= e($r['year_level']) ?></td>
                                        <td class="text-muted text-sm"><?= date('M j, Y g:i A', strtotime($r['scanned_at'])) ?></td>
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

<!-- JavaScript for Event Filtering -->
<script>
    // Event search input listener
    document.addEventListener('DOMContentLoaded', function() {
        const eventSearchInput = document.getElementById('eventSearchInput');
        if (eventSearchInput) {
            eventSearchInput.addEventListener('keyup', filterArchivedEvents);
            eventSearchInput.addEventListener('change', filterArchivedEvents);
            eventSearchInput.addEventListener('input', filterArchivedEvents);
        }
        
        // Per-event filter listeners
        document.querySelectorAll('.event-search, .event-year-filter, .event-program-filter').forEach(input => {
            input.addEventListener('keyup', applyEventFilters);
            input.addEventListener('change', applyEventFilters);
        });
    });

    // Global event search filter
    function filterArchivedEvents() {
        const searchTerm = document.getElementById('eventSearchInput').value.toLowerCase();
        const eventCards = document.querySelectorAll('.archived-event');
        let visibleCount = 0;

        eventCards.forEach(card => {
            const name = card.getAttribute('data-event-name') || '';
            const location = card.getAttribute('data-event-location') || '';
            const date = card.getAttribute('data-event-date') || '';

            const matchesSearch = !searchTerm || 
                                name.includes(searchTerm) || 
                                location.includes(searchTerm) || 
                                date.includes(searchTerm);

            if (matchesSearch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Show/hide "no events found" message
        const noEventsEl = document.getElementById('noEventsFound');
        if (noEventsEl) {
            noEventsEl.style.display = (searchTerm && visibleCount === 0) ? 'block' : 'none';
        }
    }

    // Per-event filtering
    function applyEventFilters(e) {
        const eventId = e.target.getAttribute('data-event-id');
        const searchTerm = document.querySelector(`.event-search[data-event-id="${eventId}"]`).value.toLowerCase();
        const yearLevel = document.querySelector(`.event-year-filter[data-event-id="${eventId}"]`).value.toLowerCase();
        const program = document.querySelector(`.event-program-filter[data-event-id="${eventId}"]`).value.toLowerCase();
        
        const tableBody = document.getElementById(`tableBody-${eventId}`);
        if (!tableBody) return;
        
        const rows = tableBody.querySelectorAll('.attendance-row-arch');
        
        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            const email = row.getAttribute('data-email');
            const year = row.getAttribute('data-year');
            const prog = row.getAttribute('data-program');
            
            const matchesSearch = !searchTerm || name.includes(searchTerm) || email.includes(searchTerm);
            const matchesYear = !yearLevel || year === yearLevel;
            const matchesProgram = !program || prog === program;
            
            row.style.display = (matchesSearch && matchesYear && matchesProgram) ? '' : 'none';
        });
    }
</script>
