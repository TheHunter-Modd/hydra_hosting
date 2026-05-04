<?php
// ============================================================
//  includes/dashboard_model.inc.php
//  Reuses calculator_model.inc.php to keep logic DRY.
// ============================================================

require_once __DIR__ . '/calculator_model.inc.php';

/**
 * Calculate quick results based on mode
 * Returns formatted array for the view
 */
function get_quick_calc_results(float $constant, float $normal_rate, float $cost, string $mode): array 
{
    // Base calculation (same for both)
    $quantity = calc_base($cost, $constant, $normal_rate);

    if ($mode === 'buy') {
        $calc = calc_buy($constant, $normal_rate, $cost, $quantity);
        
        return [
            'mode'         => 'buy',
            'quantity'     => $quantity,
            'new_cost'     => $calc['new_cost'],
            'rate'         => $calc['buy_rate'],
            'profit'       => $calc['profit'],
            'final'        => $calc['final_buy'],
            'final_label'  => 'Final Buy'
        ];
    } else {
        $calc = calc_sell($constant, $normal_rate, $cost, $quantity);
        
        return [
            'mode'         => 'sell',
            'quantity'     => $quantity,
            'new_cost'     => $calc['new_cost'],
            'rate'         => $calc['sell_rate'],
            'profit'       => $calc['profit'],
            'final'        => $calc['final_sell'],
            'final_label'  => 'Final Sell'
        ];
    }
}