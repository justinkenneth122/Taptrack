<!-- Login Page --->
<div class="login-wrapper" style="position:relative;">
    <div style="position:absolute;inset:0;background:linear-gradient(135deg, hsla(145, 60%, 18%, 0.75) 0%, hsla(45, 85%, 45%, 0.65) 50%, hsla(145, 50%, 25%, 0.8) 100%);z-index:1;"></div>
    <div class="mb-8 text-center" style="position:relative;z-index:2;">
        <div style="display:inline-flex;align-items:center;gap:0.75rem;" class="mb-2">
            <div style="width:48px;height:48px;border-radius:8px;background:var(--primary);display:flex;align-items:center;justify-content:center;">
                <span style="font-size:1.5rem;color:var(--primary-foreground);">▣</span>
            </div>
            <h1 style="font-size:1.875rem;color:#fff;text-shadow:0 2px 4px rgba(0,0,0,0.2);" class="font-extrabold">Tap<span class="text-gold">track</span></h1>
        </div>
        <p class="text-sm" style="color:rgba(255,255,255,0.85);"><?php echo e($config['app']['description']); ?></p>
    </div>

    <div class="card shadow-lg max-w-md w-full" style="position:relative;z-index:2;">
        <div class="card-header">
            <div class="card-title" style="font-size:1.125rem;">Welcome</div>
            <p class="card-desc">Choose your role to continue</p>
        </div>
        <div class="card-content">
            <!-- Tabs -->
            <div class="tabs-list mb-4" id="login-tabs">
                <button class="tab-trigger active" onclick="switchTab('student')" id="tab-student">🎓 Student</button>
                <button class="tab-trigger" onclick="switchTab('admin')" id="tab-admin">🛡️ Admin</button>
            </div>

            <!-- Student Tab -->
            <div id="panel-student">
                <div class="flex gap-2 mb-4">
                    <button class="btn btn-active flex-1" id="btn-login" onclick="switchStudentMode('login')">🔑 Log In</button>
                    <button class="btn btn-outline flex-1" id="btn-register" onclick="switchStudentMode('register')">➕ Register</button>
                </div>

                <!-- Student Login Form -->
                <form method="POST" id="form-student-login" class="space-y-3">
                    <input type="hidden" name="action" value="student_login">
                    <div>
                        <label class="label">FEU Email</label>
                        <input class="input" name="email" placeholder="R20260101001@feuroosevelt.edu.ph" required>
                    </div>
                    <div>
                        <label class="label">Password</label>
                        <input class="input" type="password" name="password" placeholder="••••••" required>
                    </div>
                    <p class="text-xs text-muted">Enter your registered FEU email and password to sign in.</p>
                    <button type="submit" class="btn btn-primary w-full mt-2">🔑 Sign In</button>
                    <p class="text-xs text-center text-muted mt-2">Don't have an account? <a href="#" class="text-primary" onclick="switchStudentMode('register');return false;" style="text-decoration:underline;">Register here</a></p>
                </form>

                <!-- Student Register Form -->
                <form method="POST" id="form-student-register" class="space-y-3" style="display:none;">
                    <input type="hidden" name="action" value="student_register">
                    <div>
                        <label class="label">FEU Email</label>
                        <input class="input" name="email" placeholder="R20260101001@feuroosevelt.edu.ph" required>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="label">First Name</label><input class="input" name="first_name" placeholder="Juan" required></div>
                        <div><label class="label">Last Name</label><input class="input" name="last_name" placeholder="Dela Cruz" required></div>
                    </div>
                    <div>
                        <label class="label">Course / Program</label>
                        <select class="select" name="course" required>
                            <option value="">Select course...</option>
                            <?php foreach ($config['courses'] as $c): ?>
                            <option value="<?php echo e($c); ?>"><?php echo e($c); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="label">Year Level</label>
                        <select class="select" name="year_level" required>
                            <option value="">Select year level...</option>
                            <?php foreach ($config['year_levels'] as $y): ?>
                            <option value="<?php echo e($y); ?>"><?php echo e($y); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="label">Password</label>
                        <input class="input" type="password" name="password" placeholder="Create a password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-full mt-2">➕ Register</button>
                    <p class="text-xs text-center text-muted mt-2">Already have an account? <a href="#" class="text-primary" onclick="switchStudentMode('login');return false;" style="text-decoration:underline;">Sign in here</a></p>
                </form>
            </div>

            <!-- Admin Tab -->
            <div id="panel-admin" style="display:none;">
                <form method="POST" class="space-y-3">
                    <input type="hidden" name="action" value="admin_login">
                    <div><label class="label">Username</label><input class="input" name="username" placeholder="admin" required></div>
                    <div><label class="label">Password</label><input class="input" type="password" name="password" placeholder="••••••" required></div>
                    <p class="text-xs text-muted">Demo credentials: admin / admin123</p>
                    <button type="submit" class="btn btn-primary w-full mt-2">🛡️ Login as Admin</button>
                </form>
            </div>
        </div>
    </div>
    <p class="mt-4 text-xs text-muted" style="position:relative;z-index:2;">Demo System — No email verification required. Version <?php echo $config['app']['version']; ?></p>
</div>
