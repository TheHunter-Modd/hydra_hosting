<?php
// ============================================================
//  includes/config_session.inc.php
//  Starts session and sets base config for Hydra P2P
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'hydra_p2p_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('APP_NAME', 'Hydra');
define('APP_TAGLINE', 'P2P Crypto Trading Platform');
