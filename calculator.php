<?php
require_once 'includes/config_session.inc.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require_once 'includes/calculator_contr.inc.php';

 $user_name = htmlspecialchars($_SESSION['user_name'] ?? 'Trader');
 $user_abbr = strtoupper(substr($user_name, 0, 2));

function v(array $inputs, string $key): string {
    return htmlspecialchars($inputs[$key] ?? '');
}

// ── Live Rate for Calculator ──────────────────────────────
 $calc_live_rate = '₦1,685';
 $calc_rate_time = $_SESSION['live_rate_time'] ?? 0;

if (!$calc_rate_time || (time() - $calc_rate_time) > 600) {
    require_once 'includes/rate_api_model.inc.php';
    $api_res = get_live_usdt_ngn_rate();
    if ($api_res['success'] && $api_res['price'] > 0) {
        $_SESSION['live_rate_value'] = $api_res['price'];
        $_SESSION['live_rate_time']  = time();
        $calc_live_rate = '₦' . number_format($api_res['price'], 2);
    }
} else {
    $calc_live_rate = '₦' . number_format($_SESSION['live_rate_value'] ?? 1685, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Calculator — Hydra P2P</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/calculator.css">
</head>
<body>
<div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar__brand">
            <div class="sidebar__logo">H</div>
            <div class="sidebar__brand-text">
                <div class="brand-name">Hydra</div>
                <div class="brand-sub">P2P Trading</div>
            </div>
        </div>

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
                <span class="rate-value"><?= $calc_live_rate ?></span>
                <span class="rate-change"><?= $rate_change ?></span>
            </div>
        </div>

        <nav class="sidebar__nav">
            <a href="dashboard.php" class="nav-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                Dashboard
            </a>
            <a href="calculator.php" class="nav-link active">
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
    </aside>

    <!-- MAIN -->
    <main class="main">

        <div class="calc-page-header">
            <div>
                <h1 class="calc-page-title">Rate Calculator</h1>
                <p class="calc-page-sub">Enter 3 values — quantity, buy and sell results are all calculated automatically</p>
            </div>
            <div class="live-rate-badge">Live Market Rate: ₦1,685</div>
        </div>

        <!-- ══ SUCCESS MESSAGE (NEW) ═════════════════════════ -->
        <?php if ($save_success): ?>
        <div class="calc-success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <div>
                <strong>Rates Saved!</strong>
                <span>Buy and Sell rates have been saved. <a href="rates.php">View My Rates →</a></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- ── INPUT FORM (3 inputs only) ─────────────── -->
        <div class="calc-form-card">
            <h2>Input Values</h2>

            <?php if (!empty($errors)): ?>
            <div class="calc-errors">
                <?php foreach ($errors as $err): ?>
                <p>⚠ <?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="calculator.php" id="calc-form">
                <input type="hidden" name="action" value="calculate">
                <div class="input-grid">

                    <div class="field-group">
                        <label class="field-label" for="constant">Constant</label>
                        <span class="var-badge">constant</span>
                        <input class="field-input" type="number" id="constant" name="constant"
                               value="<?= v($inputs, 'constant') ?>"
                               placeholder="e.g. 1.01" step="any" required>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="normal_rate">Normal Rate</label>
                        <span class="var-badge">normal_rate</span>
                        <input class="field-input" type="number" id="normal_rate" name="normal_rate"
                               value="<?= v($inputs, 'normal_rate') ?>"
                               placeholder="e.g. 1685" step="any" required>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="cost">Cost</label>
                        <span class="var-badge">cost</span>
                        <input class="field-input" type="number" id="cost" name="cost"
                               value="<?= v($inputs, 'cost') ?>"
                               placeholder="e.g. 18000" step="any" required>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        Calculate
                    </button>
                    <button type="button" class="btn-reset" onclick="document.getElementById('calc-form').reset()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="1 4 1 10 7 10"/>
                            <path d="M3.51 15a9 9 0 1 0 .49-4"/>
                        </svg>
                        Reset
                    </button>
                </div>
            </form>
        </div>

        <!-- ── RESULTS — Base + Buy + Sell at once ─────── -->
        <div class="results-area">

        <?php if ($results): ?>

            <!-- ══ SAVE RATE BUTTON (NEW) ══════════════════ -->
            <div class="save-rate-bar">
                <div class="save-rate-info">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                    </svg>
                    <span>Save this rate to view later in <strong>My Rates</strong></span>
                </div>
                <form method="POST" action="calculator.php" id="save-form">
                    <input type="hidden" name="action" value="save_rate">
                    <input type="hidden" name="constant" value="<?= v($inputs, 'constant') ?>">
                    <input type="hidden" name="normal_rate" value="<?= v($inputs, 'normal_rate') ?>">
                    <input type="hidden" name="cost" value="<?= v($inputs, 'cost') ?>">
                    <button type="submit" class="btn-save-rate">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        Save Rate
                    </button>
                </form>
            </div>

            <!-- BASE -->
            <div class="result-card result-card--base">
                <div class="result-card__header">
                    <div class="result-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <span class="result-card__title">Base — Quantity</span>
                </div>
                <div class="result-card__body">
                    <div class="result-row">
                        <div class="result-row__label">quantity</div>
                        <div class="result-row__formula">
                            (<?= v($inputs,'cost') ?> × <?= v($inputs,'constant') ?>) ÷ <?= v($inputs,'normal_rate') ?>
                        </div>
                        <div class="result-row__value">
                            <?= number_format($results['quantity'], 6) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BUY -->
            <div class="result-card result-card--buy">
                <div class="result-card__header">
                    <div class="result-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
                            <polyline points="16 7 22 7 22 13"/>
                        </svg>
                    </div>
                    <span class="result-card__title">Buy (B)</span>
                </div>
                <div class="result-card__body">

                    <div class="result-row">
                        <div class="result-row__label">new_cost</div>
                        <div class="result-row__formula">
                            <?= v($inputs,'normal_rate') ?> + <?= v($inputs,'cost') ?>
                        </div>
                        <div class="result-row__value">
                            <?= number_format($results['buy']['new_cost'], 4) ?>
                        </div>
                    </div>

                    <div class="result-row">
                        <div class="result-row__label">buy_rate &nbsp;<small style="font-weight:400;font-size:0.68rem;">(my_rate)</small></div>
                        <div class="result-row__formula">
                            (new_cost × <?= v($inputs,'constant') ?>) ÷ quantity
                        </div>
                        <div class="result-row__value">
                            <?= number_format($results['buy']['buy_rate'], 4) ?>
                        </div>
                    </div>

                    <div class="result-row">
                        <div class="result-row__label">profit &nbsp;<small style="font-weight:400;font-size:0.68rem;">(remainder)</small></div>
                        <div class="result-row__formula">
                            (buy_rate − <?= v($inputs,'normal_rate') ?>) ÷ 2
                        </div>
                        <div class="result-row__value">
                            <?= number_format($results['buy']['profit'], 4) ?>
                        </div>
                    </div>

                    <div class="result-row">
                        <div class="result-row__label">final_buy</div>
                        <div class="result-row__formula">
                            buy_rate − profit
                        </div>
                        <div class="result-row__value">
                            <?= number_format($results['buy']['final_buy'], 4) ?>
                        </div>
                    </div>

                </div>
            </div>

            <!-- SELL -->
            <div class="result-card result-card--sell">
                <div class="result-card__header">
                    <div class="result-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 17 13.5 8.5 8.5 13.5 2 7"/>
                            <polyline points="16 17 22 17 22 11"/>
                        </svg>
                    </div>
                    <span class="result-card__title">Sell (S)</span>
                </div>
                <div class="result-card__body">

                    <div class="result-row">
                        <div class="result-row__label">new_cost</div>
                        <div class="result-row__formula">
                            <?= v($inputs,'cost') ?> − <?= v($inputs,'normal_rate') ?>
                        </div>
                        <div class="result-row__value">
                            <?= number_format($results['sell']['new_cost'], 4) ?>
                        </div>
                    </div>

                    <div class="result-row">
                        <div class="result-row__label">sell_rate &nbsp;<small style="font-weight:400;font-size:0.68rem;">(my_rate)</small></div>
                        <div class="result-row__formula">
                            (new_cost × <?= v($inputs,'constant') ?>) ÷ quantity
                        </div>
                        <div class="result-row__value">
                            <?= number_format($results['sell']['sell_rate'], 4) ?>
                        </div>
                    </div>

                    <div class="result-row">
                        <div class="result-row__label">profit &nbsp;<small style="font-weight:400;font-size:0.68rem;">(remainder)</small></div>
                        <div class="result-row__formula">
                            (<?= v($inputs,'normal_rate') ?> − sell_rate) ÷ 2
                        </div>
                        <div class="result-row__value">
                            <?= number_format($results['sell']['profit'], 4) ?>
                        </div>
                    </div>

                    <div class="result-row">
                        <div class="result-row__label">final_sell</div>
                        <div class="result-row__formula">
                            sell_rate + profit
                        </div>
                        <div class="result-row__value">
                            <?= number_format($results['sell']['final_sell'], 4) ?>
                        </div>
                    </div>

                </div>
            </div>

        <?php else: ?>

            <div class="results-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="2" width="16" height="20" rx="2"/>
                    <line x1="8" y1="6"  x2="16" y2="6"/>
                    <line x1="8" y1="10" x2="16" y2="10"/>
                    <line x1="8" y1="14" x2="12" y2="14"/>
                </svg>
                <p>Fill in the 3 values above and click <strong>Calculate</strong><br>
                — Quantity, Buy and Sell results will all appear here.</p>
            </div>

        <?php endif; ?>

        </div><!-- /results-area -->

    </main>
</div>
</body>
</html>