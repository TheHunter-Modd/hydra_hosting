<?php
// ============================================================
//  includes/rates_model.inc.php
//  DB columns: id, user_id, rate, normal_rate, cost, new_cost, mode, saved_at
// ============================================================


// ── Fetch all rates ───────────────────────────────────────────
function rates_get_all(PDO $pdo, int $user_id): array {
    $stmt = $pdo->prepare("
        SELECT id, rate, normal_rate, cost, new_cost, mode, saved_at
        FROM   rates
        WHERE  user_id = ?
        ORDER  BY saved_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// ── Delete one rate row ───────────────────────────────────────
function rates_delete(PDO $pdo, int $id, int $user_id): bool {
    $stmt = $pdo->prepare("
        DELETE FROM rates
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$id, $user_id]);
    return $stmt->rowCount() > 0;
}


// ── Save rate (includes new_cost) ─────────────────────────────
function rates_save(PDO $pdo, int $user_id, float $final_rate, float $normal_rate, float $cost, float $new_cost, string $mode): bool {
    $stmt = $pdo->prepare("
        INSERT INTO rates (user_id, rate, normal_rate, cost, new_cost, mode, saved_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    return $stmt->execute([$user_id, $final_rate, $normal_rate, $cost, $new_cost, $mode]);
}