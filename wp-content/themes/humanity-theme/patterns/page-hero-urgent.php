<?php

/**
 * Title: Page Hero
 * Description: Outputs the page's hero for urgent register page's
 * Slug: amnesty/page-hero-urgent
 * Inserter: no
 */

declare(strict_types=1);

$featured_image_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );

$page_title = get_the_title();

?>

<?php if ( ! is_front_page() ) : ?>
	<section class="page-hero-block">
		<!-- wp:pattern {"slug":"amnesty/featured-image"} /-->
		<div class="yoast-breadcrumb-wrapper">
			<?php if ( function_exists('yoast_breadcrumb') ) yoast_breadcrumb('<nav class="yoast-breadcrumb">', '</nav>'); ?>
		</div>
		<div class="page-hero-title-wrapper">
			<div class="container-main">
				<div class="container">
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
