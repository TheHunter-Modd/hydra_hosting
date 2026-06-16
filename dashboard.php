<?php
require_once 'includes/config_session.inc.php';
require_once 'includes/dashboard_contr.inc.php';

// Auth guard
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// ── Demo / session data ────────────────────────────────────────
// In production these come from the DB via a model.
// For now we use sensible demo values so the page always renders.
$user_name  = htmlspecialchars($_SESSION['user_name'] ?? 'Trader');
$user_abbr  = strtoupper(substr($user_name, 0, 2));

// ── Live Rate Fetching (Cached for 10 minutes) ──────────────
 $live_rate       = '₦1,685'; // Default fallback
 $rate_change     = '+0.0%';  // Default fallback
 $rate_fetched_at = $_SESSION['live_rate_time'] ?? 0;

// If we don't have a rate, or it's older than 10 minutes (600 seconds), fetch new one
if (!$rate_fetched_at || (time() - $rate_fetched_at) > 600) {
    require_once 'includes/rate_api_model.inc.php';
    $api_result = get_live_usdt_ngn_rate();
    
    if ($api_result['success'] && $api_result['price'] > 0) {
        $old_rate = $_SESSION['live_rate_value'] ?? $api_result['price'];
        
        // Calculate percentage change
        if ($old_rate > 0) {
            $change = (($api_result['price'] - $old_rate) / $old_rate) * 100;
            $rate_change = ($change >= 0 ? '+' : '') . number_format($change, 1) . '%';
        }
        
        // Save to session
        $_SESSION['live_rate_value'] = $api_result['price'];
        $_SESSION['live_rate_time']  = time();
        
        $live_rate = '₦' . number_format($api_result['price'], 2);
    }
} else {
    // We have a cached rate, use it
    $cached_price = $_SESSION['live_rate_value'] ?? 1685;
    $live_rate = '₦' . number_format($cached_price, 2);
}

$total_trades    = '92';
$trades_change   = '+12%';
$success_rate    = '75%';
$success_change  = '+5%';
$total_volume    = '₦315K';
$volume_change   = '+8%';

// Trader card stats
$recent_activity = '6.1h';
$completed_trades = '45';
$trader_success  = '98%';

// Progress bar chart heights (%)
$bars = [55, 70, 45, 80, 60, 90, 85];

// Time tracker
$tracker_time    = '03:45';
$tracker_today   = 'Today';
$onboarding_pct  = 42;

 $last_updated = 'Just now';

