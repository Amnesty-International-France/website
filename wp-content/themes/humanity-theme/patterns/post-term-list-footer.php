<?php

/**
 * Title: Post Term List
 * Description: Output the taxonomy terms for a post
 * Slug: amnesty/post-term-list-footer
 * Inserter: no
 */

$post_terms = wp_get_object_terms( get_the_ID(), get_object_taxonomies( get_post_type() ) );

if ( empty( $post_terms ) ) {
	return;
}

?>

<?php foreach ( $post_terms as $post_term ) : ?>
	<!-- wp:amnesty-core/chip-category {"label":"<?php echo esc_html( $post_term->name ); ?>","link":"<?php echo esc_url( amnesty_term_link( $post_term ) ); ?>","size":"medium","style":"bg-gray"} /-->
<?php endforeach; ?>
