<?php
/**
 * Title: Page Hero Large
 * Description: Outputs the page's hero in a large version, if any
 * Slug: amnesty/page-hero-large
 * Inserter: no
 */

$featured_image_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );

$page_title = get_the_title();
$sub_title = get_field( 'subtitle', $post->ID ) ?? '';
$btn_link_text = get_field( 'btn_link_text', $post->ID ) ?? '';
$btn_link = get_field( 'btn_link', $post->ID ) ?? '';

?>

<?php if ( ! is_front_page() ) : ?>
    <section class="page-hero-block page-hero-block--large">
        <!-- wp:pattern {"slug":"amnesty/featured-image"} /-->
        <div class="yoast-breadcrumb-wrapper">
            <?php if ( function_exists('yoast_breadcrumb') ) yoast_breadcrumb('<nav class="yoast-breadcrumb">', '</nav>'); ?>
        </div>
        <div class="page-hero-title-wrapper">
            <div class="container">
				<h1 class="page-hero-title"><?php echo esc_html( $page_title ); ?></h1><br/>
				<span class="page-hero-subtitle"><?php echo esc_html($sub_title); ?></span>

				<div class='custom-button-block'>
					<a href="<?php echo esc_html($btn_link); ?>" target="_blank" class="custom-button">
						<div class='content bg-yellow large'>
							<div class="icon-container">
								<svg
									xmlns="http://www.w3.org/2000/svg"
									viewBox="0 0 24 24"
									stroke-width="1.5"
									stroke="currentColor"
								>
									<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
								</svg>
							</div>
							<div class="button-label"><?php echo esc_html($btn_link_text); ?></div>
						</div>
					</a>
				</div>
            </div>
        </div>
    </section>
<?php endif; ?>


<?php
if ( ! is_admin() ) {
    add_filter( 'the_content', 'amnesty_remove_first_hero_from_content', 0 );
}
