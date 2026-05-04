<?php
require_once 'includes/rates_contr.inc.php';

$user_name = htmlspecialchars($_SESSION['user_name'] ?? 'Trader');
$user_abbr = strtoupper(substr($user_name, 0, 2));
$filter    = $_GET['filter'] ?? 'all';
$search    = $_GET['search'] ?? '';

function rates_url(string $f, string $s = ''): string {
    $p = ['filter' => $f];
    if ($s !== '') $p['search'] = $s;
    return 'rates.php?' . http_build_query($p);
}
function ngn(float $v): string { return '₦' . number_format($v, 2); }
function qty(float $v): string { return number_format($v, 2); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rates — Hydra P2P</title>
    <link rel="stylesheet" href="css\global.css">
    <link rel="stylesheet" href="css\dashboard.css">
    <link rel="stylesheet" href="css\rates.css">
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
                <span class="rate-value">₦1,685</span>
                <span class="rate-change">+2.3%</span>
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
            <a href="rates.php" class="nav-link active">
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

        <!-- Header -->
        <div class="rates-header">
            <div>
                <h1 class="rates-title">Saved Rates</h1>
                <p class="rates-sub">View and manage your saved P2P rates</p>
            </div>
            <a href="rates.php?export=csv" class="btn-export">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export CSV
            </a>
        </div>

        <!-- Stats cards -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-box__label">Total Saved Rates</div>
                <div class="stat-box__value stat-box__value--default"><?= $total_saved ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-box__label">Active Rates</div>
                <div class="stat-box__value stat-box__value--green"><?= $active_count ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-box__label">Total Potential Profit</div>
                <div class="stat-box__value stat-box__value--default"><?= ngn($total_profit) ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-box__label">Avg. Profit Margin</div>
                <div class="stat-box__value stat-box__value--gold">
                    <?= number_format($avg_margin, 2) ?>%
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="rates-toolbar">
            <form method="GET" action="rates.php" class="search-wrap">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" class="search-input"
                       placeholder="Search by amount, rate, or payment method..."
                       value="<?= htmlspecialchars($search) ?>">
            </form>

            <div class="filter-btns">
                <?php
                $filters = ['all' => 'All', 'buy' => 'Buy', 'sell' => 'Sell',
                            'active' => 'Active', 'archived' => 'Archived'];
                foreach ($filters as $key => $label):
                ?>
                <a href="<?= rates_url($key, $search) ?>"
                   class="filter-btn <?= $filter === $key ? 'active' : '' ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Table -->
        <div class="rates-table-card">

            <?php if (empty($filtered)): ?>
            <div class="table-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="2" width="16" height="20" rx="2"/>
                    <line x1="8" y1="6"  x2="16" y2="6"/>
                    <line x1="8" y1="10" x2="16" y2="10"/>
                    <line x1="8" y1="14" x2="12" y2="14"/>
                </svg>
                <p>
                    <?php if ($total_saved === 0): ?>
                        No rates saved yet.<br>
                        Go to the <a href="calculator.php"
                            style="color:var(--blue-primary);font-weight:600;">Calculator</a>
                        to save your first rate.
                    <?php else: ?>
                        No rates match your filter or search.
                    <?php endif; ?>
                </p>
            </div>

            <?php else: ?>
            <table class="rates-table">
                <thead>
                    <tr>
                        <th>Date &amp; Time</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Rate</th>
                        <th>USDT Qty</th>
                        <th>Buy/Sell Rate</th>
                        <th>Profit</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                                        <tbody>
                <?php foreach ($filtered as $row): ?>
                <tr>
                    <!-- FROM DB: saved_at -->
                    <td class="td-date">
                        <?= htmlspecialchars(date('Y-m-d H:i', strtotime($row['saved_at']))) ?>
                    </td>

                    <!-- FROM DB: mode -->
                    <td>
                        <?php if ($row['mode'] === 'buy'): ?>
                        <span class="type-badge type-badge--buy">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
                                <polyline points="16 7 22 7 22 13"/>
                            </svg>
                            Buy
                        </span>
                        <?php else: ?>
                        <span class="type-badge type-badge--sell">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="22 17 13.5 8.5 8.5 13.5 2 7"/>
                                <polyline points="16 17 22 17 22 11"/>
                            </svg>
                            Sell
                        </span>
                        <?php endif; ?>
                    </td>

                    <!-- FROM DB: new_cost (Amount) -->
                    <td class="td-mono"><?= ngn($row['amount']) ?></td>

                    <!-- FROM DB: normal_rate -->
                    <td class="td-mono"><?= ngn($row['normal_rate']) ?></td>

                    <!-- CALCULATED: quantity -->
                    <td class="td-mono"><?= qty($row['quantity']) ?></td>

                    <!-- FROM DB: rate = final_buy or final_sell -->
                    <td class="td-mono"><?= ngn($row['rate']) ?></td>

                    <!-- CALCULATED: profit -->
                    <td class="td-profit <?= $row['profit'] >= 0 ? '' : 'td-profit--negative' ?>">
                        <?= $row['profit'] >= 0 ? '+' : '' ?><?= ngn($row['profit']) ?>
                    </td>

                    <!-- MOCKED: payment -->
                    <td><?= htmlspecialchars($row['payment']) ?></td>

                    <!-- MOCKED: status -->
                    <td>
                        <span class="status-badge status-badge--<?= $row['status'] ?>">
                            <?= $row['status'] ?>
                        </span>
                    </td>

                    <!-- Actions -->
                    <td>
                        <div class="action-btns">
                            <button class="btn-icon btn-icon--edit" type="button" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </button>

                            <form method="POST" action="rates.php"
                                  onsubmit="return confirm('Delete this rate?')" style="display:inline">
                                <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                <button class="btn-icon btn-icon--delete" type="submit" title="Delete">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                        <path d="M10 11v6M14 11v6"/>
                                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>

                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

        </div>

    </main>
</div>

<script>
// Submit search on Enter key
document.querySelector('.search-input')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') e.target.closest('form').submit();
});
</script>
</body>
</html>