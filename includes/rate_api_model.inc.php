<?php
// ============================================================
//  includes/rate_api_model.inc.php
//  Fetches live USDT/NGN rate from Binance P2P API (Free, no key)
// ============================================================

function get_live_usdt_ngn_rate(): array {
    $url = "https://api.binance.com/bapi/p2p/v1/public/stable-coin/list?fiat=NGN";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second timeout
    curl_setopt($ch, CURLOPT_USERAGENT, 'Hydra-P2P-App/1.0');
    
    $response = curl_exec($ch);
    
    // If cURL fails (e.g., network error)
    if (curl_errno($ch)) {
        curl_close($ch);
        return ['price' => 0, 'success' => false];
    }
    
    curl_close($ch);

    $data = json_decode($response, true);

    // Find USDT in the response
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as $coin) {
            if (isset($coin['symbol']) && $coin['symbol'] === 'USDT') {
                return [
                    'price'   => (float) $coin['price'],
                    'success' => true
                ];
            }
        }
    }

    return ['price' => 0, 'success' => false];
}