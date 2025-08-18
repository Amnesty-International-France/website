<?php

declare(strict_types=1);

/**
 * Title: EDH Content
 * Description: Output the content of a single edh post
 * Slug: amnesty/edh-content
 * Inserter: no
 */

?>

<!-- wp:group {"tagName":"section","className":"edh"} -->
<section class="wp-block-group edh-single">
	<!-- wp:group {"tagName":"header","className":"fo-header"} -->
	<header class="wp-block-group edh-header">
		<div class="yoast-breadcrumb-wrapper">
			<?php if (function_exists('yoast_breadcrumb')) {
				yoast_breadcrumb('<nav class="yoast-breadcrumb">', '</nav>');
			} ?>
		</div>
		<!-- wp:group {"className":"files"} -->
		<!-- wp:pattern {"slug":"amnesty/post-edh-metadata"} /-->

		<!-- /wp:group -->
	</header>
	<!-- /wp:group -->

	<!-- wp:group {"tagName":"edh","className":"edh-content"} -->
	<article class="wp-block-group edh-content">
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
