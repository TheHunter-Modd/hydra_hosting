<?php
require_once 'includes/config_session.inc.php';
require_once 'includes/auth_view.inc.php';

// Already logged in → go to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Which tab is active? (set by controller after error/success)
$active_tab = $_SESSION['active_tab'] ?? 'login';
unset($_SESSION['active_tab']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hydra — P2P Crypto Trading</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>

<div class="auth-page">

    <!-- ── Branding ─────────────────────────────────────── -->
    <div class="auth-brand">
        <div class="auth-brand__logo">H</div>
        <div class="auth-brand__name">Hydra</div>
        <div class="auth-brand__tagline">P2P Crypto Trading Platform</div>
    </div>

    <!-- ── Card ─────────────────────────────────────────── -->
    <div class="auth-card">

        <!-- Tab toggle -->
        <div class="auth-tabs">
            <button class="auth-tab <?= $active_tab === 'login'    ? 'active' : '' ?>"
                    onclick="switchTab('login')">Login</button>
            <button class="auth-tab <?= $active_tab === 'register' ? 'active' : '' ?>"
                    onclick="switchTab('register')">Sign Up</button>
        </div>

        <!-- Session messages -->
        <?php render_auth_errors(); ?>
        <?php render_auth_success(); ?>

        <!-- ── LOGIN FORM ──────────────────────────────── -->
        <div class="auth-form-section <?= $active_tab === 'login' ? 'active' : '' ?>" id="tab-login">
            <form action="includes/auth_contr.inc.php" method="post">
                <input type="hidden" name="action" value="login">

                <div class="form-group">
                    <label for="login-credential">Email or Phone Number</label>
                    <div class="input-wrap">
                        <!-- Mail icon -->
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="m2 7 10 7 10-7"/>
                        </svg>
                        <input type="text" id="login-credential" name="credential"
                               placeholder="trader@example.com or +234..." autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label for="login-password">Password</label>
                    <div class="input-wrap">
                        <!-- Lock icon -->
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input type="password" id="login-password" name="password"
                               placeholder="Enter your password" autocomplete="current-password">
                        <button type="button" class="toggle-pw" onclick="togglePw('login-password', this)" aria-label="Show password">
                            <svg id="eye-login" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="auth-meta">
                    <label class="remember-wrap">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn-primary">Login</button>
            </form>

            <!-- Social -->
            <div class="auth-divider">or continue with</div>
            <div class="social-row">
                <button class="btn-social btn-social--google" type="button">
                    <!-- Google G SVG -->
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path class="g-blue" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path class="g-green" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path class="g-yellow" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path class="g-red" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Google
                </button>
                <button class="btn-social" type="button">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="2" width="14" height="20" rx="2"/>
                        <path d="M12 18h.01"/>
                    </svg>
                    Biometric
                </button>
            </div>

            <div class="auth-security">🔒 Secured with 256-bit encryption • Your data is safe</div>
        </div>

        <!-- ── REGISTER FORM ───────────────────────────── -->
        <div class="auth-form-section <?= $active_tab === 'register' ? 'active' : '' ?>" id="tab-register">
            <form action="includes/auth_contr.inc.php" method="post">
                <input type="hidden" name="action" value="register">

                <div class="form-group">
                    <label for="reg-credential">Email or Phone Number</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="m2 7 10 7 10-7"/>
                        </svg>
                        <input type="text" id="reg-credential" name="credential"
                               placeholder="trader@example.com or +234..." autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label for="reg-password">Password</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input type="password" id="reg-password" name="password"
                               placeholder="Enter your password" autocomplete="new-password">
                        <button type="button" class="toggle-pw" onclick="togglePw('reg-password', this)" aria-label="Show password">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="margin-bottom: 20px;">Create Account</button>
            </form>

            <div class="auth-divider">or continue with</div>
            <div class="social-row">
                <button class="btn-social btn-social--google" type="button">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path class="g-blue" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path class="g-green" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path class="g-yellow" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path class="g-red" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Google
                </button>
                <button class="btn-social" type="button">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="2" width="14" height="20" rx="2"/>
                        <path d="M12 18h.01"/>
                    </svg>
                    Biometric
                </button>
            </div>

            <div class="auth-security">🔒 Secured with 256-bit encryption • Your data is safe</div>
        </div>

    </div><!-- /auth-card -->

    <!-- Footer -->
    <div class="auth-footer">
        By continuing, you agree to our
        <a href="#">Terms of Service</a> and
        <a href="#">Privacy Policy</a>
    </div>

</div><!-- /auth-page -->

<script>
function switchTab(tab) {
    document.querySelectorAll('.auth-tab').forEach((btn, i) => {
        btn.classList.toggle('active', (i === 0 && tab === 'login') || (i === 1 && tab === 'register'));
    });
    document.getElementById('tab-login').classList.toggle('active', tab === 'login');
    document.getElementById('tab-register').classList.toggle('active', tab === 'register');
}

function togglePw(inputId, btn) {
    const input = document.getElementById(inputId);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    // Swap eye icon
    btn.querySelector('svg').style.opacity = isText ? '0.5' : '1';
}
</script>

</body>
</html>
