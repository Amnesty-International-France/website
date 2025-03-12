<?php

/**
 * Title: Post Term List
 * Description: Output the taxonomy terms for a post
 * Slug: amnesty/post-term-list-metadata
 * Inserter: no
 */

$post_terms = wp_get_object_terms( get_the_ID(), get_object_taxonomies( get_post_type() ) );

if ( empty( $post_terms ) ) {
	return;
}
$post_terms = array_filter($post_terms, static function ($term) {
    return $term->slug !== 'news';
})
?>

<!-- wp:group {"tagName":"div","className":"article-chip-categories"} -->
<div class="wp-block-group article-chip-categories">

<?php foreach ( $post_terms as $post_term ) : ?>

<a class="chip-category is-style-yellow chip-category-size-medium" href="<?php echo esc_url( amnesty_term_link( $post_term ) ); ?>"><?php echo esc_html( $post_term->name ); ?></a>

<?php endforeach; ?>

</div>
<!-- /wp:group -->
