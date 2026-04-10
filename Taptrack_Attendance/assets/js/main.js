// ======================== LIBRARY INITIALIZATION CHECK ========================
console.log('[MAIN.JS] Script loaded');
console.log('[MAIN.JS] QRious available:', typeof QRious !== 'undefined' ? '✓ YES' : '❌ NO');
console.log('[MAIN.JS] Html5Qrcode available:', typeof Html5Qrcode !== 'undefined' ? '✓ YES' : '❌ NO');

// ======================== LOGIN PAGE ========================
function switchTab(tab) {
    document.getElementById('panel-student').style.display = tab === 'student' ? 'block' : 'none';
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

// ======================== FACE REGISTRATION ========================
const MODEL_URL = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';
let faceStream = null;
let modelsLoaded = false;
let modelsLoading = false;
let faceDetectionActive = false;
let lastDetectionTime = 0;
let detectionDebounce = 200;

// Face detection feedback states
const FACE_STATE = {
    NO_FACE: 'no_face',
    FACE_DETECTED: 'face_detected',
    FACE_CENTERED: 'face_centered',
    MULTIPLE_FACES: 'multiple_faces',
    FACE_TOO_SMALL: 'face_too_small',
    FACE_TOO_LARGE: 'face_too_large',
    LOADING: 'loading'
};

let currentFaceState = FACE_STATE.NO_FACE;
let lastValidDescriptor = null;

async function loadModels(STUDENT_ID) {
    if (modelsLoaded) return;
    if (modelsLoading) return modelsLoading;
    modelsLoading = (async () => {
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
        ]);
        modelsLoaded = true;
    })();
    return modelsLoading;
}

async function startFaceCamera(STUDENT_ID) {
    const video = document.getElementById('face-video');
    const idle = document.getElementById('face-idle');
    const guide = document.getElementById('face-guide');
    const btnStart = document.getElementById('btn-start-camera');
    const btnCapture = document.getElementById('btn-capture');
    const btnRestart = document.getElementById('btn-restart');
    const loading = document.getElementById('face-loading');
    const error = document.getElementById('face-error');
    const title = document.getElementById('face-title');
    const subtitle = document.getElementById('face-subtitle');
    const faceStatus = document.getElementById('face-status') || createFaceStatusElement();

    error.style.display = 'none';
    btnStart.disabled = true;
    btnStart.textContent = '⏳ Opening Camera...';

    try {
        if (faceStream) {
            faceStream.getTracks().forEach(t => t.stop());
            faceStream = null;
        }

        faceStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'user' }, width: { ideal: 720 }, height: { ideal: 960 } },
            audio: false
        });

        video.srcObject = faceStream;
        video.style.display = 'block';
        idle.style.display = 'none';
        guide.style.display = 'block';
        faceStatus.style.display = 'block';

        btnStart.style.display = 'none';
        btnCapture.style.display = 'flex';
        btnCapture.disabled = true;
        btnRestart.style.display = 'flex';

        title.textContent = 'Your live selfie preview is ready.';
        subtitle.textContent = 'Preparing the selfie scanner. Keep the camera open for a moment.';
        loading.style.display = 'flex';
        document.getElementById('face-loading-text').textContent = 'Preparing selfie scanner...';

        await loadModels(STUDENT_ID);
        loading.style.display = 'none';
        
        // Start real-time face detection feedback
        faceDetectionActive = true;
        startRealtimeFaceDetection(video, btnCapture, faceStatus);
        
        title.textContent = 'Your live selfie preview is ready.';
        subtitle.textContent = 'Look straight at the camera. Keep your full face visible.';

    } catch (err) {
        console.error('Camera error:', err);
        btnStart.disabled = false;
        btnStart.textContent = 'Next →';
        let msg = 'Could not access the camera. Please try again.';
        if (err.name === 'NotAllowedError') msg = 'Allow camera access to continue with selfie verification.';
        else if (err.name === 'NotFoundError') msg = 'No camera was detected on this device.';
        else if (err.name === 'NotReadableError') msg = 'Your camera is being used by another app or browser tab.';
        error.textContent = msg;
        error.style.display = 'block';
    }
}

