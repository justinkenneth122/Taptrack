<!-- Admin Panel -->
<?php
    requireAdmin();
    
    $subpage = isset($_GET['page']) ? str_replace('admin_', '', $_GET['page']) : 'home';
    if ($subpage === 'admin') $subpage = 'home';
    
    $adminPages = [
        'home' => ['Dashboard', 'admin', '🏠'],
        'events' => ['Events', 'admin_events', '📅'],
        'qr_generator' => ['QR Generator', 'admin_qr_generator', '▣'],
        'qr_scanner' => ['QR Scanner', 'admin_qr_scanner', '📷'],
        'attendance' => ['Attendance', 'admin_attendance', '📋'],
        'archived' => ['Archived Events', 'admin_archived', '📦'],
    ];
?>

<div class="admin-layout">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">▣</div>
            <span class="font-bold">Taptrack</span>
        </div>
        <nav class="sidebar-nav">
            <?php foreach ($adminPages as $key => [$label, $link, $icon]): ?>
                <a href="?page=<?php echo $link; ?>" class="sidebar-link <?php echo $subpage === $key ? 'active' : ''; ?>">
                    <span><?php echo $icon; ?></span>
                    <span><?php echo $label; ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-footer">
            <form method="POST"><input type="hidden" name="action" value="logout">
                <button type="submit" class="sidebar-link w-full" style="border:none;background:none;cursor:pointer;color:var(--sidebar-fg);font-size:0.875rem;text-align:left;">⬅ <span>Logout</span></button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="admin-main">
        <div class="admin-header">
            <span class="text-sm font-medium text-muted">Admin Panel</span>
        </div>
        <div class="admin-content">
            <?php 
                // Include admin sub-pages based on current subpage
                switch ($subpage) {
                    case 'home':
                        include __DIR__ . '/dashboard.php';
                        break;
                    case 'events':
                        include __DIR__ . '/events.php';
                        break;
                    case 'qr_generator':
                        include __DIR__ . '/qr_generator.php';
                        break;
                    case 'qr_scanner':
                        include __DIR__ . '/qr_scanner.php';
                        break;
                    case 'attendance':
                        include __DIR__ . '/attendance.php';
                        break;
                    case 'archived':
                        include __DIR__ . '/archived.php';
                        break;
                    default:
                        include __DIR__ . '/dashboard.php';
                }
            ?>
        </div>
    </div>
</div>
