<?php
/**
 * Title: My Space Sidebar
 * Slug: amnesty/my-space-sidebar
 */
?>

<div class="aif-mobile-header">
    <?php
    if (function_exists('amnesty_logo')) {
        amnesty_logo(home_url('/mon-espace/'));
    }
?>
    <button id="burger-toggle" class="burger-toggle" aria-label="Ouvrir le menu">
        <span></span>
        <span></span>
        <span></span>
    </button>
</div>

<div id="my-space-sidebar" class="aif-donor-space-sidebar">
    <div class="aif-donor-space-sidebar-header">
        <div class="aif-sidebar-top-row">
            <?php amnesty_logo(home_url('/mon-espace/')); ?>
            <button id="close-menu" class="close-menu" aria-label="Fermer le menu">&times;</button>
        </div>
        <h2 class="aif-donor-space-sidebar-title">Mon Espace</h2>
    </div>
    <ul class="aif-donor-space-sidebar-menu">
        <?php
    $is_member = false;

if (is_user_logged_in()) {
    $current_user = wp_get_current_user();

    if (function_exists('get_salesforce_member_data')) {
        $sf_member = get_salesforce_member_data($current_user->user_email);

        if (isset($sf_member) && !empty($sf_member->isMembre)) {
            $is_member = true;
        }
    }
}

if ($is_member) {
    if (function_exists('amnesty_nav')) {
        amnesty_nav('my-space-member');
    }
} else {
    if (function_exists('amnesty_nav')) {
        amnesty_nav('my-space-non-member');
    }
}
?>
    </ul>
    <ul class="aif-donor-space-sidebar-footer">
        <?php
if (function_exists('amnesty_nav')) {
    amnesty_nav('my-account');
}
?>
    </ul>
</div>

<div id="mobile-menu-overlay" class="mobile-menu-overlay"></div>
