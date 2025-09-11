<?php
/**
 * Title: Archive loop
 * Description: Template for the loop on archive pages
 * Slug: amnesty/archive-loop
 * Inserter: yes
 */

add_filter('get_the_terms', 'amnesty_limit_post_terms_results_for_archive');

if (is_post_type_archive('landmark')) {
    $featured_query = amnesty_get_featured_landmarks();

    if ($featured_query->have_posts()) {
        echo '<div class="featured-landmarks">';
        echo '<div class="content">';
        echo '<h2 class="wp-heading-block title">À la une</h2>';
        echo '<div class="wp-block-group postlist">';
        echo '<div class="post-grid">';

        while ($featured_query->have_posts()) {
            $featured_query->the_post();

            $block = array(
                'blockName' => 'amnesty-core/article-card',
                'attrs' => array(
                    'direction' => 'portrait'
                ),
                'innerBlocks' => array(),
                'innerHTML' => '',
                'innerContent' => array()
            );

            echo render_block($block);
        }

        echo '</div></div></div></div>';
        wp_reset_postdata();
    }
}
?>

<!-- wp:query {"inherit":true} -->
<div class="wp-block-query">
    <!-- wp:group {"tagName":"div","className":""} -->
    <div class="wp-block-group news-section section section--small section--tinted has-gutter">
        <?php if (get_post_type() === 'fiche_pays') : ?>
            <!-- wp:pattern {"slug":"amnesty/countries-list"} /-->
        <?php else : ?>
            <!-- wp:group {"tagName":"div","className":"postlist"} -->
            <div class="wp-block-group postlist">
                <!-- wp:post-template {"layout":{"type":"grid","columnCount":3},"className":"post-grid"} -->
                    <!-- wp:amnesty-core/article-card {"direction":"portrait"} /-->
                <!-- /wp:post-template -->

                <!-- wp:query-no-results -->
                <div class="wp-block-query-no-results">
                    <p>Nous n’avons pas trouvé d’articles correspondant à vos critères de recherche.</p>
                </div>
                <!-- /wp:query-no-results -->
            </div>
            <!-- /wp:group -->
        <?php endif; ?>
    </div>
    <!-- /wp:group -->

    <?php if (get_post_type() !== 'fiche_pays') : ?>
        <!-- wp:query-pagination {"align":"center","className":"section section--small","paginationArrow":"none","layout":{"type":"flex","justifyContent":"space-between","flexWrap":"nowrap"}} -->
            <!-- wp:query-pagination-previous {"label":"<?php echo esc_html(__('Previous', 'amnesty')); ?>"} /-->
            <!-- wp:query-pagination-numbers {"midSize":1,"className":"page-numbers"} /-->
            <!-- wp:query-pagination-next {"label":"<?php echo esc_html(__('Next', 'amnesty')); ?>"} /-->
        <!-- /wp:query-pagination -->
    <?php endif; ?>
</div>
<!-- /wp:query -->
