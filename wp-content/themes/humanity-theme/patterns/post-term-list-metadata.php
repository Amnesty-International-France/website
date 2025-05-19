<?php

/**
 * Title: Post Term List
 * Description: Output the taxonomy terms for a post
 * Slug: amnesty/post-term-list-metadata
 * Inserter: no
 */

$post_id = get_the_ID();

$post_terms = wp_get_object_terms($post_id, get_object_taxonomies(get_post_type()));
$post_type = get_post_type(post: $post_id);

if (empty($post_terms)) {
    return;
}

$main_category = amnesty_get_a_post_term($post_id);

$default_chip_style = match ($main_category->slug ?? '') {
    'actualites', 'dossiers' => 'bg-yellow',
    'chroniques' => 'outline-yellow',
    default => 'outline-black',
};

foreach ($post_terms as $post_term) :
    if ($main_category) {
		if( $post_term->slug === $main_category->slug ) {
			continue;
		}
        $chip_style = $default_chip_style;
    } else {
        if ($post_type === 'landmark') {
            $chip_style = 'bg-yellow';
        } else {
            $chip_style = $post_term->taxonomy === 'location' ? 'bg-yellow' : 'outline-black';
        }
    }
    ?>
    <!-- wp:amnesty-core/chip-category {"label":"<?php echo esc_html($post_term->name); ?>","link":"<?php echo esc_url(amnesty_term_link($post_term)); ?>","size":"medium","style":"<?php echo esc_attr($chip_style); ?>"} /-->
<?php endforeach; ?>
