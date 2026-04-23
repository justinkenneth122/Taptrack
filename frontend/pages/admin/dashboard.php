<!-- Admin Dashboard -->
<?php
    $eventCount = $EventModel->getCount(false);
    $studentCount = $StudentModel->getCount();
    $stats = $AttendanceController->getStatistics();
    
    $cardData = [
        ['Total Events', $eventCount, 'var(--primary)', 'var(--primary-foreground)', '📅'],
        ['Registered Students', $studentCount, 'var(--gold)', 'var(--secondary-foreground)', '👥'],
        ['Total Scans', $stats['total_scans'], 'var(--success)', 'var(--success-foreground)', '▣'],
        ['Unique Attendees', $stats['unique_students'], 'var(--muted)', 'var(--muted-foreground)', '✓'],
    ];
?>

<div class="space-y-6 max-w-4xl">
    <h2 style="font-size:1.5rem;" class="font-bold">Dashboard</h2>
    <div class="stats-grid">
        <?php foreach ($cardData as [$label, $value, $bg, $fg, $icon]): ?>
        <div class="card">
            <div class="card-header flex items-center gap-3" style="flex-direction:row;">
                <div class="stat-icon" style="background:<?php echo $bg; ?>;color:<?php echo $fg; ?>;"><?php echo $icon; ?></div>
                <span class="text-sm font-medium text-muted"><?php echo $label; ?></span>
            </div>
            <div class="card-content"><p class="stat-value"><?php echo $value; ?></p></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
