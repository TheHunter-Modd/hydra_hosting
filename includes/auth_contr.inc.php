<?php
// ============================================================
//  includes/auth_contr.inc.php
//  Handles LOGIN and REGISTER form submissions.
//  Requires: config_session, dbh, auth_model, auth_view
// ============================================================

// __DIR__ = the includes/ folder, so we step up one level for config
require_once __DIR__ . '/config_session.inc.php';
require_once __DIR__ . '/dbh.inc.php';
require_once __DIR__ . '/auth_model.inc.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit();
}

$action = $_POST['action'] ?? '';

// ── LOGIN ─────────────────────────────────────────────────────
if ($action === 'login') {
    $credential = trim($_POST['credential'] ?? '');
    $password   = $_POST['password'] ?? '';

    $errors = [];

    if (empty($credential)) $errors[] = 'Email or phone number is required.';
    if (empty($password))   $errors[] = 'Password is required.';

    if (empty($errors)) {
        try {
            $user = auth_get_user_by_credential($pdo, $credential);

            if (!$user || !password_verify($password, $user['pwd'])) {
                $errors[] = 'Invalid credentials. Please check and try again.';
            } else {
                // Set session
                $_SESSION['user_id']       = $user['id'];
                $_SESSION['user_name']     = $user['username'];
                $_SESSION['user_email']    = $user['email'];
                header('Location: ../dashboard.php');
                exit();
            }
        } catch (PDOException $e) {
            error_log('[Hydra Login] ' . $e->getMessage());
            $errors[] = 'Something went wrong. Please try again.';
        }
    }

    if (!empty($errors)) {
        $_SESSION['auth_errors'] = $errors;
        $_SESSION['active_tab']  = 'login';
        header('Location: ../index.php');
        exit();
    }
}

// ── REGISTER ──────────────────────────────────────────────────
if ($action === 'register') {
    $credential = trim($_POST['credential'] ?? '');
    $password   = $_POST['password'] ?? '';

    $errors = [];

    if (empty($credential)) $errors[] = 'Email or phone number is required.';
    if (empty($password))   $errors[] = 'Password is required.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';

    // Determine if credential is email or phone
    $is_email = filter_var($credential, FILTER_VALIDATE_EMAIL);
    $email    = $is_email ? $credential : '';
    $phone    = !$is_email ? $credential : '';

    if (empty($errors)) {
        try {
            if ($email && auth_email_exists($pdo, $email)) {
                $errors[] = 'An account with this email already exists.';
            } elseif ($phone && auth_phone_exists($pdo, $phone)) {
                $errors[] = 'An account with this phone number already exists.';
            } else {
                // Derive a username from email/phone
                $username   = $email ? explode('@', $email)[0] : 'user_' . substr($phone, -4);
                $hashed_pwd = password_hash($password, PASSWORD_BCRYPT);

                auth_create_user($pdo, $username, $email, $phone, $hashed_pwd);

                $_SESSION['auth_success'] = 'Account created! Please log in.';
                $_SESSION['active_tab']   = 'login';
                header('Location: ../index.php');
                exit();
            }
        } catch (PDOException $e) {
            error_log('[Hydra Register] ' . $e->getMessage());
            $errors[] = 'Something went wrong. Please try again.';
        }
    }

    if (!empty($errors)) {
        $_SESSION['auth_errors'] = $errors;
        $_SESSION['active_tab']  = 'register';
        header('Location: ../index.php');
        exit();
    }
}

// Fallback
header('Location: ../index.php');
exit();
