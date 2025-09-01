<?php

/**
 * Title: Page Content Pattern
 * Description: Page content pattern for the theme
 * Slug: amnesty/page-content
 * Inserter: no
 */

if ( is_front_page() ) {
	$class_name = 'homepage';
}

?>

<!-- wp:group {"tagName":"page","className":"page"} -->
<article class="wp-block-group page <?php print esc_attr( $class_name ?? '' ); ?>">
	<!-- wp:group {"tagName":"section","className":"page-content"} -->
		<section class="wp-block-group page-content">
			<!-- wp:post-content /-->
		</section>
	<!-- /wp:group -->
</article>
<!-- /wp:group -->
