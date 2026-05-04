<?php
// ============================================================
//  includes/rates_contr.inc.php
//
//  COLUMN LEGEND:
//  ─────────────────────────────────────────────────────────
//  FROM DB:    id, rate (normal_rate), mode, saved_at
//  MOCKED:     amount, payment_method, status
//  CALCULATED: quantity, buy_sell_rate, profit, margin
//              (exact same formulas as calculator_model.inc.php)
// ============================================================

require_once __DIR__ . '/config_session.inc.php';
require_once __DIR__ . '/dbh.inc.php';
require_once __DIR__ . '/rates_model.inc.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// ── Handle delete POST ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    rates_delete($pdo, (int) $_POST['delete_id'], $user_id);
    header('Location: ../rates.php');
    exit();
}

// ── Fetch raw rows from DB ────────────────────────────────────
$raw_rows = rates_get_all($pdo, $user_id);

// ── Mock data pools (stable per row index) ────────────────────
$mock_payments = ['Bank Transfer', 'Opay', 'Palmpay', 'Kuda', 'GTBank'];
$mock_statuses = ['active', 'active', 'active', 'archived']; // ~75% active
$mock_amounts  = [50000, 100000, 25000, 75000, 200000, 150000, 30000, 80000];

// Calculator constant (neutral — update when DB stores it)
$CONSTANT = 1.0;

// ── Enrich each row ───────────────────────────────────────────
$rows = [];

foreach ($raw_rows as $i => $row) {

    // ── FROM DB ───────────────────────────────────────────────
    $id         = (int)   $row['id'];
    $rate       = (float) $row['rate'];   // = normal_rate
    $mode       = $row['mode'];
    $saved_at   = $row['saved_at'];

    // ── MOCKED ────────────────────────────────────────────────
    // Deterministic so values don't change on refresh
    $amount  = $mock_amounts[$id % count($mock_amounts)];
    $payment = $mock_payments[$i % count($mock_payments)];
    $status  = $mock_statuses[$i % count($mock_statuses)];

    // ── CALCULATED ────────────────────────────────────────────
    // Base — Java: quantity = (cost * constant) / normal_rate
    $quantity = ($rate > 0) ? ($amount * $CONSTANT) / $rate : 0;

    if ($mode === 'buy') {
        // Java B:
        //   new_cost  = normal_rate + cost
        //   buy_rate  = (new_cost * constant) / quantity
        //   profit    = (buy_rate - normal_rate) / 2
        //   final_buy = buy_rate - profit
        $new_cost      = $rate + $amount;
        $raw_rate      = ($quantity > 0) ? ($new_cost * $CONSTANT) / $quantity : 0;
        $profit        = ($raw_rate - $rate) / 2;
        $display_rate  = $raw_rate - $profit;   // final_buy

    } else {
        // Java S:
        //   new_cost   = cost - normal_rate
        //   sell_rate  = (new_cost * constant) / quantity
        //   profit     = (normal_rate - sell_rate) / 2
        //   final_sell = sell_rate + profit
        $new_cost      = $amount - $rate;
        $raw_rate      = ($quantity > 0) ? ($new_cost * $CONSTANT) / $quantity : 0;
        $profit        = ($rate - $raw_rate) / 2;
        $display_rate  = $raw_rate + $profit;   // final_sell
    }

    // Profit margin %
    $margin = ($amount > 0) ? abs(($profit / $amount) * 100) : 0;

    $rows[] = [
        // DB
        'id'           => $id,
        'rate'         => $rate,
        'mode'         => $mode,
        'saved_at'     => $saved_at,
        // Mocked
        'amount'       => $amount,
        'payment'      => $payment,
        'status'       => $status,
        // Calculated
        'quantity'     => round($quantity,    2),
        'display_rate' => round($display_rate, 2),
        'profit'       => round($profit,      2),
        'margin'       => round($margin,      4),
    ];
}

// ── Filter + Search ───────────────────────────────────────────
$filter = $_GET['filter'] ?? 'all';
$search = strtolower(trim($_GET['search'] ?? ''));

$filtered = array_values(array_filter($rows, function ($r) use ($filter, $search) {

    if ($filter === 'buy'      && $r['mode']   !== 'buy')      return false;
    if ($filter === 'sell'     && $r['mode']   !== 'sell')     return false;
    if ($filter === 'active'   && $r['status'] !== 'active')   return false;
    if ($filter === 'archived' && $r['status'] !== 'archived') return false;

    if ($search !== '') {
        $haystack = strtolower($r['amount'] . $r['rate'] . $r['payment'] . $r['mode']);
        if (strpos($haystack, $search) === false) return false;
    }

    return true;
}));

// ── Stats cards (computed from ALL rows, not filtered) ────────
$total_saved   = count($rows);
$active_count  = count(array_filter($rows, fn($r) => $r['status'] === 'active'));
$total_profit  = array_sum(array_column($rows, 'profit'));
$avg_margin    = $total_saved > 0
    ? array_sum(array_column($rows, 'margin')) / $total_saved
    : 0;