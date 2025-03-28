<?php

/**
 * Title: Post Back Link
 * Description: Output back link to return to item's category archive
 * Slug: amnesty/post-back-link
 * Inserter: no
 */

$show_back_link = ! amnesty_validate_boolish( amnesty_get_option( '_display_category_label' ) );

if ( ! $show_back_link ) {
	return;
}

$main_category = amnesty_get_a_post_term( get_the_ID() );

if ( ! $main_category ) {
	return;
}

?>
<!-- wp:amnesty-core/button -->
<div class="wp-block-amnesty-core-button button-container left">
    <a href="<?php esc_url( amnesty_term_link( $main_category ) ); ?>" target="_blank" rel="noopener noreferrer" class="custom-button">
        <div class="content medium bg-black">
            <span><?php echo esc_html($main_category->name); ?></span>
        </div>
    </a>
</div>
<!-- /wp:amnesty-core/button -->
