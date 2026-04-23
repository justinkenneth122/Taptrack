<!-- Admin QR Generator -->
<?php
    $events = $EventModel->getActive();
    $students = $StudentModel->getAll();
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
                        <option value="<?php echo e($s['id']); ?>"><?php echo e($s['first_name'] . ' ' . $s['last_name'] . ' (' . $s['student_number'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="label">Select Event</label>
                <select class="select" id="gen-event" onchange="generateQR()">
                    <option value="">Choose an event...</option>
                    <?php foreach ($events as $ev): ?>
                        <option value="<?php echo e($ev['id']); ?>"><?php echo e($ev['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="gen-output" style="display:none;" class="qr-preview mt-4">
                <div class="qr-box"><canvas id="gen-canvas"></canvas></div>
                <p class="text-sm font-medium" id="gen-name"></p>
                <p class="text-xs text-muted" id="gen-event-name"></p>
            </div>
        </div>
    </div>
</div>

<script>
function generateQR() {
    const studentSelect = document.getElementById('gen-student');
    const eventSelect = document.getElementById('gen-event');
    const outputDiv = document.getElementById('gen-output');
    const canvas = document.getElementById('gen-canvas');
    const nameDisplay = document.getElementById('gen-name');
    const eventDisplay = document.getElementById('gen-event-name');

    const studentId = studentSelect.value;
    const eventId = eventSelect.value;

    // We only generate if both a student and an event are selected
    if (studentId && eventId) {
        // Format the data for the scanner (e.g., "STUDENT_ID|EVENT_ID")
        const qrData = studentId + '|' + eventId;

        // Use the QRCode library to draw on the canvas
        QRCode.toCanvas(canvas, qrData, {
            width: 200,
            margin: 2,
            color: {
                dark: '#1a4731', // Using a dark green to match your theme
                light: '#ffffff'
            }
        }, function (error) {
            if (error) {
                console.error(error);
            } else {
                // Show the hidden output div
                outputDiv.style.display = 'block';
                
                // Update the text labels below the QR code
                nameDisplay.textContent = studentSelect.options[studentSelect.selectedIndex].text;
                eventDisplay.textContent = eventSelect.options[eventSelect.selectedIndex].text;
            }
        });
    } else {
        // Hide the preview if selection is incomplete
        outputDiv.style.display = 'none';
    }
}
</script>