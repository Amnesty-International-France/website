<?php
/**
 * Title: My Space Sidebar
 * Description: My Space Sidebar
 * Slug: amnesty/my-space-sidebar
 * Inserter: no
 */
?>

<div class="aif-donor-space-sidebar">
    <div class="aif-donor-space-sidebar-header">
        <?php amnesty_logo(); ?>
        <h2 class="aif-donor-space-sidebar-title">Mon Espace</h2>
    </div>
    <ul class="aif-donor-space-sidebar-menu">
        <?php
        if ( function_exists( 'amnesty_nav' ) ) {
            amnesty_nav( 'my-space' );
        }
        ?>
    </ul>
    <ul class="aif-donor-space-sidebar-footer">
    <?php
        if ( function_exists( 'amnesty_nav' ) ) {
            amnesty_nav( 'my-account' );
        }
        ?>
    </ul>
</div>
