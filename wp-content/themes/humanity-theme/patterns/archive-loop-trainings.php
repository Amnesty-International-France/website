<?php
/**
 * Title: Archive loop for Trainings
 * Description: Template for the loop on archive trainings page
 * Slug: amnesty/archive-loop-trainings
 * Inserter: yes
 */

add_filter('get_the_terms', 'amnesty_limit_post_terms_results_for_archive');
?>

<!-- wp:query {"inherit":true} -->
<div class="wp-block-query">
    <!-- wp:group {"tagName":"div","className":""} -->
    <div class="wp-block-group news-section section section--small section--tinted has-gutter">
        <!-- wp:group {"tagName":"div","className":"postlist"} -->
        <div class="wp-block-group postlist">
            <!-- wp:post-template {"layout":{"type":"grid","columnCount":3},"className":"post-grid"} -->
                <!-- wp:amnesty-core/training-card {"direction":"portrait"} /-->
            <!-- /wp:post-template -->

            <!-- wp:query-no-results -->
            <div class="wp-block-query-no-results">
                <p>Nous n’avons pas trouvé d’articles correspondant à vos critères de recherche.</p>
            </div>
            <!-- /wp:query-no-results -->
        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:group -->

    <!-- wp:query-pagination {"align":"center","className":"section section--small","paginationArrow":"none","layout":{"type":"flex","justifyContent":"space-between","flexWrap":"nowrap"}} -->
        <!-- wp:query-pagination-previous {"label":"<?php echo esc_html( __( 'Previous', 'amnesty' ) ); ?>"} /-->
        <!-- wp:query-pagination-numbers {"midSize":1,"className":"page-numbers"} /-->
        <!-- wp:query-pagination-next {"label":"<?php echo esc_html( __( 'Next', 'amnesty' ) ); ?>"} /-->
    <!-- /wp:query-pagination -->
</div>
<!-- /wp:query -->
