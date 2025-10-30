<?php
/**
 * Title: Archive loop location
 * Description: Template for the loop on archive location page
 * Slug: amnesty/archive-loop-location
 * Inserter: yes
 */
?>

<!-- wp:query {"inherit":true} -->
<div class="wp-block-query">
    <div class="wp-block-group archive-location section section--small section--tinted has-gutter">
        <div class="wp-block-group postlist">

            <!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
                <!-- wp:amnesty-core/article-card {"direction":"portrait"} /-->
            <!-- /wp:post-template -->

            <!-- wp:query-no-results -->
            <div class="wp-block-query-no-results">
                <p>Nous n’avons pas trouvé d’articles correspondant à vos critères de recherche.</p>
            </div>
            <!-- /wp:query-no-results -->

        </div>
    </div>

    <!-- wp:query-pagination {"align":"center","className":"section section--small","paginationArrow":"none","layout":{"type":"flex","justifyContent":"space-between","flexWrap":"nowrap"}} -->
        <!-- wp:query-pagination-previous {"label":"<?php echo esc_html(__('Previous', 'amnesty')); ?>"} /-->
        <!-- wp:query-pagination-numbers {"midSize":1,"className":"page-numbers"} /-->
        <!-- wp:query-pagination-next {"label":"<?php echo esc_html(__('Next', 'amnesty')); ?>"} /-->
    <!-- /wp:query-pagination -->
</div>
<!-- /wp:query -->
