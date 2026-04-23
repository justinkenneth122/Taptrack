<!-- Admin QR Scanner -->
<?php
    $events = $EventModel->getActive();
?>

<div class="space-y-6 max-w-lg">
    <h2 style="font-size:1.5rem;" class="font-bold">QR Scanner</h2>
    <div class="card">
        <div class="card-header"><div class="card-title">📷 Scan Student QR Codes</div></div>
        <div class="card-content space-y-4">
            <div>
                <label class="label">Event</label>
                <select class="select" id="scan-event">
                    <option value="">Select event to scan for...</option>
                    <?php foreach ($events as $ev): ?>
                        <option value="<?php echo e($ev['id']); ?>"><?php echo e($ev['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="position:relative;width:100%;min-height:280px;border-radius:8px;overflow:hidden;background:var(--muted);">
                <div id="scanner-placeholder" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;text-align:center;color:var(--muted-foreground);padding:1.5rem;">
                    <div><p style="font-size:3rem;opacity:0.4;">📷</p><p class="text-sm">Camera preview will appear here</p></div>
                </div>
                <div id="qr-reader"></div>
            </div>

            <div class="flex gap-2">
                <button class="btn btn-primary flex-1" id="btn-start-scan" onclick="startScanning()">📷 Start Scanning</button>
                <button class="btn btn-destructive flex-1" id="btn-stop-scan" onclick="stopScanning()" style="display:none;">Stop Scanning</button>
            </div>

            <div id="scan-error" class="flash flash-error" style="display:none;"></div>
            <div id="scan-result" style="display:none;" class="p-3"></div>
        </div>
    </div>
</div>
