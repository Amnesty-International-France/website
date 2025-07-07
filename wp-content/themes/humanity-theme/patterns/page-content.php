<?php

/**
 * Title: Page Content Pattern
 * Description: Page content pattern for the theme
 * Slug: amnesty/page-content
 * Inserter: no
 */

global $post;

$slug =  '/' . $post->post_name;

$template_donation = $slug === '/don';


$no_more_blocks = $template_donation || is_front_page();

if ( is_front_page() ) {
	$class_name = 'homepage';
}

?>

<!-- wp:group {"tagName":"page","className":"page"} -->
<article class="wp-block-group page <?php print esc_attr( $class_name ?? '' ); ?>">
	<!-- wp:group {"tagName":"section","className":"page-content"} -->
		<section class="wp-block-group page-content">
			<!-- wp:post-content /-->
			<?php if ( ! $no_more_blocks ) : ?>
			<!-- wp:amnesty-core/related-posts {"title":"Voir aussi"} /-->
			<?php endif; ?>
		</section>
	<!-- /wp:group -->
</article>
<!-- /wp:group -->
