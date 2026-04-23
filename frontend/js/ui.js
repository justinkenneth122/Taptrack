/**
 * ============================================================
 * TAPTRACK — Client-side UI Functions
 * ============================================================
 * Handles front-end interactions, QR generation, face registration, etc.
 */

// ============================================================
// LOGIN PAGE FUNCTIONS
// ============================================================

function switchTab(tab) {
    document.getElementById('panel-student').style.display = tab ===  'student' ? 'block' : 'none';
    document.getElementById('panel-admin').style.display = tab === 'admin' ? 'block' : 'none';
    document.getElementById('tab-student').className = 'tab-trigger' + (tab === 'student' ? ' active' : '');
    document.getElementById('tab-admin').className = 'tab-trigger' + (tab === 'admin' ? ' active' : '');
}

function switchStudentMode(mode) {
    document.getElementById('form-student-login').style.display = mode === 'login' ? 'block' : 'none';
    document.getElementById('form-student-register').style.display = mode === 'register' ? 'block' : 'none';
    document.getElementById('btn-login').className = 'btn flex-1 ' + (mode === 'login' ? 'btn-active' : 'btn-outline');
    document.getElementById('btn-register').className = 'btn flex-1 ' + (mode === 'register' ? 'btn-active' : 'btn-outline');
}

// ============================================================
// STUDENT DASHBOARD FUNCTIONS
// ============================================================

function toggleQR(eventId) {
    const el = document.getElementById('qr-' + eventId);
    const isOpen = el.style.display !== 'none';
    
    // Close all other QR codes
    document.querySelectorAll('[id^="qr-"]').forEach(e => {
        if (e.id.startsWith('qr-')) e.style.display = 'none';
    });
    
    if (!isOpen) {
        el.style.display = 'flex';
        const canvas = document.getElementById('qr-canvas-' + eventId);
        if (!canvas.dataset.rendered) {
            const studentId = document.body.dataset.studentId;
            QRCode.toCanvas(
                canvas,
                JSON.stringify({
                    studentId: studentId,
                    eventId: eventId,
                    system: 'taptrack'
                }),
                { width: 180, errorCorrectionLevel: 'H' }
            );
            canvas.dataset.rendered = '1';
        }
    }
}

// ============================================================
// ADMIN QR GENERATOR FUNCTIONS
// ============================================================

function generateQR() {
    const studentId = document.getElementById('gen-student').value;
    const eventId = document.getElementById('gen-event').value;
    const output = document.getElementById('gen-output');
    
    if (!studentId || !eventId) {
        output.style.display = 'none';
        return;
    }
    
    output.style.display = 'flex';
    const canvas = document.getElementById('gen-canvas');
    
    QRCode.toCanvas(
        canvas,
        JSON.stringify({
            studentId: studentId,
            eventId: eventId,
            system: 'taptrack'
        }),
        { width: 200, errorCorrectionLevel: 'H' }
    );
    
    document.getElementById('gen-name').textContent = 
        document.getElementById('gen-student').selectedOptions[0].text;
    document.getElementById('gen-event-name').textContent = 
        document.getElementById('gen-event').selectedOptions[0].text;
}

// ============================================================
// ADMIN QR SCANNER FUNCTIONS
// ============================================================

let scanner = null;
let isScanning = false;

