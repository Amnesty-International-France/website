<?php

/**
 * Title: Post Metadata
 * Description: Output contextual data for a post
 * Slug: amnesty/post-metadata
 * Inserter: no
 */

// prevent weird output in the site editor
if ( ! get_the_ID() ) {
    return;
}

$show_back_link    = ! amnesty_validate_boolish( amnesty_get_option( '_display_category_label' ) );
$show_share_icons  = ! amnesty_validate_boolish( get_post_meta( get_the_ID(), '_disable_share_icons', true ) );
$show_byline       = amnesty_validate_boolish( get_post_meta( get_the_ID(), '_display_author_info', true ) );
$show_publish_date = amnesty_validate_boolish( get_post_meta( get_the_ID(), 'show_published_date', true ) );
$show_updated_date = amnesty_validate_boolish( get_post_meta( get_the_ID(), 'show_updated_date', true ) );
$main_category     = amnesty_get_a_post_term( get_the_ID() );

$show_top_row    = ( $main_category && $show_back_link ) || $show_share_icons;
$show_bottom_row = $show_byline || $show_publish_date || $show_updated_date;

$reading_time = calculate_reading_time();
?>
<!-- wp:group {"tagName":"div","className":"article-meta"} -->
<div class="wp-block-group article-meta">
    <?php if ( $show_top_row ) : ?>
        <!-- wp:group {"tagName":"div","className":"article-metaActions"} -->
        <div class="wp-block-group article-metaActions">
            <!-- wp:pattern {"slug":"amnesty/post-back-link"} /-->
        </div>
        <!-- /wp:group -->
    <?php endif; ?>

    <!-- wp:post-title {"level":1} /-->

    <?php if ( $show_bottom_row ) : ?>
        <!-- wp:group {"tagName":"div","className":"article-metadata"} -->
        <div class="wp-block-group article-metadata">
            <div class="published-updated">
                <!-- wp:pattern {"slug":"amnesty/post-published-updated-date"} /-->
            </div>
            <div class="reading-time">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="metadata-icon">
                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
                </svg>
                Temps de lecture estimé : <?php echo esc_html( $reading_time === 1 ? "$reading_time minute" : "$reading_time minutes")?>
            </div>
        </div>
        <!-- /wp:group -->
    <?php endif; ?>

    <!-- wp:group {"tagName":"div","className":"article-metaActions"} -->
    <div class="wp-block-group article-metaActions">
        <!-- wp:pattern {"slug":"amnesty/post-term-list-metadata"} /-->
    </div>
    <!-- /wp:group -->
</div>
<!-- /wp:group -->
