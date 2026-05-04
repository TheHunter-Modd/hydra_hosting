<?php
// ============================================================
//  includes/auth_model.inc.php
//  Database queries for authentication.
//  No HTML. No logic. Returns raw data only.
// ============================================================

// Find a user by email OR phone number
function auth_get_user_by_credential(PDO $pdo, string $credential): array|false {
    $stmt = $pdo->prepare("
        SELECT id, username, email, phone, pwd, is_verified
        FROM   users
        WHERE  email = ? OR phone = ?
        LIMIT  1
    ");
    $stmt->execute([$credential, $credential]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Insert a new user on registration
function auth_create_user(PDO $pdo, string $username, string $email, string $phone, string $hashed_pwd): int {
    
    // Convert empty strings to NULL so UNIQUE constraints don't block new users
    $email = $email === '' ? null : $email;
    $phone = $phone === '' ? null : $phone;

    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, phone, pwd, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$username, $email, $phone, $hashed_pwd]);
    return (int) $pdo->lastInsertId('users_id_seq'); // Postgres sequence name
}

// Check if email already exists (ignore NULL/empty)
function auth_email_exists(PDO $pdo, string $email): bool {
    if (empty($email)) return false;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    return (bool) $stmt->fetch();
}

// Check if phone already exists (ignore NULL/empty)
function auth_phone_exists(PDO $pdo, string $phone): bool {
    if (empty($phone)) return false;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
    $stmt->execute([$phone]);
    return (bool) $stmt->fetch();
}