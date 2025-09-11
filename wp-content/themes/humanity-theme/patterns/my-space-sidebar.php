<?php
/**
 * Title: My Space Sidebar
 * Slug: amnesty/my-space-sidebar
 */
?>

<div class="aif-mobile-header">
    <?php
    if (function_exists('amnesty_logo')) {
        amnesty_logo();
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
            <?php amnesty_logo(); ?>
            <button id="close-menu" class="close-menu" aria-label="Fermer le menu">&times;</button>
        </div>
        <h2 class="aif-donor-space-sidebar-title">Mon Espace</h2>
    </div>
    <ul class="aif-donor-space-sidebar-menu">
        <?php
    if (function_exists('amnesty_nav')) {
        amnesty_nav('my-space');
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

