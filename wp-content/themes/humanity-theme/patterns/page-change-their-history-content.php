<?php

/**
 * Title: Page Change Their History Content Pattern
 * Description: Page content pattern for the theme
 * Slug: amnesty/page-change-their-history-content
 * Inserter: no
 */

$hero_extra_class = ! has_post_thumbnail() ? 'no-featured-image' : '';
$no_chapo = ! has_block('amnesty-core/chapo') ? 'no-chapo' : '';
$page = get_post();
$parent = get_post($page->post_parent);
$has_related_content = !empty(get_field('_related_posts_selected', $page));
?>

<!-- wp:group {"tagName":"page","className":"page"} -->
<article class="wp-block-group page <?php print esc_attr($class_name ?? ''); ?>">
	<!-- wp:group {"tagName":"section","className":"page-content"} -->
		<section class="wp-block-group page-content <?php echo esc_attr($hero_extra_class); ?> <?php print esc_attr($no_chapo ?? ''); ?>">
			<!-- wp:post-content /-->
		</section>
	<!-- /wp:group -->
	<?php if ($has_related_content): ?>
		<!-- wp:amnesty-core/related-posts {"title":"Voir aussi"} /-->
	<?php endif; ?>
	<?php if (! is_front_page()): ?>
		<!-- wp:group {"tagName":"footer","className":"article-footer"} -->
		<footer class="wp-block-group article-footer">
			<!-- wp:pattern {"slug":"amnesty/post-terms"} /-->
		</footer>
		<!-- /wp:group -->
	<?php endif; ?>
</article>
<!-- /wp:group -->
