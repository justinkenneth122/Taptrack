<?php
/**
 * Admin Attendance Records - Enhanced with Comprehensive Filtering
 */

// Fetch all active events
$events = $pdo->query("SELECT id, name, date FROM events WHERE archived = 0 ORDER BY date DESC")->fetchAll();

// Fetch all unique programs
$programs = $pdo->query("SELECT DISTINCT course FROM students WHERE course IS NOT NULL AND course != '' ORDER BY course")->fetchAll();

// Fetch all unique year levels
$yearLevels = $pdo->query("SELECT DISTINCT year_level FROM students WHERE year_level IS NOT NULL AND year_level != '' ORDER BY year_level")->fetchAll();

// Check if face_verified column exists
$columnCheckResult = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'attendance' AND COLUMN_NAME = 'face_verified'");
$hasFaceVerified = $columnCheckResult->rowCount() > 0;

// If column doesn't exist, try to create it
if (!$hasFaceVerified) {
    try {
        $pdo->exec("ALTER TABLE attendance ADD COLUMN face_verified TINYINT(1) DEFAULT 0 AFTER scanned_at");
        $hasFaceVerified = true;
    } catch (Exception $e) {
        // Column creation failed, will use without it
        $hasFaceVerified = false;
    }
}

$selectedEvent = $_GET['event'] ?? '';
$selectedProgram = $_GET['program'] ?? 'ALL';
$selectedYearLevel = $_GET['year_level'] ?? 'ALL';
$searchTerm = $_GET['search'] ?? '';

$attendees = [];
$eventName = '';
$totalAttendees = 0;

