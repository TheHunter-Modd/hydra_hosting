<?php
// ============================================================
//  includes/calculator_contr.inc.php
//  Reads 3 form inputs, validates, runs all calculations.
//  Exposes $results and $errors to calculator.php.
// ============================================================

require_once __DIR__ . '/calculator_model.inc.php';

$errors  = [];
$results = null;

$inputs = [
    'constant'    => '',
    'normal_rate' => '',
    'cost'        => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Read the 3 inputs
    $inputs = [
        'constant'    => $_POST['constant']    ?? '',
        'normal_rate' => $_POST['normal_rate'] ?? '',
        'cost'        => $_POST['cost']        ?? '',
    ];

    // Validate
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

        // Step 1 — base
        $quantity = calc_base($cost, $constant, $normal_rate);

        // Step 2 — buy and sell simultaneously
        $buy  = calc_buy($constant,  $normal_rate, $cost, $quantity);
        $sell = calc_sell($constant, $normal_rate, $cost, $quantity);

        $results = [
            'quantity' => $quantity,
            'buy'      => $buy,
            'sell'     => $sell,
        ];
    }
}
