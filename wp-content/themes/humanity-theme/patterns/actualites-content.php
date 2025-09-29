<?php
/**
 * Title: Actualities Content Pattern
 * Description: Actualities content
 * Slug: amnesty/actualites-content
 * Inserter: no
 */
?>

<!-- wp:pattern {"slug":"amnesty/my-space-sidebar"} /-->
<main class="aif-donor-space-content">
    <!-- wp:pattern {"slug":"amnesty/my-space-header"} /-->
    <div class="aif-actualities">
        <h2 class="aif-actualities-title"><?php the_title(); ?></h2>
        <!-- wp:amnesty-core/archive-filters-actualities /-->
        <!-- wp:pattern {"slug":"amnesty/actualites-loop"} /-->
    </div>
</main>
