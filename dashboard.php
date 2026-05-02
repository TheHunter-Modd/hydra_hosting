<?php
require_once 'includes/config_session.inc.php';

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

$live_rate       = '₦1,685';
$rate_change     = '+2.3%';
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

// Last updated
$last_updated = 'Just now';
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
                <div class="brand-sub">P2P Trading</div>
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
            <a href="profile.php" class="nav-link">
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
            </a>
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

        <!-- Page header -->
        <div class="page-header">
            <div>
                <h1 class="page-header__title">Welcome back, <?= $user_name ?></h1>
                <p class="page-header__sub">Here's your P2P trading overview</p>
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

        <!-- ── Quick Calculator ───────────────────────── -->
        <div class="calculator-section">
            <div class="calc-header">
                <h2 class="calc-title">Quick Calculator</h2>
                <div class="calc-toggle">
                    <button class="btn-toggle btn-toggle--buy"  id="btn-buy"  onclick="setMode('buy')">Buy USDT</button>
                    <button class="btn-toggle btn-toggle--sell" id="btn-sell" onclick="setMode('sell')">Sell USDT</button>
                </div>
            </div>

            <div class="calc-inputs">
                <div class="calc-input-group">
                    <label>Amount (₦)</label>
                    <input type="number" id="calc-amount" value="18000"
                           oninput="calculate()" placeholder="e.g. 18000">
                </div>
                <div class="calc-input-group">
                    <label>Rate (₦/USDT)</label>
                    <input type="number" id="calc-rate" value="1685"
                           oninput="calculate()" placeholder="e.g. 1685">
                </div>
            </div>

            <div class="calc-results">
                <div class="result-card result-card--default">
                    <div class="result-card__label">USDT Quantity</div>
                    <div class="result-card__value" id="res-usdt">10.6825</div>
                </div>
                <div class="result-card result-card--buy">
                    <div class="result-card__label">Buy Rate</div>
                    <div class="result-card__value" id="res-buy-rate">₦1693.42</div>
                </div>
                <div class="result-card result-card--margin">
                    <div class="result-card__label">Profit Margin</div>
                    <div class="result-card__value" id="res-margin">1.01%</div>
                </div>
                <div class="result-card result-card--profit">
                    <div class="result-card__label">Profit Amount</div>
                    <div class="result-card__value" id="res-profit">₦180.00</div>
                </div>
            </div>

            <div class="calc-actions">
                <button class="btn-save-rate" onclick="saveRate()">Save Rate</button>
                <button class="btn-use-trade" onclick="useInTrade()">Use in Trade</button>
            </div>
        </div>

    </main><!-- /main -->
</div><!-- /app -->

<script>
// ── Calculator logic ──────────────────────────────────────────
let mode = 'buy'; // 'buy' | 'sell'

function setMode(m) {
    mode = m;
    document.getElementById('btn-buy').className  = 'btn-toggle ' + (m === 'buy'  ? 'btn-toggle--buy'  : 'btn-toggle--sell');
    document.getElementById('btn-sell').className = 'btn-toggle ' + (m === 'sell' ? 'btn-toggle--buy'  : 'btn-toggle--sell');
    calculate();
}

function calculate() {
    const amount = parseFloat(document.getElementById('calc-amount').value) || 0;
    const rate   = parseFloat(document.getElementById('calc-rate').value)   || 1;

    // USDT quantity
    const usdt = amount / rate;

    // Spread: buy at 0.5% above rate, sell at 0.5% below
    const spread = mode === 'buy' ? 1.005 : 0.995;
    const effectiveRate = rate * spread;

    // Buy/sell rate (what we actually charge)
    const tradeRate = effectiveRate;

    // Profit margin %
    const margin = Math.abs(spread - 1) * 100;

    // Profit in ₦
    const profit = usdt * Math.abs(rate - effectiveRate);

    document.getElementById('res-usdt').textContent     = usdt.toFixed(4);
    document.getElementById('res-buy-rate').textContent = '₦' + tradeRate.toFixed(2);
    document.getElementById('res-margin').textContent   = margin.toFixed(2) + '%';
    document.getElementById('res-profit').textContent   = '₦' + profit.toFixed(2);
}

function saveRate() {
    const rate = document.getElementById('calc-rate').value;
    // In production: AJAX POST to rates controller
    alert('Rate ₦' + rate + ' saved!');
}

function useInTrade() {
    // In production: redirect to trade page with pre-filled rate
    window.location.href = 'rates.php?rate=' + document.getElementById('calc-rate').value;
}

// Run on load
calculate();
</script>

</body>
</html>
