<?php
// ============================================================
//  includes/rates_model.inc.php
//  Responsibility: ALL database reads/writes for My Rates.
//  Returns raw rows only. No calculations. No HTML.
//
//  DB columns (exact — do NOT assume extras):
//    id, user_id, rate, mode, saved_at
// ============================================================


// ── Fetch all rates for the logged-in user ────────────────────
function rates_get_all(PDO $pdo, int $user_id): array {
    $stmt = $pdo->prepare("
        SELECT id, rate, mode, saved_at
        FROM   rates
        WHERE  user_id = ?
        ORDER  BY saved_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// ── Delete one rate row (user_id guard prevents cross-deletion) ─
function rates_delete(PDO $pdo, int $id, int $user_id): bool {
    $stmt = $pdo->prepare("
        DELETE FROM rates
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$id, $user_id]);
    return $stmt->rowCount() > 0;
}