// Create face status indicator element
function createFaceStatusElement() {
    const existing = document.getElementById('face-status');
    if (existing) return existing;
    
    const statusEl = document.createElement('div');
    statusEl.id = 'face-status';
    statusEl.style.cssText = `
        position: absolute;
        bottom: 1.5rem;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        z-index: 10;
        min-width: 200px;
        text-align: center;
    `;
    statusEl.innerHTML = '🔍 No face detected';
    document.getElementById('face-viewport')?.appendChild(statusEl);
    return statusEl;
}

// Real-time face detection with visual feedback
async function startRealtimeFaceDetection(video, btnCapture, faceStatus) {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const detectionInterval = 300; // Check every 300ms for performance
    let lastCheck = 0;

    const detectFace = async () => {
        const now = Date.now();
        if (now - lastCheck < detectionInterval) {
            if (faceDetectionActive) requestAnimationFrame(detectFace);
            return;
        }
        lastCheck = now;

        if (!video || !video.srcObject || !modelsLoaded) {
            if (faceDetectionActive) requestAnimationFrame(detectFace);
            return;
        }

        try {
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Detect all faces first
            const detections = await faceapi
                .detectAllFaces(canvas, new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.3 }))
                .withFaceLandmarks(true)
                .withFaceDescriptors();

            updateFaceState(detections, btnCapture, faceStatus);

            // Also check with single face detection for better accuracy
            if (detections.length === 0) {
                const singleDetection = await faceapi
                    .detectSingleFace(canvas, new faceapi.TinyFaceDetectorOptions({ inputSize: 512, scoreThreshold: 0.2 }))
                    .withFaceLandmarks(true)
                    .withFaceDescriptor();
                
                if (singleDetection?.descriptor) {
                    updateFaceState([singleDetection], btnCapture, faceStatus);
                }
            }
        } catch (err) {
            console.error('Detection error:', err);
        }

        if (faceDetectionActive) requestAnimationFrame(detectFace);
    };

    detectFace();
}

// Update UI based on detected face state
function updateFaceState(detections, btnCapture, faceStatus) {
    let newState = FACE_STATE.NO_FACE;
    let statusText = '🔍 No face detected';
    let statusColor = '#dc2626'; // red

    if (detections.length === 0) {
        newState = FACE_STATE.NO_FACE;
        statusText = '🔍 No face detected';
        statusColor = '#dc2626';
    } else if (detections.length > 1) {
        newState = FACE_STATE.MULTIPLE_FACES;
        statusText = `⚠️ ${detections.length} faces detected - keep only one`;
        statusColor = '#f59e0b'; // amber
        btnCapture.disabled = true;
    } else {
        const detection = detections[0];
        const box = detection.detection.box;
        const canvasWidth = 640;
        const canvasHeight = 480;
        
        // Calculate if face is centered and appropriately sized
        const faceCenterX = box.x + box.width / 2;
        const faceCenterY = box.y + box.height / 2;
        const canvasCenterX = canvasWidth / 2;
        const canvasCenterY = canvasHeight / 2;
        
        // Deviation tolerance (in pixels)
        const centerTolerance = 80;
        const isCentered = Math.abs(faceCenterX - canvasCenterX) < centerTolerance && 
                          Math.abs(faceCenterY - canvasCenterY) < centerTolerance;
        
        // Face size check (should be 30-70% of video width)
        const faceSize = box.width / canvasWidth;
        const isSizeOK = faceSize > 0.25 && faceSize < 0.75;

        if (faceSize < 0.25) {
            newState = FACE_STATE.FACE_TOO_SMALL;
            statusText = '📍 Move closer to camera';
            statusColor = '#f59e0b';
            btnCapture.disabled = true;
        } else if (faceSize > 0.75) {
            newState = FACE_STATE.FACE_TOO_LARGE;
            statusText = '📍 Move away from camera';
            statusColor = '#f59e0b';
            btnCapture.disabled = true;
        } else if (!isCentered) {
            newState = FACE_STATE.NO_FACE;
            statusText = '↔️ Center your face';
            statusColor = '#f59e0b';
            btnCapture.disabled = true;
        } else {
            newState = FACE_STATE.FACE_CENTERED;
            statusText = '✅ Face centered - Ready to capture!';
            statusColor = '#16a34a'; // green
            btnCapture.disabled = false;
            lastValidDescriptor = detection.descriptor;
        }
    }

    // Update status display
    if (faceStatus) {
        faceStatus.innerHTML = statusText;
        faceStatus.style.backgroundColor = statusColor;
    }

    currentFaceState = newState;
}

