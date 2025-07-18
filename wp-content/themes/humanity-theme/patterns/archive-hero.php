<?php
/**
 * Title: Archive hero
 * Description: Outputs hero block for post type archive pages
 * Slug: amnesty/archive-hero
 * Inserter: no
 */

 $term = get_queried_object();
 $term_slug = isset($term->slug) ? $term->slug : '';
 $post_type_class = is_post_type_archive() ? get_post_type() : '';
 $image_url = '';
 $image_alt = '';

 if (is_post_type_archive()) {
    $post_type = get_post_type();
    $image_id = get_option("{$post_type}_global_image_id");

    if ($image_id) {
        $image_url = wp_get_attachment_url($image_id);
        $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
    }
} elseif ($term && isset($term->term_id)) {
    $image = get_field('category_image', $term);
    if ($image) {
        $image_url = $image['url'];
        $image_alt = $image['alt'];
    }
}

 if (empty($image_url)) {
	 $image_url = get_template_directory_uri() . '/assets/images/default-archive-hero.png';
	 $image_alt = 'Image par défaut';
 }

 if (is_post_type_archive()) {
	 $post_type_obj = get_post_type_object(get_post_type());
	 $category_name = $post_type_obj ? $post_type_obj->labels->name : '';
 } elseif (isset($term->name)) {
	 $category_name = $term->name;
 } else {
	 $category_name = 'Actualités';
 }

 if ($category_name === 'Évènements') {
	$category_name = 'Agenda';
}
 ?>


 <!-- wp:group {"tagName":"section","className":"archive-hero"} -->
 <section class="wp-block-group archive-hero <?php echo esc_attr($term_slug); ?> <?php echo esc_attr($post_type_class); ?>">
	 <div class="archive-hero-img-container">
		 <?php if ($image_url) : ?>
			 <img class="archive-hero-img" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt); ?>" />
		 <?php endif; ?>
			<div class="yoast-breadcrumb-wrapper">
				<?php
					if ( function_exists('yoast_breadcrumb') ) {
						yoast_breadcrumb( '<nav class="yoast-breadcrumb">','</nav>' );
					}
				?>
			</div>
			<div class="archive-hero-title-container">
			<div class="archive-hero-title">
				<h1><?php echo esc_html($category_name); ?></h1>
			</div>
		</div>
	 </div>

 </section>
 <!-- /wp:group -->
