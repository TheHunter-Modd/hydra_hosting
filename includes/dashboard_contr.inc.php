<?php
// ============================================================
//  includes/dashboard_contr.inc.php
//  Handles Quick Calculator POST request, validation & saving.
// ============================================================

require_once __DIR__ . '/dashboard_model.inc.php';

// Default state
 $calc_inputs   = [
    'constant'    => '1',
    'normal_rate' => '1374.22',
    'cost'        => '18000'
];
 $calc_mode     = 'buy';
 $calc_results  = null;
 $calc_errors   = [];

// Flash messages
 $success_msg = $_SESSION['success'] ?? null;
 $error_msg   = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// ── Handle SAVE POST (Save directly to DB) ───────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_quick_rate') {
    
    require_once __DIR__ . '/dbh.inc.php';
    require_once __DIR__ . '/rates_model.inc.php';
    
    $user_id     = (int) $_SESSION['user_id'];
    $constant    = (float) ($_POST['constant'] ?? 0);
    $normal_rate = (float) ($_POST['normal_rate'] ?? 0);
    $cost        = (float) ($_POST['cost'] ?? 0);

    if ($normal_rate > 0 && $cost > 0) {
        
        // Calculate both buy and sell (same as full calculator)
        $quantity    = calc_base($cost, $constant, $normal_rate);
        $buy_result  = calc_buy($constant, $normal_rate, $cost, $quantity);
        $sell_result = calc_sell($constant, $normal_rate, $cost, $quantity);

        // Calculate new_cost for both
        $buy_new_cost  = $normal_rate + $cost;
        $sell_new_cost = $cost - $normal_rate;

        // Save both rows to DB
        $buy_saved  = rates_save($pdo, $user_id, $buy_result['final_buy'], $normal_rate, $cost, $buy_new_cost, 'buy');
        $sell_saved = rates_save($pdo, $user_id, $sell_result['final_sell'], $normal_rate, $cost, $sell_new_cost, 'sell');

        if ($buy_saved && $sell_saved) {
            $_SESSION['success'] = 'Buy and Sell rates saved successfully!';
        } else {
            $_SESSION['error'] = 'Failed to save rates.';
        }
    } else {
        $_SESSION['error'] = 'Cannot save: enter valid rate and cost.';
    }

    // Redirect back to dashboard
    header('Location: dashboard.php');
    exit();
}

// ── Handle CALCULATE POST ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_calc') {
    
    // Grab inputs
    $calc_inputs = [
        'constant'    => $_POST['constant']    ?? '',
        'normal_rate' => $_POST['normal_rate'] ?? '',
        'cost'        => $_POST['cost']        ?? '',
    ];
    
    // Determine mode (from the toggle buttons)
    $calc_mode = ($_POST['mode'] ?? 'buy') === 'sell' ? 'sell' : 'buy';

    // Validate
    foreach ($calc_inputs as $field => $value) {
        if ($value === '' || !is_numeric($value)) {
            $calc_errors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be a valid number.';
        }
    }

    if (empty($calc_errors) && (float)$calc_inputs['normal_rate'] === 0.0) {
        $calc_errors[] = 'Normal rate cannot be zero.';
    }

    // Calculate via Model
    if (empty($calc_errors)) {
        $calc_results = get_quick_calc_results(
            (float) $calc_inputs['constant'],
            (float) $calc_inputs['normal_rate'],
            (float) $calc_inputs['cost'],
            $calc_mode
        );
    }
}