<?php

/**
 * Title: Post Term List
 * Description: Output the taxonomy terms for a post
 * Slug: amnesty/post-term-list-footer
 * Inserter: no
 */

$postId = get_the_ID();
$post_type = get_post_type($postId);
$main_category = amnesty_get_a_post_term($postId);
$post_terms = amnesty_get_post_terms($postId);

if ($main_category) {
    $post_terms = array_filter($post_terms, static function ($term) use ($main_category) {
        return $term->taxonomy !== $main_category->taxonomy && $term->term_id !== $main_category->term_id;
    });
}

if ($post_type !== 'tribe_events') {
    $post_terms = array_filter($post_terms, static function ($term) {
        if ($term->taxonomy === 'location') {
            return false;
        }
        if ($term->taxonomy === 'combat') {
            return (int) $term->parent !== 0;
        }
        return true;
    });
}

if (empty($post_terms)) {
    return;
}

?>

<?php foreach ($post_terms as $post_term) : ?>
	<!-- wp:amnesty-core/chip-category {"label":"<?php echo esc_html($post_term->name); ?>","link":"<?php echo esc_url(amnesty_term_link($post_term)); ?>","size":"medium","style":"bg-gray"} /-->
<?php endforeach; ?>
