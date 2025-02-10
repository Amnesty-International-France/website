<?php

/**
 * Title: Post Published Date
 * Description: Output the published date metadata for a post
 * Slug: amnesty/post-published-date
 * Inserter: no
 */

if ( ! amnesty_validate_boolish( get_post_meta( get_the_ID(), 'show_published_date', true ) ) ) {
	return;
}
$post_published = get_the_date('d.m.Y');
?>
<?php do_action( 'amnesty_before_published_date' ); ?>
Publié le <span class="publishedDate" aria-label="<?php /* translators: [front] */ esc_attr_e( 'Post published timestamp', 'amnesty' ); ?>"><time><?php echo esc_html( $post_published ); ?></time></span>
<?php do_action( 'amnesty_after_published_date' ); ?>