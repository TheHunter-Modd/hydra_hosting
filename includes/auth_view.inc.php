<?php
// ============================================================
//  includes/auth_view.inc.php
//  Displays login/register errors and success messages
//  stored in $_SESSION by the controller.
// ============================================================

function render_auth_errors(): void {
    if (!empty($_SESSION['auth_errors'])) {
        echo '<div class="auth-alert auth-alert--error">';
        foreach ($_SESSION['auth_errors'] as $err) {
            echo '<p>' . htmlspecialchars($err) . '</p>';
        }
        echo '</div>';
        unset($_SESSION['auth_errors']);
    }
}

function render_auth_success(): void {
    if (!empty($_SESSION['auth_success'])) {
        echo '<div class="auth-alert auth-alert--success">';
        echo '<p>' . htmlspecialchars($_SESSION['auth_success']) . '</p>';
        echo '</div>';
        unset($_SESSION['auth_success']);
    }
}
