<?php
// ============================================================
//  includes/calculator_contr.inc.php
//  Saves: final_buy/final_sell, normal_rate, cost, new_cost
// ============================================================

require_once __DIR__ . '/calculator_model.inc.php';
require_once __DIR__ . '/dbh.inc.php';
require_once __DIR__ . '/rates_model.inc.php';

 $errors       = [];
 $results      = null;
 $save_success = false;

 $inputs = [
    'constant'    => '',
    'normal_rate' => '',
    'cost'        => '',
];

// ── Handle SAVE POST ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_rate') {
    
    $user_id = (int) $_SESSION['user_id'];
    
    // Restore inputs
    $inputs = [
        'constant'    => $_POST['constant']    ?? '',
        'normal_rate' => $_POST['normal_rate'] ?? '',
        'cost'        => $_POST['cost']        ?? '',
    ];
    
    $constant    = (float) $inputs['constant'];
    $normal_rate = (float) $inputs['normal_rate'];
    $cost        = (float) $inputs['cost'];
    
    // Validate
    if ($normal_rate <= 0 || $cost <= 0) {
        $errors[] = 'Cannot save: rate and cost must be greater than zero.';
    } else {
        
        // Calculate all values
        $quantity    = calc_base($cost, $constant, $normal_rate);
        $buy_result  = calc_buy($constant, $normal_rate, $cost, $quantity);
        $sell_result = calc_sell($constant, $normal_rate, $cost, $quantity);
        
        // ════════════════════════════════════════════════════════
        // CALCULATE new_cost FOR EACH MODE
        // ════════════════════════════════════════════════════════
        $buy_new_cost  = $normal_rate + $cost;   // BUY: new_cost = normal_rate + cost
        $sell_new_cost = $cost - $normal_rate;   // SELL: new_cost = cost - normal_rate
        
        // ════════════════════════════════════════════════════════
        // SAVE BOTH ROWS WITH THEIR RESPECTIVE new_cost
        // ════════════════════════════════════════════════════════
        $buy_saved  = rates_save(
            $pdo, $user_id,
            $buy_result['final_buy'],    // rate = final_buy
            $normal_rate,                // normal_rate = original
            $cost,                       // cost = original (for quantity calc)
            $buy_new_cost,               // new_cost = calculated
            'buy'
        );
        
        $sell_saved = rates_save(
            $pdo, $user_id,
            $sell_result['final_sell'],  // rate = final_sell
            $normal_rate,                // normal_rate = original
            $cost,                       // cost = original (for quantity calc)
            $sell_new_cost,              // new_cost = calculated
            'sell'
        );
        
        if ($buy_saved && $sell_saved) {
            $save_success = true;
            
            $results = [
                'quantity' => $quantity,
                'buy'      => $buy_result,
                'sell'     => $sell_result,
            ];
        } else {
            $errors[] = 'Failed to save rates. Please try again.';
        }
    }
}

// ── Handle CALCULATE POST ────────────────────────────────────
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $inputs = [
        'constant'    => $_POST['constant']    ?? '',
        'normal_rate' => $_POST['normal_rate'] ?? '',
        'cost'        => $_POST['cost']        ?? '',
    ];

    foreach ($inputs as $field => $value) {
        if ($value === '' || !is_numeric($value)) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required and must be a number.';
        }
    }

    if (empty($errors) && (float)$inputs['normal_rate'] === 0.0) {
        $errors[] = 'Normal rate cannot be zero.';
    }

    if (empty($errors)) {
        $constant    = (float) $inputs['constant'];
        $normal_rate = (float) $inputs['normal_rate'];
        $cost        = (float) $inputs['cost'];

        $quantity = calc_base($cost, $constant, $normal_rate);
        $buy  = calc_buy($constant,  $normal_rate, $cost, $quantity);
        $sell = calc_sell($constant, $normal_rate, $cost, $quantity);

        $results = [
            'quantity' => $quantity,
            'buy'      => $buy,
            'sell'     => $sell,
        ];
    }
}