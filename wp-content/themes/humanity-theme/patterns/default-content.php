<?php

/**
 * Title: Default Content Pattern
 * Description: Default content
 * Slug: amnesty/default-content
 * Inserter: no
 */

?>

<!-- wp:pattern {"slug":"amnesty/my-space-sidebar"} /-->
<main class="aif-donor-space-content">
    <!-- wp:pattern {"slug":"amnesty/my-space-header"} /-->
    <div class="aif-default">
        <h2><?php the_title(); ?></h2>
        <div>
            <?php the_content(); ?>
        </div>
    </div>
</main>
