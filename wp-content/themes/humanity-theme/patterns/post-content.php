<?php

/**
 * Title: Post Content
 * Description: Output the content of a single post
 * Slug: amnesty/post-content
 * Inserter: no
 */

$category = amnesty_get_a_post_term( get_the_ID() )->slug;

?>
<!-- wp:group {"tagName":"section","className":"article"} -->
<section class="wp-block-group article <?php echo $category ?>">
	<!-- wp:group {"tagName":"header","className":"article-header"} -->
	<header class="wp-block-group article-header">
		<div class="yoast-breadcrumb-wrapper">
			<?php
			if ( function_exists('yoast_breadcrumb') ) {
				yoast_breadcrumb( '<nav class="yoast-breadcrumb">','</nav>' );
			}
			?>
		</div>
		<!-- wp:pattern {"slug":"amnesty/post-metadata"} /-->
		<!-- wp:pattern {"slug":"amnesty/featured-image"} /-->
	</header>
	<!-- /wp:group -->
	<!-- wp:group {"tagName":"article","className":"article-content"} -->
	<article class="wp-block-group article-content">
		<!-- wp:post-content /-->
	</article>
	<!-- /wp:group -->
	<?php if ( ( $category === 'actualites' || $category === 'chroniques') && get_the_ID() ) : // prevent weird output in the site editor ?>
		<!-- wp:group {"tagName":"footer","className":"article-footer"} -->
		<footer class="wp-block-group article-footer">
			<!-- wp:pattern {"slug":"amnesty/post-terms"} /-->
		</footer>
		<!-- /wp:group -->
	<?php endif; ?>
	<!-- wp:amnesty-core/related-posts {"title":"Sur le même thème"} /-->
</section>
<!-- /wp:group -->
