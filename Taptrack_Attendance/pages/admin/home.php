<?php
/**
 * Admin Dashboard Home
 */

$eventCount = $pdo->query("SELECT COUNT(*) FROM events WHERE archived = 0")->fetchColumn();
$studentCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$scanCount = $pdo->query("SELECT COUNT(*) FROM attendance")->fetchColumn();
$uniqueCount = $pdo->query("SELECT COUNT(DISTINCT student_id) FROM attendance")->fetchColumn();

$stats = [
    ['Total Events', $eventCount, 'var(--primary)', 'var(--primary-foreground)', '📅'],
    ['Registered Students', $studentCount, 'var(--gold)', 'var(--secondary-foreground)', '👥'],
    ['Total Scans', $scanCount, 'var(--success)', 'var(--success-foreground)', '▣'],
    ['Unique Attendees', $uniqueCount, 'var(--muted)', 'var(--muted-foreground)', '✓'],
];
?>
<div class="space-y-6 max-w-4xl">
    <h2 style="font-size:1.5rem;" class="font-bold">Dashboard</h2>
    <div class="stats-grid">
        <?php foreach ($stats as [$label, $value, $bg, $fg, $icon]): ?>
        <div class="card">
            <div class="card-header flex items-center gap-3" style="flex-direction:row;">
                <div class="stat-icon" style="background:<?= $bg ?>;color:<?= $fg ?>;"><?= $icon ?></div>
                <span class="text-sm font-medium text-muted"><?= $label ?></span>
            </div>
            <div class="card-content"><p class="stat-value"><?= $value ?></p></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
