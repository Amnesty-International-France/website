<?php

declare(strict_types=1);

/**
 * Title: Page Hero Foundation
 * Description: Outputs the page's foundation hero, if any
 * Slug: amnesty/page-hero-foundation
 * Inserter: no
 */

$featured_image_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );

$page_title = get_the_title();

$over_title = get_field( 'sur-titre', $post->ID ) ?? '';

?>

<?php if ( ! is_front_page() ) : ?>
	<section class="page-hero-block">
		<!-- wp:pattern {"slug":"amnesty/featured-image"} /-->
		<div class="yoast-breadcrumb-wrapper">
			<?php if ( function_exists('yoast_breadcrumb') ) yoast_breadcrumb('<nav class="yoast-breadcrumb">', '</nav>'); ?>
		</div>
		<div class="page-hero-title-wrapper">
			<div class="container">
				<div class="container-title">
					<?php if ( $over_title ) : ?>
						<h3 class="page-hero-overtitle"><?php echo esc_html( $over_title ); ?></h3>
						<br>
					<?php endif; ?>
					<h1 class="page-hero-title"><?php echo esc_html( $page_title ); ?></h1>
				</div>
			</div>
		</div>
	</section>
<?php endif; ?>


<?php
if ( ! is_admin() ) {
	add_filter( 'the_content', 'amnesty_remove_first_hero_from_content', 0 );
}