async function startScanning() {
    const eventId = document.getElementById('scan-event').value;
    if (!eventId) {
        alert('Please select an event first.');
        return;
    }
    if (isScanning) return;

    document.getElementById('scan-error').style.display = 'none';
    document.getElementById('scan-result').style.display = 'none';
    document.getElementById('btn-start-scan').disabled = true;
    document.getElementById('btn-start-scan').textContent = '⏳ Starting camera...';

    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' } },
            audio: false
        });
        const deviceId = stream.getVideoTracks()[0]?.getSettings().deviceId;
        stream.getTracks().forEach(t => t.stop());

        scanner = new Html5Qrcode('qr-reader');
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        if (deviceId) {
            await scanner.start(deviceId, config, onScanSuccess, () => {});
        } else {
            const cameras = await Html5Qrcode.getCameras();
            if (!cameras.length) throw new Error('No camera detected.');
            const back = cameras.find(c => /back|rear|environment/i.test(c.label));
            await scanner.start(back?.id || cameras[0].id, config, onScanSuccess, () => {});
        }

        isScanning = true;
        document.getElementById('scanner-placeholder').style.display = 'none';
        document.getElementById('btn-start-scan').style.display = 'none';
        document.getElementById('btn-stop-scan').style.display = 'flex';
    } catch (err) {
        const msg = err.name === 'NotAllowedError' ? 
            'Camera access denied. Please allow camera permission.' :
            err.name === 'NotFoundError' ? 
            'No camera found on this device.' :
            err.name === 'NotReadableError' ? 
            'Camera is in use by another app.' :
            'Unable to start camera: ' + (err.message || err);
        document.getElementById('scan-error').textContent = msg;
        document.getElementById('scan-error').style.display = 'block';
        stopScanning();
    }
    document.getElementById('btn-start-scan').disabled = false;
    document.getElementById('btn-start-scan').textContent = '📷 Start Scanning';
}

async function stopScanning() {
    if (scanner) {
        try { await scanner.stop(); } catch {}
        try { scanner.clear(); } catch {}
        scanner = null;
    }
    isScanning = false;
    document.getElementById('scanner-placeholder').style.display = 'flex';
    document.getElementById('btn-start-scan').style.display = 'flex';
    document.getElementById('btn-stop-scan').style.display = 'none';
}

async function onScanSuccess(decodedText) {
    const resultEl = document.getElementById('scan-result');
    const eventId = document.getElementById('scan-event').value;
    
    try {
        const parsed = JSON.parse(decodedText);
        if (parsed.system !== 'taptrack') {
            showScanResult(false, 'Invalid QR code — not a Taptrack code.');
            return;
        }
        if (parsed.eventId && parsed.eventId !== eventId) {
            showScanResult(false, 
                '⚠ This QR code is for a different event. Please use the correct QR code.');
            return;
        }
        
        parsed.eventId = eventId;
        const resp = await fetch('?ajax=scan_qr', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(parsed)
        });
        const data = await resp.json();
        showScanResult(data.success, data.message);
    } catch {
        showScanResult(false, 'Could not read QR code data.');
    }
}

function showScanResult(success, message) {
    const el = document.getElementById('scan-result');
    el.style.display = 'flex';
    el.style.background = success ? 'hsl(152 60% 40% / 0.1)' : 'hsl(0 72% 51% / 0.1)';
    el.style.borderRadius = 'var(--radius)';
    el.style.alignItems = 'center';
    el.style.gap = '0.5rem';
    el.innerHTML = `<span style="font-size:1.25rem;">${success ? '✅' : '❌'}</span><span class="text-sm">${message}</span>`;
}

// ============================================================
// MODAL FUNCTIONS
// ============================================================

function openModal(modalId) {
    document.getElementById(modalId).classList.add('open');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('open');
}

// ============================================================
// ARCHIVED EVENTS COLLAPSIBLE
// ============================================================

function toggleCollapsible(id) {
    const el = document.getElementById(id);
    const arrow = document.getElementById('arrow-' + id);
    const isOpen = el.classList.contains('open');
    el.classList.toggle('open');
    if (arrow) arrow.textContent = isOpen ? '▸' : '▾';
}


// ============================================================
// UTILITY FUNCTIONS
// ============================================================

/**
 * Show toast notification
 */
function showToast(message, type = 'success', duration = 4000) {
    const toast = document.createElement('div');
    toast.className = `flash flash-${type}`;
    toast.textContent = message;
    toast.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:200;max-width:400px;';
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, duration);
}

/**
 * Confirm before action
 */
function confirmAction(message) {
    return confirm(message);
}

/**
 * Redirect to URL
 */
function redirect(url) {
    window.location.href = url;
}