async function captureFace(STUDENT_ID) {
    const video = document.getElementById('face-video');
    const btnCapture = document.getElementById('btn-capture');
    const btnRestart = document.getElementById('btn-restart');
    const loading = document.getElementById('face-loading');
    const error = document.getElementById('face-error');
    const title = document.getElementById('face-title');
    const subtitle = document.getElementById('face-subtitle');
    const faceStatus = document.getElementById('face-status');

    btnCapture.disabled = true;
    btnCapture.innerHTML = '<span class="animate-spin" style="display:inline-block;width:1rem;height:1rem;border:2px solid hsl(50 100% 95%);border-top-color:transparent;border-radius:50%;margin-right:0.5rem;"></span> Capturing Selfie...';
    btnRestart.disabled = true;
    error.style.display = 'none';

    try {
        if (!lastValidDescriptor) {
            error.textContent = '❌ No valid face detected. Please adjust your position and try again.';
            error.style.display = 'block';
            return;
        }

        faceStatus.innerHTML = '⏳ Verifying face...';
        faceStatus.style.backgroundColor = '#3b82f6';

        const json = JSON.stringify(Array.from(lastValidDescriptor));
        
        // First, check for duplicate faces BEFORE saving
        loading.style.display = 'flex';
        document.getElementById('face-loading-text').textContent = 'Checking for duplicate faces...';
        
        const checkResp = await fetch('?ajax=check_face_duplicate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                student_id: STUDENT_ID, 
                face_descriptor: json 
            })
        });
        const checkResult = await checkResp.json();

        if (!checkResult.success) {
            loading.style.display = 'none';
            error.textContent = '❌ ' + (checkResult.message || 'Error checking face data.');
            error.style.display = 'block';
            faceStatus.innerHTML = '❌ Verification failed';
            faceStatus.style.backgroundColor = '#dc2626';
            return;
        }

        // If duplicate detected, show alert and block registration
        if (checkResult.is_duplicate) {
            loading.style.display = 'none';
            const dupName = checkResult.matched_student || 'unknown student';
            const confidence = Math.round(checkResult.confidence);
            
            error.innerHTML = `<strong>⚠️ Duplicate Face Detected!</strong><br>` +
                `This face is already registered with <strong>${dupName}</strong> ` +
                `(${confidence}% match).<br><br>` +
                `<em>Each student can only have one registered face.</em><br>` +
                `Please contact your administrator if you believe this is an error.`;
            error.style.display = 'block';
            faceStatus.innerHTML = '❌ Duplicate face - Registration blocked';
            faceStatus.style.backgroundColor = '#dc2626';
            return;
        }

        // No duplicate - proceed with registration
        document.getElementById('face-loading-text').textContent = 'Registering face...';
        const resp = await fetch('?ajax=save_face_descriptor', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ student_id: STUDENT_ID, face_descriptor: json })
        });
        const result = await resp.json();
        loading.style.display = 'none';

        if (!result.success) {
            error.textContent = '❌ ' + (result.message || 'Could not save your face data. Please try again.');
            error.style.display = 'block';
            faceStatus.innerHTML = '❌ Registration failed';
            faceStatus.style.backgroundColor = '#dc2626';
            return;
        }

        // Success!
        faceDetectionActive = false;
        if (faceStream) {
            faceStream.getTracks().forEach(t => t.stop());
            faceStream = null;
        }
        
        faceStatus.innerHTML = '✅ Face registered successfully!';
        faceStatus.style.backgroundColor = '#16a34a';
        
        document.getElementById('face-reg-card').style.display = 'none';
        document.getElementById('face-success-card').style.display = 'block';

    } catch (err) {
        console.error('Face capture error:', err);
        loading.style.display = 'none';
        error.textContent = '❌ ' + (err.message || 'An error occurred during face registration. Please try again.');
        error.style.display = 'block';
        faceStatus.innerHTML = '❌ Error occurred';
        faceStatus.style.backgroundColor = '#dc2626';
    } finally {
        btnCapture.disabled = false;
        btnCapture.innerHTML = '📷 Take Selfie';
        btnRestart.disabled = false;
    }
}

