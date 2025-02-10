<?php

/**
 * Title: Post Updated Date
 * Description: Output the "updated date" for a post
 * Slug: amnesty/post-updated-date
 * Inserter: no
 */

if ( ! amnesty_validate_boolish( get_post_meta( get_the_ID(), 'show_updated_date', true ) ) ) {
	return;
}

$post_updated = get_post_meta( get_the_ID(), 'amnesty_updated', true );

if ( $post_updated ) {
    $post_updated = date( 'd.m.Y', strtotime( $post_updated ) );
}

if ( ! $post_updated ) {
	return;
}

?>
| Mis à jour le <span class="updatedDate" aria-label="<?php /* translators: [front] */ esc_attr_e( 'Post updated timestamp', 'amnesty' ); ?>"><time><?php echo esc_html( $post_updated ); ?></time></span>