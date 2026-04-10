<?php
/**
 * Login Page with Modern Background Design
 * Uses background image with gradient overlay and blur effects
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TapTrack - Login</title>
    <style>
        /* ============================================================
           RESPONSIVE LOGIN PAGE - MOBILE FIRST APPROACH
           Supports: Mobile (320px), Tablet (481px), iPad (769px), Desktop (1025px+)
           ============================================================ */

        /* ROOT - Base responsive units */
        :root {
            --base-font: clamp(14px, 2vw, 16px);
            --spacing-xs: clamp(0.25rem, 1vw, 0.5rem);
            --spacing-sm: clamp(0.5rem, 2vw, 1rem);
            --spacing-md: clamp(1rem, 3vw, 1.5rem);
            --spacing-lg: clamp(1.5rem, 4vw, 2rem);
            --spacing-xl: clamp(2rem, 5vw, 3rem);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            width: 100%;
            overflow-x: hidden;
        }

        body {
            font-size: var(--base-font);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        /* ============================================================
           BACKGROUND LAYERS - Fully Responsive
           ============================================================ */
        
        .login-container {
            position: relative;
            min-height: 100vh;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-sm);
            overflow-x: hidden;
        }

        .login-bg-image {
            position: absolute;
            inset: 0;
            z-index: 0;
            width: 100%;
            height: 100%;
            background-color: hsl(150, 30%, 40%);
        }

        .login-bg-image::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url('assets/src/feur.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            opacity: 1;
            z-index: 1;
        }

        .login-bg-image::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                90deg,
                hsl(152, 60%, 22%, 0.5) 0%,
                hsl(152, 50%, 28%, 0.4) 40%,
                hsl(45, 90%, 50%, 0.4) 80%,
                hsl(45, 90%, 55%, 0.5) 100%
            );
            z-index: 2;
        }

        /* ============================================================
           CONTENT WRAPPER - Responsive Layout
           ============================================================ */

        .login-content {
            position: relative;
            z-index: 3;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: min(100%, 480px);
            padding: 0;
        }

        /* ============================================================
           BRAND SECTION - Scalable Logo & Typography
           ============================================================ */

        .login-brand {
            margin-bottom: var(--spacing-lg);
            text-align: center;
            color: white;
            width: 100%;
        }

        .login-brand-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: clamp(100px, 20vw, 180px);
            aspect-ratio: 1;
            border-radius: 50%;
            background: transparent;
            margin-bottom: var(--spacing-md);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            flex-shrink: 0;
        }

        .login-brand-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .login-brand h1 {
            font-size: clamp(1.5rem, 5vw, 2.5rem);
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.5px;
            line-height: 1.2;
            word-break: break-word;
        }

        .login-brand h1 .text-gold {
            color: hsl(45, 90%, 55%);
        }

        .login-brand p {
            font-size: clamp(0.7rem, 2.5vw, 0.95rem);
            opacity: 0.95;
            margin-top: var(--spacing-sm);
            letter-spacing: 0.5px;
            line-height: 1.4;
            padding: 0 var(--spacing-xs);
        }

        /* ============================================================
           LOGIN CARD - Responsive Padding & Shadow
           ============================================================ */

        .login-card {
            background: rgba(255, 255, 255, 1);
            border-radius: clamp(12px, 3vw, 20px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            width: 100%;
            padding: clamp(1.5rem, 5vw, 2.5rem);
            margin-bottom: var(--spacing-md);
            border: 1px solid rgba(200, 220, 210, 0.4);
        }

        .login-card-title {
            font-size: clamp(1rem, 3vw, 1.4rem);
            font-weight: 700;
            color: hsl(152, 40%, 25%);
            margin-bottom: 0.25rem;
            line-height: 1.3;
        }

        .login-card-desc {
            font-size: clamp(0.8rem, 2vw, 1rem);
            color: hsl(150, 15%, 50%);
            margin-bottom: var(--spacing-md);
            line-height: 1.4;
        }

        /* ============================================================
           FORM ELEMENTS - Touch-Friendly (min 44px height)
           ============================================================ */

        .label {
            display: block;
            font-size: clamp(0.8rem, 2vw, 0.95rem);
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: hsl(150, 20%, 35%);
        }

        .input,
        .select {
            width: 100%;
            padding: clamp(0.65rem, 2vw, 0.85rem) clamp(0.75rem, 2vw, 1rem);
            min-height: 44px;
            border: 1px solid hsl(150, 20%, 85%);
            border-radius: clamp(6px, 2vw, 10px);
            font-size: clamp(0.8rem, 2vw, 0.95rem);
            background: hsl(150, 30%, 97%);
            color: hsl(150, 20%, 25%);
            outline: none;
            font-family: inherit;
            transition: all 0.2s ease;
            -webkit-appearance: none;
            appearance: none;
        }

        .input:focus,
        .select:focus {
            border-color: hsl(152, 60%, 50%);
            background: hsl(150, 25%, 98%);
            box-shadow: 0 0 0 3px hsl(152 60% 50% / 0.1);
        }

        /* ============================================================
           BUTTONS - Touch-Friendly with Responsive Sizing
           ============================================================ */

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: clamp(0.65rem, 2vw, 0.9rem) clamp(1rem, 3vw, 1.5rem);
            border-radius: clamp(6px, 2vw, 10px);
            font-size: clamp(0.8rem, 2vw, 0.95rem);
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            line-height: 1.5;
            gap: 0.5rem;
            white-space: nowrap;
        }

        .btn-primary {
            background: hsl(152, 55%, 40%);
            color: white;
        }

        .btn-primary:hover,
        .btn-primary:active {
            background: hsl(152, 55%, 45%);
            box-shadow: 0 4px 12px hsl(152 55% 40% / 0.25);
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            border: 1.5px solid hsl(150, 20%, 80%);
            color: hsl(150, 20%, 40%);
        }

        .btn-outline:hover,
        .btn-outline:active {
            background: hsl(150, 25%, 95%);
            border-color: hsl(152, 55%, 50%);
            color: hsl(152, 55%, 40%);
        }

        .btn-active {
            background: hsl(152, 55%, 40%);
            color: white;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* ============================================================
           TABS - Responsive Grid
           ============================================================ */

        .tabs-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            background: hsl(150, 20%, 92%);
            border-radius: clamp(6px, 2vw, 10px);
            padding: clamp(0.25rem, 1vw, 0.4rem);
            gap: clamp(0.2rem, 1vw, 0.4rem);
            margin-bottom: var(--spacing-md);
            width: 100%;
        }

        .tab-trigger {
            padding: clamp(0.5rem, 2vw, 0.7rem);
            min-height: 44px;
            border-radius: clamp(5px, 1.5vw, 8px);
            font-size: clamp(0.75rem, 2vw, 0.9rem);
            font-weight: 500;
            border: none;
            cursor: pointer;
            background: transparent;
            color: hsl(150, 15%, 50%);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .tab-trigger.active {
            background: white;
            color: hsl(152, 55%, 40%);
            box-shadow: 0 2px 6px hsl(152 55% 40% / 0.1);
        }

        /* ============================================================
           TEXT UTILITIES - Responsive Typography
           ============================================================ */

        .text-muted {
            color: hsl(150, 15%, 55%);
            font-size: clamp(0.75rem, 2vw, 0.85rem);
            line-height: 1.5;
        }

        .text-xs {
            font-size: clamp(0.7rem, 1.5vw, 0.8rem);
            line-height: 1.4;
        }

        .text-primary {
            color: hsl(152, 55%, 40%);
        }

        .text-gold {
            color: hsl(45, 90%, 55%);
        }

        /* ============================================================
           SPACING & LAYOUT UTILITIES
           ============================================================ */

        .space-y-3 > * + * {
            margin-top: var(--spacing-sm);
        }

        .space-y-4 > * + * {
            margin-top: var(--spacing-md);
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .gap-3 {
            gap: var(--spacing-sm);
        }

        .flex {
            display: flex;
        }

        .flex-col {
            flex-direction: column;
        }

        .grid {
            display: grid;
        }

        .grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .mb-2 {
            margin-bottom: 0.5rem;
        }

        .mb-3 {
            margin-bottom: 0.75rem;
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        .mt-2 {
            margin-top: 0.5rem;
        }

        .w-full {
            width: 100%;
        }

        .flex-1 {
            flex: 1;
            min-width: 0;
        }

        /* Footer text */
        .login-footer {
            position: relative;
            z-index: 3;
            font-size: clamp(0.65rem, 1.5vw, 0.8rem);
            color: rgba(255, 255, 255, 0.9);
            text-align: center;
            margin-top: var(--spacing-sm);
            padding: 0 var(--spacing-xs);
            line-height: 1.5;
        }

        /* ============================================================
           MOBILE BREAKPOINT: 320px - 480px
           ============================================================ */
        @media (max-width: 480px) {
            /* Show full image without cropping on mobile only */
            .login-bg-image::before {
                background-size: contain;
                background-attachment: scroll;
            }

            .login-container {
                padding: 0.75rem;
                justify-content: flex-start;
                padding-top: max(1rem, env(safe-area-inset-top));
                padding-bottom: max(1rem, env(safe-area-inset-bottom));
            }

            .login-content {
                margin-top: auto;
                margin-bottom: auto;
            }

            .login-brand {
                margin-bottom: 1.5rem;
            }

            .login-card {
                padding: 1.5rem;
                margin-bottom: 1rem;
            }

            .input,
            .select {
                font-size: 16px; /* Prevents zoom on iOS */
            }

            .btn {
                width: 100%;
            }

            .grid-cols-2 {
                grid-template-columns: 1fr;
            }

            a {
                word-break: break-word;
            }
        }

        /* ============================================================
           TABLET BREAKPOINT: 481px - 768px
           ============================================================ */
        @media (min-width: 481px) and (max-width: 768px) {
            .login-container {
                padding: 1rem;
            }

            .login-content {
                max-width: 100%;
            }

            .login-card {
                padding: 2rem;
            }

            .login-brand-icon {
                width: clamp(110px, 15vw, 130px);
            }
        }

        /* ============================================================
           iPad BREAKPOINT: 769px - 1024px
           ============================================================ */
        @media (min-width: 769px) and (max-width: 1024px) {
            .login-container {
                padding: 1.5rem;
            }

            .login-content {
                max-width: 520px;
            }

            .login-brand-icon {
                width: clamp(120px, 18vw, 160px);
            }
        }

        /* ============================================================
           DESKTOP BREAKPOINT: 1025px and above
           ============================================================ */
        @media (min-width: 1025px) {
            .login-container {
                padding: 2rem;
            }

            .login-content {
                max-width: 500px;
            }

            .login-brand-icon {
                width: 160px;
            }

            .btn:hover {
                transform: translateY(-2px);
            }

            /* Hover effects only on desktop */
            .input:hover,
            .select:hover {
                border-color: hsl(152, 60%, 45%);
            }

            .tab-trigger:hover:not(.active) {
                background: hsl(150, 25%, 96%);
            }
        }

        /* ============================================================
           LANDSCAPE ORIENTATION - Compact Layout
           ============================================================ */
        @media (orientation: landscape) and (max-height: 600px) {
            .login-brand {
                margin-bottom: 1rem;
            }

            .login-brand p {
                display: none;
            }

            .login-card {
                padding: 1.5rem;
            }
        }

        /* ============================================================
           DARK MODE SUPPORT
           ============================================================ */
        @media (prefers-color-scheme: dark) {
            .login-card {
                background: hsl(150, 20%, 20%);
                border-color: rgba(200, 220, 210, 0.2);
                color: hsl(150, 20%, 90%);
            }

            .login-card-title {
                color: hsl(150, 25%, 85%);
            }

            .login-card-desc {
                color: hsl(150, 15%, 70%);
            }

            .label {
                color: hsl(150, 20%, 75%);
            }

            .input,
            .select {
                border-color: hsl(150, 15%, 35%);
                background: hsl(150, 20%, 25%);
                color: hsl(150, 20%, 85%);
            }

            .input:focus,
            .select:focus {
                background: hsl(150, 20%, 28%);
            }

            .tabs-list {
                background: hsl(150, 15%, 30%);
            }

            .tab-trigger {
                color: hsl(150, 15%, 70%);
            }

            .tab-trigger.active {
                background: hsl(150, 20%, 25%);
                color: hsl(152, 60%, 60%);
            }

            .text-muted {
                color: hsl(150, 15%, 65%);
            }

            .btn-outline {
                border-color: hsl(150, 15%, 45%);
                color: hsl(150, 20%, 75%);
            }

            .btn-outline:hover {
                background: hsl(150, 20%, 30%);
            }
        }

        /* ============================================================
           ACCESSIBILITY & REDUCED MOTION
           ============================================================ */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* High contrast mode support */
        @media (prefers-contrast: more) {
            .btn,
            .input,
            .select {
                border-width: 2px;
            }

            .input:focus,
            .select:focus {
                outline: 3px solid hsl(152, 60%, 50%);
            }
        }
    </style>
</head>
<body>
    <!-- Background with image and gradient -->
    <div class="login-bg-image"></div>

    <!-- Content wrapper -->
    <div class="login-container">
        <div class="login-content">
            <!-- Brand section -->
            <div class="login-brand">
                <div class="login-brand-icon">
                    <img src="assets/src/logofeur-removebg-preview.png" alt="TapTrack Logo">
                </div>
                <h1>Tap<span class="text-gold">track</span></h1>
                <p>QR Code Attendance System — FEU Roosevelt Marikina</p>
            </div>

            <!-- Login Card -->
            <div class="login-card">
                <div class="login-card-title">Sign In</div>
                <div class="login-card-desc">Enter your credentials to continue</div>

                <!-- Unified Login Form - NO ROLE SELECTION -->
                <form method="POST" id="form-login" class="space-y-3">
                    <input type="hidden" name="action" value="unified_login">
                    <div>
                        <label class="label">Email</label>
                        <input class="input" type="email" name="email" placeholder="your.email@feuroosevelt.edu.ph" required>
                    </div>
                    <div>
                        <label class="label">Password</label>
                        <input class="input" type="password" name="password" placeholder="••••••" required>
                    </div>
                    <p class="text-xs text-muted">Enter your registered email and password to sign in. Admin users will be automatically detected.</p>
                    <button type="submit" class="btn btn-primary w-full mt-2">🔑 Sign In</button>
                    <p class="text-xs text-center text-muted mt-2">Don't have an account? <a href="#" class="text-primary" onclick="document.getElementById('form-login').style.display='none'; document.getElementById('form-register').style.display='block'; return false;" style="text-decoration:underline;">Register here</a></p>
                </form>

                <!-- Registration Form -->
                <form method="POST" id="form-register" class="space-y-3" style="display:none;">
                    <input type="hidden" name="action" value="student_register">
                    <div>
                        <label class="label">FEU Email</label>
                        <input class="input" type="email" name="email" placeholder="R20260101001@feuroosevelt.edu.ph" required>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="label">First Name</label><input class="input" name="first_name" placeholder="Juan" required></div>
                        <div><label class="label">Last Name</label><input class="input" name="last_name" placeholder="Dela Cruz" required></div>
                    </div>
                    <div>
                        <label class="label">Course / Program</label>
                        <select class="select" name="course" required>
                            <option value="">Select course...</option>
                            <?php foreach ($COURSES as $c): ?><option value="<?= htmlspecialchars($c, ENT_QUOTES) ?>"><?= htmlspecialchars($c, ENT_QUOTES) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="label">Year Level</label>
                        <select class="select" name="year_level" required>
                            <option value="">Select year level...</option>
                            <?php foreach ($YEAR_LEVELS as $y): ?><option value="<?= htmlspecialchars($y, ENT_QUOTES) ?>"><?= htmlspecialchars($y, ENT_QUOTES) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="label">Password</label>
                        <input class="input" type="password" name="password" placeholder="Create a password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-full mt-2">➕ Create Account</button>
                    <p class="text-xs text-center text-muted mt-2">Already have an account? <a href="#" class="text-primary" onclick="document.getElementById('form-login').style.display='block'; document.getElementById('form-register').style.display='none'; return false;" style="text-decoration:underline;">Sign in here</a></p>
                </form>
            </div>

            <!-- Footer -->
            <p class="login-footer">Demo System — No email verification required</p>
        </div>
    </div>

    <!-- JavaScript for form toggle -->
    <script>
        // Simple toggle handler for login/register links
        // Forms toggle via inline onclick handlers in login-card HTML
    </script>
</body>
</html>
</div>
