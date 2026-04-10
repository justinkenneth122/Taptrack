<?php
/**
 * Admin QR Generator
 */

$events = $pdo->query("SELECT * FROM events WHERE archived = 0 ORDER BY date")->fetchAll();
$students = $pdo->query("SELECT id, first_name, last_name, student_number FROM students ORDER BY created_at")->fetchAll();
?>
<div class="space-y-6 max-w-lg">
    <h2 style="font-size:1.5rem;" class="font-bold">QR Generator</h2>
    <div class="card">
        <div class="card-header"><div class="card-title">Generate Student QR Code</div></div>
        <div class="card-content space-y-4">
            <div>
                <label class="label">Select Student</label>
                <select class="select" id="gen-student" onchange="generateQR()">
                    <option value="">Choose a student...</option>
                    <?php foreach ($students as $s): ?>
                        <option value="<?= e($s['id']) ?>"><?= e($s['first_name'] . ' ' . $s['last_name'] . ' (' . $s['student_number'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="label">Select Event</label>
                <select class="select" id="gen-event" onchange="generateQR()">
                    <option value="">Choose an event...</option>
                    <?php foreach ($events as $ev): ?>
                        <!-- UPDATED: QR now uses simple EVENT_id|USER_id format, no QR_token needed -->
                        <option value="<?= e($ev['id']) ?>"><?= e($ev['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="gen-output" style="display:none;" class="qr-preview mt-4">
                <div class="qr-box" style="display:flex;align-items:center;justify-content:center;min-height:220px;padding:1rem;border:1px solid var(--border);border-radius:4px;">
                    <canvas id="gen-canvas" width="200" height="200" style="border:1px solid #ccc;border-radius:4px;"></canvas>
                </div>
                <p class="text-sm font-medium" id="gen-name"></p>
                <p class="text-xs text-muted" id="gen-event-name"></p>
            </div>
        </div>
    </div>
</div>
