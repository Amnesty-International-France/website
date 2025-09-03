<?php

/**
 * Title: Training Content
 * Description: Output the content of a single training post
 * Slug: amnesty/training-content
 * Inserter: no
 */

declare(strict_types=1);

?>


<!-- wp:group {"tagName":"section","className":"training"} -->
<section class="wp-block-group training-single">
	<!-- wp:group {"tagName":"header","className":"fo-header"} -->
	<header class="wp-block-group training-header">
		<div class="yoast-breadcrumb-wrapper">
			<?php if (function_exists('yoast_breadcrumb')) {
				yoast_breadcrumb('<nav class="yoast-breadcrumb">', '</nav>');
			} ?>
		</div>
		<!-- wp:group {"className":"files"} -->
		<!-- wp:pattern {"slug":"amnesty/post-training-metadata"} /-->

		<!-- /wp:group -->
	</header>
	<!-- /wp:group -->

	<!-- wp:group {"tagName":"","className":"training-content"} -->
	<article class="wp-block-group training-content">
		<!-- wp:pattern {"slug":"amnesty/featured-image"} /-->
		<!-- wp:post-content /-->

		<!-- wp:group {"tagName":"footer","className":"article-footer"} -->
		<footer class="wp-block-group article-footer">
			<!-- wp:pattern {"slug":"amnesty/post-terms"} /-->
		</footer>
		<!-- /wp:group -->
	</article>
	<!-- /wp:group -->
</section>
<!-- /wp:group -->