// ======================== STUDENT DASHBOARD ========================
function toggleQR(eventId, studentId) {
    console.log('[toggleQR] Called with eventId:', eventId, 'studentId:', studentId);

    const el = document.getElementById('qr-' + eventId);
    if (!el) {
        console.error('❌ QR container not found for event:', eventId);
        console.error('Looking for element with ID: qr-' + eventId);
        return;
    }
    console.log('✓ Found QR container element:', el);

    const isOpen = el.style.display !== 'none';
    console.log('[toggleQR] Current display state:', el.style.display, '(isOpen:', isOpen, ')');

    // Close all other QR codes
    document.querySelectorAll('[id^="qr-"]').forEach(e => {
        if (e.id.startsWith('qr-') && e !== el) e.style.display = 'none';
    });

    if (!isOpen) {
        // Show parent container
        el.style.display = 'block';
        console.log('[toggleQR] ✓ Parent container set to visible');

        const qrContainer = document.getElementById('qr-canvas-' + eventId);
        if (!qrContainer) {
            console.error('❌ QR canvas not found for event:', eventId);
            console.error('Looking for canvas with ID: qr-canvas-' + eventId);
            return;
        }
        console.log('✓ Found QR canvas element:', qrContainer);
        console.log('Canvas type:', qrContainer.tagName);
        console.log('Canvas width:', qrContainer.width, 'height:', qrContainer.height);

        // ⭐ IMPORTANT: Make sure canvas is visible
        qrContainer.style.display = 'block';
        console.log('[toggleQR] ✓ Canvas visibility set to block');

        // QR code data
        const qrData = `EVENT_${eventId}|USER_${studentId}`;
        console.log('[toggleQR] Generating QR for:', qrData);
        console.log('[toggleQR] QRious library available:', typeof QRious);

        try {
            // Verify QRious is available
            if (!window.QRious) {
                throw new Error('QRious library not loaded! Check if qrious.min.js is properly included.');
            }

            console.log('[toggleQR] Creating new QRious instance...');
            
            // Generate QR code using QRious library
            const qr = new QRious({
                element: qrContainer,
                value: qrData,
                size: 200,
                level: 'H',
                mime: 'image/png'
            });

            console.log('[toggleQR] ✓ QR generated successfully');
            console.log('[toggleQR] QRious instance:', qr);
            
            const errorDiv = document.getElementById('qr-error-' + eventId);
            if (errorDiv) errorDiv.style.display = 'none';
        } catch (err) {
            console.error('[toggleQR] ❌ Error generating QR:', err);
            console.error('[toggleQR] Error message:', err.message);
            console.error('[toggleQR] Error stack:', err.stack);
            
            const errorDiv = document.getElementById('qr-error-' + eventId);
            if (errorDiv) {
                errorDiv.innerHTML = '❌ Failed to generate QR: ' + err.message;
                errorDiv.style.display = 'block';
            }
        }
    } else {
        console.log('[toggleQR] Hiding QR container');
        el.style.display = 'none';
    }
}

// ======================== ADMIN QR GENERATOR ========================
function generateQR() {
    const studentId = document.getElementById('gen-student').value;
    const eventId = document.getElementById('gen-event').value;
    const output = document.getElementById('gen-output');
    const container = document.getElementById('gen-canvas');

    console.log('generateQR called - Student:', studentId, 'Event:', eventId);

    if (!studentId || !eventId) {
        output.style.display = 'none';
        return;
    }

    if (!container) {
        console.error('QR container element not found');
        return;
    }

    // QR DATA (text format: EVENT_X|USER_Y)
    const qrData = `EVENT_${eventId}|USER_${studentId}`;
    console.log('Generating QR code with data:', qrData);

    try {
        // Generate QR code using QRious library directly on canvas
        new QRious({
            element: container,
            value: qrData,
            size: 200,
            level: 'H',
            mime: 'image/png'
        });
        
        // Show QR output container
        output.style.display = 'flex';
        console.log('✓ QR code generated successfully');

        // Display names
        const studentOption = document.getElementById('gen-student').selectedOptions[0];
        const eventOption = document.getElementById('gen-event').selectedOptions[0];

        if (studentOption) {
            document.getElementById('gen-name').textContent = studentOption.text;
        }
        if (eventOption) {
            document.getElementById('gen-event-name').textContent = eventOption.text;
        }
    } catch (err) {
        console.error('Error generating QR:', err);
        alert('Error generating QR: ' + err.message);
    }
}

