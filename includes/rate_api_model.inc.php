<?php
// ============================================================
//  includes/rate_api_model.inc.php
//  Fetches live USDT/NGN rate from CoinGecko (Free, no key)
//  Falls back to a default rate if the API fails.
// ============================================================

function get_live_usdt_ngn_rate(): array {
    // CoinGecko API (Cloud-friendly, no bot blocking)
    $url = "https://api.coingecko.com/api/v3/simple/price?ids=tether&vs_currencies=ngn";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Hydra-P2P-App/1.0');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        curl_close($ch);
        return ['price' => 0, 'success' => false];
    }
    
    curl_close($ch);

    $data = json_decode($response, true);

    // CoinGecko returns: {"tether":{"ngn":1650.42}}
    if (isset($data['tether']['ngn']) && $data['tether']['ngn'] > 0) {
        return [
            'price'   => (float) $data['tether']['ngn'],
            'success' => true
        ];
    }

    return ['price' => 0, 'success' => false];
}