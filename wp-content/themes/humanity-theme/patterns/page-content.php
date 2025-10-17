<?php

/**
 * Title: Page Content Pattern
 * Description: Page content pattern for the theme
 * Slug: amnesty/page-content
 * Inserter: no
 */

if (is_front_page()) {
    $class_name = 'homepage';
}

$hero_extra_class = ! has_post_thumbnail() ? 'no-featured-image' : '';
$no_chapo = ! has_block('amnesty-core/chapo') ? 'no-chapo' : '';
$post_id = get_the_ID();
$is_combat_page = !empty(get_the_terms($post_id, 'combat'));
$has_related_content = !empty(get_field('_related_posts_selected', $post_id));
?>

<!-- wp:group {"tagName":"page","className":"page"} -->
<article class="wp-block-group page <?php print esc_attr($class_name ?? ''); ?>">
	<!-- wp:group {"tagName":"section","className":"page-content"} -->
		<section class="wp-block-group page-content <?php echo esc_attr($hero_extra_class); ?> <?php print esc_attr($no_chapo ?? ''); ?>">
			<!-- wp:post-content /-->
		</section>
	<!-- /wp:group -->
	<?php if ($is_combat_page && $has_related_content): ?>
		<!-- wp:amnesty-core/related-posts {"title":"Voir aussi"} /-->
	<?php endif; ?>
</article>
<!-- /wp:group -->