// ======================== ADMIN QR SCANNER ========================
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
        const readerElement = document.getElementById('qr-reader');
        if (!readerElement) {
            throw new Error('QR reader element not found');
        }

        // Create Html5Qrcode instance
        scanner = new Html5Qrcode('qr-reader');
        
        // Check if camera is already in use
        if (scanner._isScanning) {
            await scanner.stop();
        }

        // Get available cameras
        const cameras = await Html5Qrcode.getCameras();
        if (!cameras || cameras.length === 0) {
            throw new Error('No cameras found on this device.');
        }

        // Prefer rear/environment camera for QR scanning
        let selectedCamera = cameras[0];
        const rearCamera = cameras.find(camera => 
            camera.label && /back|rear|environment|landscape/i.test(camera.label)
        );
        if (rearCamera) {
            selectedCamera = rearCamera;
        }

        // Configuration for QR scanning
        const config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0,
            disableFlip: false
        };

        // Start scanning
        await scanner.start(
            selectedCamera.id,
            config,
            onScanSuccess,
            onScanFailure
        );

        isScanning = true;
        document.getElementById('scanner-placeholder').style.display = 'none';
        document.getElementById('btn-start-scan').style.display = 'none';
        document.getElementById('btn-stop-scan').style.display = 'flex';
        document.getElementById('btn-start-scan').disabled = false;
        document.getElementById('btn-start-scan').textContent = '📷 Start Scanning';

        console.log('✓ QR scanning started successfully with camera:', selectedCamera.label);
    } catch (err) {
        console.error('Scanning error:', err);
        let msg = 'Unable to start camera: ' + (err.message || err);
        
        if (err.name === 'NotAllowedError' || err.message?.includes('NotAllowedError')) {
            msg = 'Camera access denied. Please allow camera permission in your browser settings.';
        } else if (err.name === 'NotFoundError' || err.message?.includes('No camera')) {
            msg = 'No camera found on this device.';
        } else if (err.name === 'NotReadableError') {
            msg = 'Camera is in use by another app or browser tab.';
        }
        
        document.getElementById('scan-error').textContent = msg;
        document.getElementById('scan-error').style.display = 'block';
        stopScanning();
        document.getElementById('btn-start-scan').disabled = false;
        document.getElementById('btn-start-scan').textContent = '📷 Start Scanning';
    }
}

async function stopScanning() {
    if (scanner) {
        try {
            await scanner.stop();
            scanner.clear();
        } catch (err) {
            console.error('Error stopping scanner:', err);
        }
        scanner = null;
    }
    isScanning = false;
    document.getElementById('scanner-placeholder').style.display = 'flex';
    document.getElementById('btn-start-scan').style.display = 'flex';
    document.getElementById('btn-stop-scan').style.display = 'none';
    console.log('✓ QR scanning stopped');
}

function onScanSuccess(decodedText) {
    console.log('Decoded QR text:', decodedText);
    try {
        // Parse QR format: EVENT_{event_id}|USER_{user_id}
        const match = decodedText.match(/^EVENT_(\d+)\|USER_(\d+)$/);
        if (!match) {
            console.warn('Invalid QR format:', decodedText);
            showScanResult(false, 'Invalid QR code — not a TapTrack code.');
            return;
        }
        
        const eventId = match[1];
        const studentId = match[2];
        
        console.log('Processing attendance - Event:', eventId, 'Student:', studentId);
        
        // Send to backend
        fetch('?ajax=scan_qr', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({eventId: eventId, studentId: studentId})
        })
        .then(resp => resp.json())
        .then(data => {
            console.log('Server response:', data);
            showScanResult(data.success, data.message);
        })
        .catch(err => {
            console.error('Error sending scan data:', err);
            showScanResult(false, 'Error processing QR code. Please try again.');
        });
    } catch (err) {
        console.error('Error parsing QR code:', err);
        showScanResult(false, 'Could not read QR code data.');
    }
}

function onScanFailure(error) {
    // This is called continuously - only log errors that aren't timeout-related
    if (error && !error.message?.includes('No QR code found')) {
        console.debug('Scan status:', error.message || error);
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

// ======================== ADMIN ARCHIVED ========================
function toggleCollapsible(id) {
    const el = document.getElementById(id);
    const arrow = document.getElementById('arrow-' + id);
    const isOpen = el.classList.contains('open');
    el.classList.toggle('open');
    if (arrow) arrow.textContent = isOpen ? '▸' : '▾';
}

// ======================== CLEANUP ========================
window.addEventListener('beforeunload', () => {
    if (faceStream) faceStream.getTracks().forEach(t => t.stop());
});