if ($selectedEvent) {
    // Build dynamic query based on filters
    // Conditionally select face_verified column only if it exists
    $faceVerifiedColumn = $hasFaceVerified ? 'a.face_verified' : '0 as face_verified';
    
    $query = "SELECT 
                a.id, 
                a.student_id, 
                a.event_id, 
                a.scanned_at, 
                $faceVerifiedColumn,
                s.first_name, 
                s.last_name, 
                s.student_number, 
                s.email,
                s.course, 
                s.year_level
              FROM attendance a 
              JOIN students s ON a.student_id = s.id 
              WHERE a.event_id = ?";
    
    $params = [$selectedEvent];
    
    // Add program filter
    if ($selectedProgram !== 'ALL') {
        $query .= " AND s.course = ?";
        $params[] = $selectedProgram;
    }
    
    // Add year level filter
    if ($selectedYearLevel !== 'ALL') {
        $query .= " AND s.year_level = ?";
        $params[] = $selectedYearLevel;
    }
    
    // Add search filter (name, email, or student number)
    if ($searchTerm) {
        $query .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.email LIKE ? OR s.student_number LIKE ?)";
        $searchParam = "%" . $searchTerm . "%";
        array_push($params, $searchParam, $searchParam, $searchParam, $searchParam);
    }
    
    $query .= " ORDER BY a.scanned_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $attendees = $stmt->fetchAll();
    $totalAttendees = count($attendees);
    
    // Get event name
    $stmt2 = $pdo->prepare("SELECT name, date FROM events WHERE id = ?");
    $stmt2->execute([$selectedEvent]);
    $eventData = $stmt2->fetch();
    $eventName = $eventData['name'] ?? '';
}
?>
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex justify-between items-center">
        <h2 style="font-size:1.5rem;" class="font-bold">📋 Attendance Records</h2>
        <?php if ($selectedEvent): ?>
            <span class="badge badge-lg badge-primary">
                👥 <strong id="attendanceCount"><?= $totalAttendees ?></strong> Total Attendees
            </span>
        <?php endif; ?>
    </div>

    <!-- Filter Section -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">🔍 Filter & Search</div>
        </div>
        <div class="card-content">
            <!-- Event Filter -->
            <div style="margin-bottom:1rem;">
                <label class="label required">📅 Event <span style="color:#999;">(Required)</span></label>
                <select id="eventFilter" class="select" style="width: 100%; max-width: 100%;" onchange="applyFilters()">
                    <option value="">-- Select an Event --</option>
                    <?php foreach ($events as $ev): ?>
                        <option value="<?= e($ev['id']) ?>" <?= $selectedEvent == $ev['id'] ? 'selected' : '' ?>>
                            <?= e($ev['name']) ?> (<?= date('M j, Y', strtotime($ev['date'])) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Horizontal Filters: Search, Program, Year Level -->
            <div style="display:flex; gap:2rem; align-items:flex-start; flex-wrap:wrap;">
                <!-- Search Student -->
                <div style="flex:1; min-width:250px;">
                    <label class="text-xs font-semibold text-muted uppercase">Search Student</label>
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="Name, email, or ID..." 
                        class="input mt-2" 
                        style="width: 100%;"
                        value="<?= e($searchTerm) ?>"
                        onkeyup="clientSideFilter()"
                    >
                </div>

                <!-- Program Filter -->
                <div style="flex:1; min-width:200px;">
                    <label class="text-xs font-semibold text-muted uppercase">Program</label>
                    <select id="programFilter" class="input mt-2" style="width: 100%;" onchange="applyFilters()">
                        <option value="ALL">All</option>
                        <?php foreach ($programs as $prog): ?>
                            <option value="<?= e($prog['course']) ?>" <?= $selectedProgram == $prog['course'] ? 'selected' : '' ?>>
                                <?= e($prog['course']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Year Level Filter -->
                <div style="flex:1; min-width:200px;">
                    <label class="text-xs font-semibold text-muted uppercase">Year Level</label>
                    <select id="yearLevelFilter" class="input mt-2" style="width: 100%;" onchange="applyFilters()">
                        <option value="ALL">All</option>
                        <?php foreach ($yearLevels as $year): ?>
                            <option value="<?= e($year['year_level']) ?>" <?= $selectedYearLevel == $year['year_level'] ? 'selected' : '' ?>>
                                <?= e($year['year_level']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Reset Button -->
                <div style="flex-shrink:0; align-self:flex-end; margin-bottom:0;">
                    <button class="btn btn-sm btn-outline" onclick="resetFilters()" style="white-space:nowrap;">🔄 Reset</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <?php if ($selectedEvent): ?>
        <div class="card">
            <div class="card-header flex justify-between items-center" style="flex-direction: row; justify-content: space-between;">
                <div class="card-title">
                    📊 Attendance Results for <strong><?= e($eventName) ?></strong>
                </div>
            </div>
            <div class="card-content">
                <?php if (empty($attendees)): ?>
                    <div class="alert alert-info" style="background-color: #f0f7ff; border-left: 4px solid #0066cc; padding: 1rem; border-radius: 4px; color: #003d99;">
                        ℹ️ No attendance records found for the selected filters.
                    </div>
                <?php else: ?>
                    <!-- Stats Bar -->
                    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="stat-box" style="background: #f5f5f5; padding: 1rem; border-radius: 8px; border-left: 4px solid #0066cc;">
                            <div class="stat-label" style="font-size: 0.85rem; color: #666; margin-bottom: 0.25rem;">Total Attendees</div>
                            <div class="stat-value" style="font-size: 1.75rem; font-weight: bold; color: #0066cc;">
                                <span id="totalCount"><?= $totalAttendees ?></span>
                            </div>
                        </div>
                        
                        <div class="stat-box" style="background: #f5f5f5; padding: 1rem; border-radius: 8px; border-left: 4px solid #28a745;">
                            <div class="stat-label" style="font-size: 0.85rem; color: #666; margin-bottom: 0.25rem;">Verified (Face)</div>
                            <div class="stat-value" style="font-size: 1.75rem; font-weight: bold; color: #28a745;">
                                <span id="verifiedCount">
                                    <?php echo count(array_filter($attendees, fn($a) => $a['face_verified'])); ?>
                                </span>
                            </div>
                        </div>

                        <div class="stat-box" style="background: #f5f5f5; padding: 1rem; border-radius: 8px; border-left: 4px solid #ffc107;">
                            <div class="stat-label" style="font-size: 0.85rem; color: #666; margin-bottom: 0.25rem;">Filtered Results</div>
                            <div class="stat-value" style="font-size: 1.75rem; font-weight: bold; color: #ff9800;">
                                <span id="filteredCount"><?= $totalAttendees ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Table -->
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table" id="attendanceTable" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #f9f9f9; border-bottom: 2px solid #ddd;">
                                    <th style="padding: 0.75rem; text-align: left; font-weight: 600;">#</th>
                                    <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Student Name</th>
                                    <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Student #</th>
                                    <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Email</th>
                                    <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Program</th>
                                    <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Year Level</th>
                                    <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Time Scanned</th>
                                    <th style="padding: 0.75rem; text-align: center; font-weight: 600;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php foreach ($attendees as $i => $record): ?>
                                    <tr class="attendance-row" style="border-bottom: 1px solid #eee;" data-name="<?= strtolower(e($record['first_name'] . ' ' . $record['last_name'])) ?>" data-email="<?= strtolower(e($record['email'])) ?>" data-number="<?= strtolower(e($record['student_number'])) ?>" data-program="<?= strtolower(e($record['course'])) ?>" data-year="<?= strtolower(e($record['year_level'])) ?>">
                                        <td style="padding: 0.75rem;"><?= $i + 1 ?></td>
                                        <td style="padding: 0.75rem; font-weight: 500;"><?= e($record['first_name'] . ' ' . $record['last_name']) ?></td>
                                        <td style="padding: 0.75rem; font-family: monospace; font-size: 0.9rem;"><?= e($record['student_number']) ?></td>
                                        <td style="padding: 0.75rem; font-size: 0.9rem; color: #0066cc;"><?= e($record['email']) ?></td>
                                        <td style="padding: 0.75rem;"><?= e($record['course']) ?></td>
                                        <td style="padding: 0.75rem;"><?= e($record['year_level']) ?></td>
                                        <td style="padding: 0.75rem; font-size: 0.85rem; color: #666;"><?= date('M j, Y · g:i A', strtotime($record['scanned_at'])) ?></td>
                                        <td style="padding: 0.75rem; text-align: center;">
                                            <?php if ($record['face_verified']): ?>
                                                <span class="badge" style="background-color: #28a745; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.8rem;">✓ Verified</span>
                                            <?php else: ?>
                                                <span class="badge" style="background-color: #007bff; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.8rem;">QR Scanned</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- No Results Message -->
                    <div id="noResults" class="alert alert-warning" style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 1rem; border-radius: 4px; color: #856404; margin-top: 1rem; display: none;">
                        ⚠️ No records match your search criteria. Try adjusting your filters.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-content py-16 text-center">
                <p style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;">📋</p>
                <p class="text-muted">Select an event from the filter section to view attendance records.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- JavaScript for Dynamic Filtering -->
<script>
    let filterTimeout;
    
    function applyFilters() {
        const eventId = document.getElementById('eventFilter').value;
        const programFilter = document.getElementById('programFilter').value;
        const yearLevelFilter = document.getElementById('yearLevelFilter').value;

        // If no event is selected, redirect to show event selection
        if (!eventId) {
            window.location.href = '?page=admin_attendance';
            return;
        }

        // Build query parameters
        const params = new URLSearchParams({
            page: 'admin_attendance',
            event: eventId,
            ...(programFilter !== 'ALL' && { program: programFilter }),
            ...(yearLevelFilter !== 'ALL' && { year_level: yearLevelFilter })
        });

        // Navigate with filters applied (NOTE: Search is now client-side only)
        window.location.href = '?' + params.toString();
    }

    function resetFilters() {
        document.getElementById('eventFilter').value = '';
        document.getElementById('searchInput').value = '';
        document.getElementById('programFilter').value = 'ALL';
        document.getElementById('yearLevelFilter').value = 'ALL';
        window.location.href = '?page=admin_attendance';
    }

    // Client-side filtering for real-time search (without page reload)
    // This prevents page reloads on every keystroke, maintaining focus
    function clientSideFilter() {
        clearTimeout(filterTimeout);
        
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#tableBody .attendance-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const email = row.getAttribute('data-email') || '';
            const number = row.getAttribute('data-number') || '';
            const program = row.getAttribute('data-program') || '';
            const year = row.getAttribute('data-year') || '';

            // Match search term against all searchable fields
            const matchesSearch = !searchTerm || 
                                name.includes(searchTerm) || 
                                email.includes(searchTerm) || 
                                number.includes(searchTerm) ||
                                program.includes(searchTerm) ||
                                year.includes(searchTerm);

            if (matchesSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update visible count
        const filteredCountEl = document.getElementById('filteredCount');
        if (filteredCountEl) {
            filteredCountEl.textContent = visibleCount;
        }
        
        // Show/hide "no results" message
        const noResultsEl = document.getElementById('noResults');
        if (noResultsEl) {
            noResultsEl.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    // Ensure search input has proper event listeners
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput && !searchInput.dataset.listenerAdded) {
            searchInput.addEventListener('keyup', clientSideFilter);
            searchInput.addEventListener('change', clientSideFilter);
            searchInput.dataset.listenerAdded = 'true';
        }
    });
</script>
