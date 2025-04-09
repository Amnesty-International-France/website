<?php

/**
 * Title: Post Term List
 * Description: Output the taxonomy terms for a post
 * Slug: amnesty/post-term-list-metadata
 * Inserter: no
 */

$post_terms = wp_get_object_terms(get_the_ID(), get_object_taxonomies(get_post_type()));
$main_category = amnesty_get_a_post_term(get_the_ID());

if (empty($post_terms) || ! $main_category) {
    return;
}

$post_terms = array_filter($post_terms, static function ($term) use ($main_category) {
    return $term->slug !== $main_category->slug;
});

$chip_style = match ($main_category->slug) {
    'actualites', 'dossier' => 'bg-yellow',
    'chroniques' => 'outline-yellow',
    default => 'black-outline',
};

?>

<?php foreach ($post_terms as $post_term) : ?>
    <!-- wp:amnesty-core/chip-category {"label":"<?php echo esc_html($post_term->name); ?>","link":"<?php echo esc_url(amnesty_term_link($post_term)); ?>","size":"medium","style":"<?php echo esc_attr($chip_style); ?>"} /-->
<?php endforeach; ?>
