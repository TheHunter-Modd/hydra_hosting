<?php
// ============================================================
//  includes/calculator_model.inc.php
//
//  INPUTS  (user types these — 3 only):
//    $constant    — multiplier
//    $normal_rate — live market rate
//    $cost        — base Naira amount
//
//  COMPUTED (derived from the 3 inputs — never entered by user):
//    $quantity  = (cost * constant) / normal_rate        [BASE]
//    buy_rate   = (new_cost * constant) / quantity       [BUY]
//    sell_rate  = (new_cost * constant) / quantity       [SELL]
//    remainder  = profit (half-spread) — derived, not entered
// ============================================================


// ── BASE ──────────────────────────────────────────────────────
// Java: quantity = (cost * constant) / normal_rate
function calc_base(float $cost, float $constant, float $normal_rate): float {
    return ($cost * $constant) / $normal_rate;
}


// ── BUY ───────────────────────────────────────────────────────
// Java B operation — exact, unchanged:
//   new_cost = normal_rate + cost
//   buy_rate = (new_cost * constant) / quantity
//   profit   = (my_rate - normal_rate) / 2    → here my_rate = buy_rate
//   final_buy = my_rate - remainder            → here remainder = profit
function calc_buy(float $constant, float $normal_rate, float $cost, float $quantity): array {
    $new_cost  = $normal_rate + $cost;
    $buy_rate  = ($new_cost * $constant) / $quantity;   // this IS my_rate for buy
    $profit    = ($buy_rate - $normal_rate) / 2;        // remainder derived from buy_rate
    $final_buy = $buy_rate - $profit;                   // my_rate - remainder

    return [
        'new_cost'  => $new_cost,
        'buy_rate'  => $buy_rate,
        'profit'    => $profit,
        'final_buy' => $final_buy,
    ];
}


// ── SELL ──────────────────────────────────────────────────────
// Java S operation — exact, unchanged:
//   new_cost   = cost - normal_rate
//   sell_rate  = (new_cost * constant) / quantity   → this IS my_rate for sell
//   profit     = (normal_rate - my_rate) / 2        → remainder derived from sell_rate
//   final_sell = my_rate + remainder
function calc_sell(float $constant, float $normal_rate, float $cost, float $quantity): array {
    $new_cost   = $cost - $normal_rate;
    $sell_rate  = ($new_cost * $constant) / $quantity;  // this IS my_rate for sell
    $profit     = ($normal_rate - $sell_rate) / 2;      // remainder derived from sell_rate
    $final_sell = $sell_rate + $profit;                 // my_rate + remainder

    return [
        'new_cost'   => $new_cost,
        'sell_rate'  => $sell_rate,
        'profit'     => $profit,
        'final_sell' => $final_sell,
    ];
}
