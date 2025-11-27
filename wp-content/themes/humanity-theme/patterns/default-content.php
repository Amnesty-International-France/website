<?php

/**
 * Title: Default Content Pattern
 * Description: Default content
 * Slug: amnesty/default-content
 * Inserter: no
 */

$image_id = get_post_thumbnail_id();
?>

<!-- wp:pattern {"slug":"amnesty/my-space-sidebar"} /-->
<main class="aif-donor-space-content">
    <!-- wp:pattern {"slug":"amnesty/my-space-header"} /-->
    <div class="aif-default">
        <h1><?php the_title(); ?></h1>
		<?php if (has_post_thumbnail()) :?>
			<img src="<?php echo esc_url(amnesty_get_attachment_image_src($image_id, 'hero-md'));?>" alt="" class="hero-image wp-image-<?php echo absint($image_id); ?>" />
		<?php endif;?>
        <div>
            <?php the_content(); ?>
        </div>
    </div>
</main>
