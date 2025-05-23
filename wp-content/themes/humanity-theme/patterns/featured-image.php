<?php

/**
 * Title: Featured Image
 * Description: Featured image output with caption, copyright, etc.
 * Slug: amnesty/featured-image
 * Inserter: yes
 */

use Amnesty\Get_Image_Data;

if ( amnesty_post_has_hero() || amnesty_post_has_header() ) {
	return;
}

if ( get_post_meta( get_the_ID(), '_hide_featured_image', true ) ) {
	return;
}

$image_id = get_post_thumbnail_id( get_the_ID() );

if ( ! $image_id ) {
	return;
}

$image = new Get_Image_Data( $image_id );

$is_page   = is_page();
$class_name = $is_page ? 'page-figure is-stretched' : 'article-figure is-stretched';

$attributes = [
	'id'              => $image_id,
	'className'       => $class_name,
	'sizeSlug'        => 'hero-md',
	'linkDestination' => 'none',
];

$credit  = trim( $image->credit() );
$caption = trim( $image->caption() );
?>
<!-- wp:group {"tagName":"div"} -->
<div class="wp-block-group">
	<!-- wp:image <?php echo wp_kses_data( wp_json_encode( $attributes ) ); ?> {"className":"container--full-width"} -->
	<figure class="wp-block-image container--full-width <?php echo esc_attr( $attributes['className'] ); ?>">
		<?php if (get_post_type() === 'fiche_pays') : ?>
			<div class="yoast-breadcrumb-wrapper">
				<?php if ( function_exists('yoast_breadcrumb') ) yoast_breadcrumb('<nav class="yoast-breadcrumb">', '</nav>'); ?>
			</div>
			<div class="country-title-wrapper">
				<div class="container">
					<h1 class="country-title"><?php the_title(); ?></h1>
				</div>
			</div>
		<?php endif; ?>

		<img src="<?php echo esc_url( amnesty_get_attachment_image_src( $image_id, 'hero-md' ) ); ?>" alt="" class="wp-image-<?php echo absint( $image_id ); ?>"/>

		<?php if ( ( $is_page || get_post_type() === 'fiche_pays' ) && ( $credit || $caption ) ) : ?>
			<figcaption class="feature-image-caption-overlay">
				<?php if ( $credit ) : ?>
					<span class="feature-image-description"><?php echo esc_html( $credit ); ?></span><br/>
				<?php endif; ?>
				<?php if ( $caption ) : ?>
					<span class="feature-image-caption">/<?php echo esc_html( $caption ); ?></span>
				<?php endif; ?>
			</figcaption>
		<?php endif; ?>
	</figure>
	<!-- /wp:image -->

	<?php if ( ! $is_page && get_post_type() !== 'fiche_pays' && ( $credit || $caption ) ) : ?>
		<div class="feature-image">
			<div class="feature-image-caption-block">
				<?php if ( $credit ) : ?>
					<div class="feature-image-description">
						<?php echo esc_html( $credit ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $caption ) : ?>
					<div class="feature-image-caption">
						<?php echo esc_html( $caption ); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
<!-- /wp:group -->
