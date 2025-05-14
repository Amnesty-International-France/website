<?php

/**
 * Title: Post Content
 * Description: Output the content of a single post
 * Slug: amnesty/post-content
 * Inserter: no
 */

$term = amnesty_get_a_post_term(get_the_ID());
$category_class = $term ? $term->slug : '';
$post_type_class = get_post_type() ?: '';


?>
<!-- wp:group {"tagName":"section","className":"article"} -->
<section class="wp-block-group article <?php echo esc_attr($category_class); ?> <?php echo esc_attr($post_type_class); ?>">
	<!-- wp:group {"tagName":"header","className":"article-header"} -->
	<header class="wp-block-group article-header">
		<?php if ( $category_class === "dossiers" || $category_class === "campagnes" ) : ?>
			<div class="files-wrapper">
				<div class="files-wrapper-left">
					<div class="yoast-breadcrumb-wrapper">
						<?php
						if ( function_exists('yoast_breadcrumb') ) {
							yoast_breadcrumb( '<nav class="yoast-breadcrumb">','</nav>' );
						}
						?>
					</div>
					<!-- wp:group {"className":"files"} -->
						<!-- wp:pattern {"slug":"amnesty/post-metadata"} /-->
					<!-- /wp:group -->
				</div>
				<div class="files-wrapper-right">
					<!-- wp:pattern {"slug":"amnesty/featured-image"} /-->
				</div>
			</div>
		<?php else : ?>
			<div class="yoast-breadcrumb-wrapper">
				<?php
				if ( function_exists('yoast_breadcrumb') ) {
					yoast_breadcrumb( '<nav class="yoast-breadcrumb">','</nav>' );
				}
				?>
			</div>
			<!-- wp:pattern {"slug":"amnesty/post-metadata"} /-->
			<!-- wp:pattern {"slug":"amnesty/featured-image"} /-->
		<?php endif; ?>
	</header>
	<!-- /wp:group -->

	<!-- wp:group {"tagName":"article","className":"article-content"} -->
	<article class="wp-block-group article-content">
		<!-- wp:post-content /-->
	</article>
	<!-- /wp:group -->

	<?php if ( ( $category_class === 'actualites' || $category_class === 'chroniques') && get_the_ID() ) : ?>
		<!-- wp:group {"tagName":"footer","className":"article-footer"} -->
		<footer class="wp-block-group article-footer">
			<!-- wp:pattern {"slug":"amnesty/post-terms"} /-->
		</footer>
		<!-- /wp:group -->
	<?php endif; ?>
	<!-- wp:amnesty-core/related-posts {"title":"Voir aussi"} /-->
</section>
<!-- /wp:group -->
