<?php
/**
 * Title: Se former Content Pattern
 * Description: Se former content
 * Slug: amnesty/get-trained-content
 * Inserter: no
 */
?>

<!-- wp:pattern {"slug":"amnesty/my-space-sidebar"} /-->
<main class="aif-donor-space-content">
    <!-- wp:pattern {"slug":"amnesty/my-space-header"} /-->
    <div class="aif-trainings">
        <h2 class="aif-trainings-title"><?php the_title(); ?></h2>
        <!-- wp:amnesty-core/archive-filters-trainings /-->
        <!-- wp:pattern {"slug":"amnesty/archive-loop-trainings"} /-->
    </div>
</main>
