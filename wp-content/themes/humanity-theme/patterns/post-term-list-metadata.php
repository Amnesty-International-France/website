<?php

/**
 * Title: Post Term List
 * Description: Output the taxonomy terms for a post
 * Slug: amnesty/post-term-list-metadata
 * Inserter: no
 */

$post_id = get_the_ID();

$post_terms = wp_get_object_terms($post_id, get_object_taxonomies(get_post_type()));

if (empty($post_terms)) {
    return;
}

$main_category = amnesty_get_a_post_term($post_id);

$default_chip_style = match ($main_category->slug ?? '') {
    'actualites', 'dossier' => 'bg-yellow',
    'chroniques' => 'bg-black',
    default => 'outline-black',
};

$post_terms = array_filter($post_terms, static function ($term) use ($main_category) {
    return $term->slug !== $main_category->slug;
});

foreach ($post_terms as $post_term) :
    if ($post_term->taxonomy === 'location') {
        $chip_style = 'bg-yellow';
    } else {
        $chip_style = $default_chip_style;
    }
    ?>
    <!-- wp:amnesty-core/chip-category {"label":"<?php echo esc_html($post_term->name); ?>","link":"<?php echo esc_url(amnesty_term_link($post_term)); ?>","size":"medium","style":"<?php echo esc_attr($chip_style); ?>"} /-->
<?php endforeach; ?>
