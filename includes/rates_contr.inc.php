<?php
// ============================================================
//  includes/rates_contr.inc.php
//
//  DB COLUMNS:
//    rate        = final_buy (buy) or final_sell (sell)
//    normal_rate = original normal_rate from calculator
//    cost        = original cost from calculator (for quantity calc)
//    new_cost    = calculated new_cost (for Amount display)
//    mode, saved_at
//
//  TABLE DISPLAY:
//    Amount      = new_cost (FROM DB - no calculation needed)
//    Rate        = normal_rate (FROM DB)
//    Buy/Sell Rate = rate (FROM DB - already calculated)
//    USDT Qty    = (cost × constant) / normal_rate (CALCULATED)
//    Profit      = formula using quantity, rate, normal_rate
// ============================================================

require_once __DIR__ . '/config_session.inc.php';
require_once __DIR__ . '/dbh.inc.php';
require_once __DIR__ . '/rates_model.inc.php';
require_once __DIR__ . '/calculator_model.inc.php';

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

// ── Mock pools ────────────────────────────────────────────────
 $mock_payments = ['Bank Transfer', 'Opay', 'Palmpay', 'Kuda', 'GTBank'];
 $mock_statuses = ['active', 'active', 'active', 'archived'];
 $mock_constant = 1.0;

// ── Enrich each row ───────────────────────────────────────────
 $rows = [];

foreach ($raw_rows as $i => $row) {

    // ════════════════════════════════════════════════════════════
    // FROM DATABASE (all values come from DB now)
    // ════════════════════════════════════════════════════════════
    $id          = (int)   $row['id'];
    $final_rate  = (float) $row['rate'];         // final_buy or final_sell
    $normal_rate = (float) $row['normal_rate'];  // original normal_rate
    $cost        = (float) $row['cost'];         // original cost
    $new_cost    = (float) $row['new_cost'];     // calculated new_cost
    $mode        = $row['mode'];
    $saved_at    = $row['saved_at'];

    // ════════════════════════════════════════════════════════════
    // MOCKED
    // ════════════════════════════════════════════════════════════
    $constant = $mock_constant;
    $payment  = $mock_payments[$i % count($mock_payments)];
    $status   = $mock_statuses[$i % count($mock_statuses)];

    // ════════════════════════════════════════════════════════════
    // CALCULATED — quantity (uses ORIGINAL cost)
    // ════════════════════════════════════════════════════════════
    $quantity = ($normal_rate > 0) ? ($cost * $constant) / $normal_rate : 0;

    // ════════════════════════════════════════════════════════════
    // CALCULATED — profit
    // ════════════════════════════════════════════════════════════
    if ($mode === 'buy') {
        // BUY: profit = (usdt_qty × final_buy) - (usdt_qty × normal_rate)
        $profit = ($quantity * $final_rate) - ($quantity * $normal_rate);
    } else {
        // SELL: profit = (usdt_qty × normal_rate) - (usdt_qty × final_sell)
        $profit = ($quantity * $normal_rate) - ($quantity * $final_rate);
    }

    // Profit margin % (based on new_cost)
    $margin = ($new_cost > 0) ? ($profit / $new_cost) * 100 : 0;

    // ════════════════════════════════════════════════════════════
    // BUILD ROW
    // ════════════════════════════════════════════════════════════
    $rows[] = [
        // FROM DB (direct display - no calculation)
        'id'           => $id,
        'rate'         => $final_rate,     // final_buy or final_sell
        'normal_rate'  => $normal_rate,    // original normal_rate
        'amount'       => $new_cost,       // new_cost FROM DB
        'mode'         => $mode,
        'saved_at'     => $saved_at,
        // CALCULATED
        'quantity'     => round($quantity,  4),
        'profit'       => round($profit,    2),
        'margin'       => round($margin,    4),
        // MOCKED
        'payment'      => $payment,
        'status'       => $status,
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
        $haystack = strtolower($r['amount'] . $r['normal_rate'] . $r['rate'] . $r['payment'] . $r['mode']);
        if (strpos($haystack, $search) === false) return false;
    }

    return true;
}));

// ── Stats ─────────────────────────────────────────────────────
 $total_saved   = count($rows);
 $active_count  = count(array_filter($rows, fn($r) => $r['status'] === 'active'));
 $total_profit  = array_sum(array_column($rows, 'profit'));
 $avg_margin    = $total_saved > 0
    ? array_sum(array_column($rows, 'margin')) / $total_saved
    : 0;


// ════════════════════════════════════════════════════════════════
// CSV EXPORT (respects current filter + search)
// ════════════════════════════════════════════════════════════════
if (isset($_GET['export']) && $_GET['export'] === 'csv') {

    // Headers to force browser download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="hydra_rates_' . date('Y-m-d_His') . '.csv"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');

    // Open output stream
    $output = fopen('php://output', 'w');

    // BOM for Excel to recognize UTF-8
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Column headers (match table display)
    fputcsv($output, [
        'Date & Time',
        'Type',
        'Amount (NGN)',
        'Normal Rate (NGN)',
        'USDT Quantity',
        'Buy/Sell Rate (NGN)',
        'Profit (NGN)',
        'Payment Method',
        'Status',
    ]);

    // Data rows
    foreach ($filtered as $row) {
        fputcsv($output, [
            date('Y-m-d H:i', strtotime($row['saved_at'])),
            strtoupper($row['mode']),
            number_format($row['amount'], 2, '.', ''),
            number_format($row['normal_rate'], 2, '.', ''),
            number_format($row['quantity'], 4, '.', ''),
            number_format($row['rate'], 2, '.', ''),
            number_format($row['profit'], 2, '.', ''),
            $row['payment'],
            ucfirst($row['status']),
        ]);
    }

    fclose($output);
    exit(); // Stop — don't render HTML
}