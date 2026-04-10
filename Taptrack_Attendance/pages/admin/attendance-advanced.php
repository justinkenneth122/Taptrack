<?php
/**
 * Admin Attendance Records - Advanced AJAX Version
 * Features real-time filtering without page reloads
 */

$events = $pdo->query("SELECT id, name, date FROM events WHERE archived = 0 ORDER BY date DESC")->fetchAll();
$programs = $pdo->query("SELECT DISTINCT course FROM students WHERE course IS NOT NULL AND course != '' ORDER BY course")->fetchAll();
$yearLevels = $pdo->query("SELECT DISTINCT year_level FROM students WHERE year_level IS NOT NULL AND year_level != '' ORDER BY year_level")->fetchAll();

$selectedEvent = $_GET['event'] ?? '';
$selectedProgram = $_GET['program'] ?? 'ALL';
$selectedYearLevel = $_GET['year_level'] ?? 'ALL';
$searchTerm = $_GET['search'] ?? '';
?>

<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex justify-between items-center">
        <h2 style="font-size:1.5rem;" class="font-bold">📋 Attendance Records - Advanced</h2>
        <span class="badge badge-lg badge-primary" id="attendanceBadge" style="display: none;">
            👥 <strong id="attendanceCount">0</strong> Attendees
        </span>
    </div>

    <!-- Filter Section -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">🔍 Filter & Search</div>
        </div>
        <div class="card-content">
            <div class="space-y-4">
                <!-- Event Filter -->
                <div class="form-group">
                    <label class="label required">📅 Event <span style="color:#999;">(Required)</span></label>
                    <select id="eventFilter" class="select" style="width: 100%;" onchange="handleFilterChange()">
                        <option value="">-- Select an Event --</option>
                        <?php foreach ($events as $ev): ?>
                            <option value="<?= e($ev['id']) ?>" <?= $selectedEvent == $ev['id'] ? 'selected' : '' ?>>
                                <?= e($ev['name']) ?> (<?= date('M j, Y', strtotime($ev['date'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Search Student Bar -->
                <div class="form-group">
                    <label class="label">🔎 Search Student</label>
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="Search by name, email, or student number..." 
                        class="input" 
                        style="width: 100%;"
                        value="<?= e($searchTerm) ?>"
                        onkeyup="debounceFilter()"
                    >
                    <p class="text-xs text-muted mt-1">Results update in real-time as you type</p>
                </div>

                <!-- Program and Year Level Filters -->
                <div class="grid grid-cols-2 gap-4" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="label">🎓 Program</label>
                        <select id="programFilter" class="select" style="width: 100%;" onchange="handleFilterChange()">
                            <option value="ALL">-- All Programs --</option>
                            <?php foreach ($programs as $prog): ?>
                                <option value="<?= e($prog['course']) ?>" <?= $selectedProgram == $prog['course'] ? 'selected' : '' ?>>
                                    <?= e($prog['course']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="label">📚 Year Level</label>
                        <select id="yearLevelFilter" class="select" style="width: 100%;" onchange="handleFilterChange()">
                            <option value="ALL">-- All Years --</option>
                            <?php foreach ($yearLevels as $year): ?>
                                <option value="<?= e($year['year_level']) ?>" <?= $selectedYearLevel == $year['year_level'] ? 'selected' : '' ?>>
                                    <?= e($year['year_level']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Reset Filters -->
                <div class="flex gap-2">
                    <button class="btn btn-sm btn-outline" onclick="resetFilters()">🔄 Reset Filters</button>
                    <button class="btn btn-sm btn-outline" onclick="exportToCSV()">📥 Export CSV</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="alert alert-info" style="display: none; background-color: #f0f7ff; border-left: 4px solid #0066cc; padding: 1rem; border-radius: 4px;">
        ⏳ Filtering records...
    </div>

    <!-- Results Section -->
    <div id="resultsContainer">
        <div class="card">
            <div class="card-content py-16 text-center">
                <p style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;">📋</p>
                <p class="text-muted">Select an event to view attendance records.</p>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Content Template (will be populated by JavaScript) -->
<template id="resultsTemplate">
    <div class="card" id="resultsCard">
        <div class="card-header flex justify-between items-center" style="flex-direction: row; justify-content: space-between;">
            <div class="card-title">📊 Attendance Results</div>
        </div>
        <div class="card-content">
            <!-- Stats Bar -->
            <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                <div class="stat-box" style="background: #f5f5f5; padding: 1rem; border-radius: 8px; border-left: 4px solid #0066cc;">
                    <div class="stat-label" style="font-size: 0.85rem; color: #666;">Total Attendees</div>
                    <div class="stat-value" style="font-size: 1.5rem; font-weight: bold; color: #0066cc;" id="totalAttendeesDisplay">0</div>
                </div>
                
                <div class="stat-box" style="background: #f5f5f5; padding: 1rem; border-radius: 8px; border-left: 4px solid #28a745;">
                    <div class="stat-label" style="font-size: 0.85rem; color: #666;">Verified</div>
                    <div class="stat-value" style="font-size: 1.5rem; font-weight: bold; color: #28a745;" id="verifiedAttendeesDisplay">0</div>
                </div>
            </div>

            <!-- Attendance Table -->
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table" id="attendanceTableAdvanced" style="width: 100%; border-collapse: collapse;">
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
                    <tbody id="attendanceTableBody">
                        <!-- Will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <div id="noResultsMessage" class="alert alert-warning" style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 1rem; border-radius: 4px; color: #856404; margin-top: 1rem; display: none;">
                ⚠️ No records match your search criteria.
            </div>
        </div>
    </div>
</template>

<script>
    let filterTimeout;
    
    function debounceFilter() {
        clearTimeout(filterTimeout);
        document.getElementById('loadingIndicator').style.display = 'block';
        filterTimeout = setTimeout(handleFilterChange, 500);
    }
    
    async function handleFilterChange() {
        const eventId = document.getElementById('eventFilter').value;
        
        if (!eventId) {
            document.getElementById('resultsContainer').innerHTML = `
                <div class="card">
                    <div class="card-content py-16 text-center">
                        <p style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;">📋</p>
                        <p class="text-muted">Select an event to view attendance records.</p>
                    </div>
                </div>
            `;
            document.getElementById('loadingIndicator').style.display = 'none';
            document.getElementById('attendanceBadge').style.display = 'none';
            return;
        }

        const searchTerm = document.getElementById('searchInput').value;
        const program = document.getElementById('programFilter').value;
        const yearLevel = document.getElementById('yearLevelFilter').value;

        try {
            const response = await fetch(`?page=api_attendance&event=${eventId}&search=${encodeURIComponent(searchTerm)}&program=${program}&year_level=${yearLevel}`);
            
            if (!response.ok) throw new Error('Failed to fetch data');
            
            const data = await response.json();
            document.getElementById('loadingIndicator').style.display = 'none';
            
            if (!data.success) {
                document.getElementById('resultsContainer').innerHTML = `
                    <div class="alert alert-error" style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 1rem; border-radius: 4px; color: #721c24;">
                        ❌ Error: ${data.error}
                    </div>
                `;
                return;
            }

            // Render results
            renderResults(data);
            updateURL(eventId, searchTerm, program, yearLevel);
            
        } catch (error) {
            console.error('Error filtering:', error);
            document.getElementById('loadingIndicator').style.display = 'none';
            document.getElementById('resultsContainer').innerHTML = `
                <div class="alert alert-error" style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 1rem; border-radius: 4px; color: #721c24;">
                    ❌ Error loading records. Please try again.
                </div>
            `;
        }
    }

    function renderResults(data) {
        const { records, stats, event } = data;
        const template = document.getElementById('resultsTemplate');
        const clone = template.content.cloneNode(true);

        // Update stats
        clone.querySelector('#totalAttendeesDisplay').textContent = stats.total;
        clone.querySelector('#verifiedAttendeesDisplay').textContent = stats.verified;

        // Populate table
        const tbody = clone.querySelector('#attendanceTableBody');
        tbody.innerHTML = '';

        if (records.length === 0) {
            clone.querySelector('#noResultsMessage').style.display = 'block';
        } else {
            records.forEach((record, index) => {
                const row = document.createElement('tr');
                row.style.borderBottom = '1px solid #eee';
                
                const statusBadge = record.face_verified 
                    ? '<span class="badge" style="background-color: #28a745; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.8rem;">✓ Verified</span>'
                    : '<span class="badge" style="background-color: #007bff; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.8rem;">QR Scanned</span>';
                
                row.innerHTML = `
                    <td style="padding: 0.75rem;">${index + 1}</td>
                    <td style="padding: 0.75rem; font-weight: 500;">${escapeHtml(record.first_name + ' ' + record.last_name)}</td>
                    <td style="padding: 0.75rem; font-family: monospace; font-size: 0.9rem;">${escapeHtml(record.student_number)}</td>
                    <td style="padding: 0.75rem; font-size: 0.9rem; color: #0066cc;">${escapeHtml(record.email)}</td>
                    <td style="padding: 0.75rem;">${escapeHtml(record.course)}</td>
                    <td style="padding: 0.75rem;">${escapeHtml(record.year_level)}</td>
                    <td style="padding: 0.75rem; font-size: 0.85rem; color: #666;">${formatDateTime(record.scanned_at)}</td>
                    <td style="padding: 0.75rem; text-align: center;">${statusBadge}</td>
                `;
                tbody.appendChild(row);
            });
        }

        // Update container
        document.getElementById('resultsContainer').innerHTML = '';
        document.getElementById('resultsContainer').appendChild(clone);
        
        // Update badge
        document.getElementById('attendanceCount').textContent = stats.total;
        document.getElementById('attendanceBadge').style.display = 'flex';
    }

    function updateURL(event, search, program, yearLevel) {
        const params = new URLSearchParams({
            page: 'attendance_advanced',
            event: event,
            ...(search && { search: search }),
            ...(program !== 'ALL' && { program: program }),
            ...(yearLevel !== 'ALL' && { year_level: yearLevel })
        });
        window.history.replaceState({}, '', '?' + params.toString());
    }

    function resetFilters() {
        document.getElementById('eventFilter').value = '';
        document.getElementById('searchInput').value = '';
        document.getElementById('programFilter').value = 'ALL';
        document.getElementById('yearLevelFilter').value = 'ALL';
        document.getElementById('resultsContainer').innerHTML = `
            <div class="card">
                <div class="card-content py-16 text-center">
                    <p style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;">📋</p>
                    <p class="text-muted">Select an event to view attendance records.</p>
                </div>
            </div>
        `;
        document.getElementById('attendanceBadge').style.display = 'none';
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    function formatDateTime(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + 
               ' · ' + 
               date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }

    function exportToCSV() {
        const eventId = document.getElementById('eventFilter').value;
        if (!eventId) {
            alert('Please select an event first');
            return;
        }

        const table = document.getElementById('attendanceTableAdvanced');
        if (!table) {
            alert('No data to export');
            return;
        }

        let csv = 'Name,Student #,Email,Program,Year Level,Time Scanned,Status\n';
        
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length > 0) {
                const name = cells[1].textContent.trim();
                const number = cells[2].textContent.trim();
                const email = cells[3].textContent.trim();
                const program = cells[4].textContent.trim();
                const year = cells[5].textContent.trim();
                const time = cells[6].textContent.trim();
                const status = cells[7].textContent.trim().replace(/(\✓|QR Scanned|Verified)/g, '').trim();
                
                csv += `"${name}","${number}","${email}","${program}","${year}","${time}","${status}"\n`;
            }
        });

        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `attendance_${new Date().getTime()}.csv`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    }

    // Load initial data if event is selected
    window.addEventListener('load', () => {
        const eventId = document.getElementById('eventFilter').value;
        if (eventId) {
            handleFilterChange();
        }
    });
</script>