// Helper function for safe HTML output
function qc_val(array $arr, string $key): string {
    return htmlspecialchars($arr[$key] ?? '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Hydra P2P</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

<div class="app">

    <!-- ════════════════════════════════════════════════════
         SIDEBAR
    ════════════════════════════════════════════════════ -->
    <aside class="sidebar">

        <!-- Brand -->
        <div class="sidebar__brand">
            <div class="sidebar__logo">H</div>
            <div class="sidebar__brand-text">
                <div class="brand-name">Hydra</div>
                <div class="brand-sub">P2P Rate</div>
            </div>
        </div>

        <!-- Live rate -->
        <div class="sidebar__rate">
            <div class="rate-label">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
                    <polyline points="16 7 22 7 22 13"/>
                </svg>
                Live USDT Rate
            </div>
            <div>
                <span class="rate-value"><?= $live_rate ?></span>
                <span class="rate-change"><?= $rate_change ?></span>
            </div>
        </div>

        <!-- Nav -->
        <nav class="sidebar__nav">
            <a href="dashboard.php" class="nav-link active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                Dashboard
            </a>
            <a href="calculator.php" class="nav-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="2" width="16" height="20" rx="2"/>
                    <line x1="8" y1="6" x2="16" y2="6"/>
                    <line x1="8" y1="10" x2="16" y2="10"/>
                    <line x1="8" y1="14" x2="12" y2="14"/>
                </svg>
                Calculator
            </a>
            <a href="rates.php" class="nav-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 7H6a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2v-3"/>
                    <path d="M9 15h3l8.5-8.5a1.5 1.5 0 0 0-3-3L9 12v3"/>
                    <line x1="16" y1="5" x2="19" y2="8"/>
                </svg>
                My Rates
            </a>
            <!--<a href="profile.php" class="nav-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                </svg>
                Profile
            </a>
            <a href="admin.php" class="nav-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                Admin
            </a>-->
        </nav>

        <!-- User profile + logout -->
        <div class="sidebar__user">
            <div class="sidebar__profile">
                <div class="sidebar__avatar"><?= $user_abbr ?></div>
                <div>
                    <div class="sidebar__profile-name"><?= $user_name ?></div>
                    <div class="sidebar__profile-role">Pro Trader</div>
                </div>
            </div>
            <a href="includes/logout.inc.php" class="btn-logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Logout
            </a>
        </div>

    </aside><!-- /sidebar -->

    <!-- ════════════════════════════════════════════════════
         MAIN
    ════════════════════════════════════════════════════ -->
    <main class="main">
        <!-- Flash Messages -->
        <?php if ($success_msg): ?>
        <div style="background:#dcfce7;border:1px solid #86efac;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:0.9rem;color:#166534;display:flex;align-items:center;gap:10px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <strong><?= htmlspecialchars($success_msg) ?></strong>
            <a href="rates.php" style="margin-left:auto;color:#3b82f6;font-weight:600;">View My Rates →</a>
        </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
        <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:0.9rem;color:#dc2626;">
            <?= htmlspecialchars($error_msg) ?>
        </div>
        <?php endif; ?>

        <!-- Page header -->
        <div class="page-header">
            <div>
                <h1 class="page-header__title">Welcome back, <?= $user_name ?></h1>
                <p class="page-header__sub">Here's your P2P overview</p>
            </div>
            <div class="last-updated">
                Last Updated
                <span><?= $last_updated ?></span>
            </div>
        </div>

        <!-- ── Stat cards ─────────────────────────────── -->
        <div class="stat-row">
            <!-- Total Trades -->
            <div class="stat-card">
                <div class="stat-card__top">
                    <span class="stat-card__label">Total Trades</span>
                    <div class="stat-card__icon stat-card__icon--green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
                            <polyline points="16 7 22 7 22 13"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <span class="stat-card__value"><?= $total_trades ?></span>
                    <span class="stat-card__change"><?= $trades_change ?></span>
                </div>
            </div>

            <!-- Success Rate -->
            <div class="stat-card">
                <div class="stat-card__top">
                    <span class="stat-card__label">Success Rate</span>
                    <div class="stat-card__icon stat-card__icon--blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="20" x2="18" y2="10"/>
                            <line x1="12" y1="20" x2="12" y2="4"/>
                            <line x1="6"  y1="20" x2="6"  y2="14"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <span class="stat-card__value"><?= $success_rate ?></span>
                    <span class="stat-card__change"><?= $success_change ?></span>
                </div>
            </div>

            <!-- Total Volume -->
            <div class="stat-card">
                <div class="stat-card__top">
                    <span class="stat-card__label">Total Volume</span>
                    <div class="stat-card__icon stat-card__icon--yellow">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <span class="stat-card__value"><?= $total_volume ?></span>
                    <span class="stat-card__change"><?= $volume_change ?></span>
                </div>
            </div>
        </div>

        <!-- ── Middle row ─────────────────────────────── -->
        <div class="mid-row">

            <!-- Trader profile card -->
            <div class="trader-card">
                <div class="trader-card__head">
                    <div class="trader-card__avatar"><?= $user_abbr ?></div>
                    <div>
                        <div class="trader-card__name"><?= $user_name ?></div>
                        <span class="trader-card__badge">Pro Trader</span>
                    </div>
                </div>
                <div class="trader-card__stats">
                    <div class="trader-stat">
                        Recent Activities <span><?= $recent_activity ?></span>
                    </div>
                    <div class="trader-stat">
                        Completed Trades <span><?= $completed_trades ?></span>
                    </div>
                    <div class="trader-stat">
                        Success Rate <span><?= $trader_success ?></span>
                    </div>
                </div>
            </div>

            <!-- Progress / bar chart -->
            <div class="progress-card">
                <div class="card-header">
                    <span class="card-title">Progress</span>
                    <span class="card-badge">Week</span>
                </div>
                <div class="progress-value">0.1h</div>
                <div class="bar-chart">
                    <?php foreach ($bars as $h): ?>
                    <div class="bar" style="height: <?= $h ?>%;" title="<?= $h ?>%"></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Time tracker -->
            <div class="tracker-card">
                <div class="card-header">
                    <span class="card-title">Time Tracker</span>
                    <a href="#" class="view-all">View All</a>
                </div>

                <div class="circular-wrap">
                    <div class="circular-progress">
                        <svg viewBox="0 0 100 100" width="100" height="100">
                            <circle class="track" cx="50" cy="50" r="40"/>
                            <!-- stroke-dasharray = 2πr ≈ 251.2 -->
                            <circle class="fill" cx="50" cy="50" r="40"
                                    stroke-dasharray="251.2"
                                    stroke-dashoffset="<?= 251.2 - (251.2 * $onboarding_pct / 100) ?>"/>
                        </svg>
                        <div class="circular-label">
                            <span class="circular-time"><?= $tracker_time ?></span>
                            <span class="circular-sub"><?= $tracker_today ?></span>
                        </div>
                    </div>
                </div>

                <div class="tracker-row">
                    <span class="text-muted" style="font-size:0.8rem;">Onboarding</span>
                    <span><?= $onboarding_pct ?>%</span>
                </div>
                <div class="tracker-progress-bar">
                    <div class="tracker-progress-fill" style="width:<?= $onboarding_pct ?>%"></div>
                </div>
            </div>

        </div><!-- /mid-row -->

                <!-- ── Quick Calculator (Pure PHP) ───────────── -->
        <div class="calculator-section">
            <div class="calc-header">
                <h2 class="calc-title">Quick Calculator</h2>
                <form method="POST" action="" class="calc-toggle">
                    <input type="hidden" name="action" value="quick_calc">
                    <input type="hidden" name="constant" value="<?= qc_val($calc_inputs, 'constant') ?>">
                    <input type="hidden" name="normal_rate" value="<?= qc_val($calc_inputs, 'normal_rate') ?>">
                    <input type="hidden" name="cost" value="<?= qc_val($calc_inputs, 'cost') ?>">
                    
                    <!-- Clicking these submits the form and switches mode -->
                    <button type="submit" name="mode" value="buy" 
                            class="btn-toggle btn-toggle--buy <?= $calc_mode === 'buy' ? 'active' : '' ?>">
                        Buy USDT
                    </button>
                    <button type="submit" name="mode" value="sell" 
                            class="btn-toggle btn-toggle--sell <?= $calc_mode === 'sell' ? 'active' : '' ?>">
                        Sell USDT
                    </button>
                </form>
            </div>

            <?php if (!empty($calc_errors)): ?>
            <div class="calc-errors" style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:0.85rem;color:#dc2626;">
                <?php foreach ($calc_errors as $err): ?>
                    <p>⚠ <?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" class="calc-form-quick">
                <input type="hidden" name="action" value="quick_calc">
                <input type="hidden" name="mode" value="<?= htmlspecialchars($calc_mode) ?>">
                
                <div class="calc-inputs">
                    <div class="calc-input-group">
                        <label>Constant</label>
                        <input type="number" name="constant" value="<?= qc_val($calc_inputs, 'constant') ?>" step="any" placeholder="e.g. 1.01" required>
                    </div>
                    <div class="calc-input-group">
                        <label>Normal Rate (₦)</label>
                        <input type="number" name="normal_rate" value="<?= qc_val($calc_inputs, 'normal_rate') ?>" step="any" placeholder="e.g. 1374.22" required>
                    </div>
                    <div class="calc-input-group">
                        <label>Cost (₦)</label>
                        <input type="number" name="cost" value="<?= qc_val($calc_inputs, 'cost') ?>" step="any" placeholder="e.g. 18000" required>
                    </div>
                </div>

                <button type="submit" class="btn-save-rate" style="margin-bottom: 20px; width: 100%;">Calculate</button>
            </form>

                       <?php if ($calc_results): ?>
            <div class="calc-results">
                <!-- Quantity (Base) -->
                <div class="result-card result-card--base">
                    <div class="result-card__label">Quantity</div>
                    <div class="result-card__value"><?= number_format($calc_results['quantity'], 6) ?></div>
                </div>

                <!-- New Cost -->
                <div class="result-card result-card--<?= $calc_results['mode'] ?>">
                    <div class="result-card__label">New Cost</div>
                    <div class="result-card__value"><?= number_format($calc_results['new_cost'], 4) ?></div>
                </div>

                <!-- Buy/Sell Rate -->
                <div class="result-card result-card--<?= $calc_results['mode'] ?>">
                    <div class="result-card__label"><?= ucfirst($calc_results['mode']) ?> Rate</div>
                    <div class="result-card__value"><?= number_format($calc_results['rate'], 4) ?></div>
                </div>

                <!-- Profit -->
                <div class="result-card result-card--margin">
                    <div class="result-card__label">Profit</div>
                    <div class="result-card__value"><?= number_format($calc_results['profit'], 4) ?></div>
                </div>

                <!-- Final Buy/Sell -->
                <div class="result-card result-card--profit">
                    <div class="result-card__label"><?= $calc_results['final_label'] ?></div>
                    <div class="result-card__value"><?= number_format($calc_results['final'], 4) ?></div>
                </div>
            </div>

                        <div class="calc-actions">
                <form method="POST" action="" style="flex:1;">
                    <input type="hidden" name="action" value="save_quick_rate">
                    <input type="hidden" name="constant" value="<?= qc_val($calc_inputs, 'constant') ?>">
                    <input type="hidden" name="normal_rate" value="<?= qc_val($calc_inputs, 'normal_rate') ?>">
                    <input type="hidden" name="cost" value="<?= qc_val($calc_inputs, 'cost') ?>">
                    <button type="submit" class="btn-save-rate">Save Rate</button>
                </form>
                <a href="calculator.php" class="btn-use-trade">Full Calculator</a>
            </div>
            <?php else: ?>
            <div class="calc-placeholder">
                Enter the 3 values above and click <strong>Calculate</strong>
            </div>
            <?php endif; ?>

        </div>


</body>
</html>