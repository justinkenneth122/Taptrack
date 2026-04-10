<?php
/**
 * Face Registration Page
 */

$studentId = $_SESSION['face_reg_student_id'] ?? '';
$studentName = $_SESSION['user_name'] ?? 'Student';
?>
<div class="login-wrapper" style="position:relative;">
    <div style="position:absolute;inset:0;background:url('images/feu-roosevelt-bg.png') center/cover no-repeat;z-index:0;"></div>
    <div style="position:absolute;inset:0;background:linear-gradient(135deg, hsla(145, 60%, 18%, 0.75) 0%, hsla(45, 85%, 45%, 0.65) 50%, hsla(145, 50%, 25%, 0.8) 100%);z-index:1;"></div>

    <div style="position:relative;z-index:2;width:100%;max-width:28rem;" class="space-y-4">
        <div class="text-center mb-4">
            <div style="width:48px;height:48px;border-radius:8px;background:var(--primary);display:inline-flex;align-items:center;justify-content:center;margin-bottom:0.5rem;">
                <span style="font-size:1.5rem;color:var(--primary-foreground);">▣</span>
            </div>
            <h2 style="font-size:1.25rem;color:#fff;font-weight:700;text-shadow:0 2px 4px rgba(0,0,0,0.2);">Almost Done!</h2>
            <p class="text-sm" style="color:rgba(255,255,255,0.8);text-shadow:0 1px 2px rgba(0,0,0,0.15);">Register your face to complete your account setup.</p>
        </div>

        <!-- Face Registration Card -->
        <div class="card shadow-xl" style="border-color:hsl(152 60% 22% / 0.2);" id="face-reg-card">
            <div style="background:var(--primary);padding:0.75rem 1rem;color:var(--primary-foreground);">
                <div class="flex items-center gap-2 text-sm font-bold">🛡️ Verify with Face Recognition</div>
            </div>
            <div style="padding:1.25rem;" class="space-y-5">
                <!-- Phone Frame -->
                <div class="face-phone-frame">
                    <div class="face-viewport" id="face-viewport" style="position: relative;">
                        <!-- Idle state -->
                        <div id="face-idle" class="flex flex-col items-center justify-center" style="height:100%;gap:1.25rem;padding:1.5rem;text-align:center;">
                            <div class="face-icon-circle">👤</div>
                            <div class="space-y-2">
                                <p style="font-size:1.25rem;font-weight:600;">Take a selfie to secure your account</p>
                                <p class="text-sm text-muted" style="line-height:1.5;">We will use one clear selfie for student face verification during attendance.</p>
                            </div>
                        </div>
                        <!-- Camera active state -->
                        <video id="face-video" style="display:none;width:100%;height:100%;object-fit:cover;transform:scaleX(-1);" playsinline muted autoplay></video>
                        <div id="face-guide" style="display:none;position:relative;width:100%;height:100%;">
                            <!-- Circular face guide overlay -->
                            <svg style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:280px;height:280px;z-index:5;" viewBox="0 0 280 280">
                                <!-- Outer circle guide -->
                                <circle cx="140" cy="140" r="130" fill="none" stroke="rgba(34, 197, 94, 0.5)" stroke-width="2" stroke-dasharray="5,5"/>
                                <!-- Inner circle (target size) -->
                                <circle cx="140" cy="140" r="110" fill="none" stroke="rgba(34, 197, 94, 0.3)" stroke-width="1"/>
                                <!-- Corner markers -->
                                <line x1="30" y1="30" x2="50" y2="30" stroke="rgba(34, 197, 94, 0.6)" stroke-width="3" stroke-linecap="round"/>
                                <line x1="30" y1="30" x2="30" y2="50" stroke="rgba(34, 197, 94, 0.6)" stroke-width="3" stroke-linecap="round"/>
                                <line x1="250" y1="30" x2="230" y2="30" stroke="rgba(34, 197, 94, 0.6)" stroke-width="3" stroke-linecap="round"/>
                                <line x1="250" y1="30" x2="250" y2="50" stroke="rgba(34, 197, 94, 0.6)" stroke-width="3" stroke-linecap="round"/>
                                <line x1="30" y1="250" x2="50" y2="250" stroke="rgba(34, 197, 94, 0.6)" stroke-width="3" stroke-linecap="round"/>
                                <line x1="30" y1="250" x2="30" y2="230" stroke="rgba(34, 197, 94, 0.6)" stroke-width="3" stroke-linecap="round"/>
                                <line x1="250" y1="250" x2="230" y2="250" stroke="rgba(34, 197, 94, 0.6)" stroke-width="3" stroke-linecap="round"/>
                                <line x1="250" y1="250" x2="250" y2="230" stroke="rgba(34, 197, 94, 0.6)" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            <!-- Animated scanning line -->
                            <div class="face-scan-line"></div>
                            <!-- Status text overlay -->
                            <div class="face-info-overlay" style="position:absolute;bottom:20px;left:0;right:0;text-align:center;z-index:10;">
                                <p class="text-sm font-medium" style="color:white;text-shadow:0 1px 3px rgba(0,0,0,0.5);">Center your face inside the circle</p>
                                <p class="text-xs text-muted mt-2" style="color:rgba(255,255,255,0.8);text-shadow:0 1px 2px rgba(0,0,0,0.5);">Keep your eyes open, face the camera, good lighting</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Text -->
                <div class="text-center space-y-3" id="face-status-area">
                    <p class="text-sm font-medium" id="face-title">Make sure your full face is visible before continuing.</p>
                    <p class="text-xs text-muted" id="face-subtitle">Use a bright area, keep the phone steady, and allow camera access when asked.</p>
                    <p class="text-xs" style="color:var(--destructive);display:none;" id="face-error"></p>
                    <p class="flex items-center justify-center gap-2 text-sm text-muted" id="face-loading" style="display:none;">
                        <span class="animate-spin" style="display:inline-block;width:1rem;height:1rem;border:2px solid var(--muted);border-top-color:var(--primary);border-radius:50%;"></span>
                        <span id="face-loading-text">Preparing selfie scanner...</span>
                    </p>
                </div>

                <!-- Buttons -->
                <div class="flex flex-col gap-2" id="face-buttons">
                    <button class="btn btn-primary w-full" id="btn-start-camera" onclick="startFaceCamera('<?= e($studentId) ?>')">Next →</button>
                    <button class="btn btn-primary w-full" id="btn-capture" onclick="captureFace('<?= e($studentId) ?>')" style="display:none;" disabled>📷 Take Selfie</button>
                    <button class="btn btn-outline w-full" id="btn-restart" onclick="startFaceCamera('<?= e($studentId) ?>')" style="display:none;">🔄 Restart Camera</button>
                </div>
            </div>
        </div>

        <!-- Success Card (hidden initially) -->
        <div class="card shadow-xl" id="face-success-card" style="display:none;border-color:hsl(152 60% 40% / 0.3);background:hsl(152 60% 40% / 0.05);">
            <div style="background:var(--success);padding:0.75rem 1rem;text-align:center;font-weight:600;font-size:0.875rem;color:var(--success-foreground);">Verification complete</div>
            <div style="padding:2rem;" class="flex flex-col items-center gap-3 text-center">
                <span style="font-size:3.5rem;">✅</span>
                <p class="font-bold" style="font-size:1.125rem;">Face Registered Successfully</p>
                <p class="text-sm text-muted">You can now continue to your student account.</p>
                <form method="POST" class="mt-2 w-full">
                    <input type="hidden" name="action" value="skip_face_reg">
                    <button type="submit" class="btn btn-primary w-full">Continue to Dashboard →</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
