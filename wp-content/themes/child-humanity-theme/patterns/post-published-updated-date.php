<?php

/**
 * Title: Post Published Date
 * Description: Output the published and updated date metadata for a post
 * Slug: amnesty/post-published-updated-date
 * Inserter: no
 */

$post_published = get_the_date('d.m.Y');
$post_updated = get_post_meta( get_the_ID(), 'amnesty_updated', true );
if ( $post_updated ) {
    $post_updated = date( 'd.m.Y', strtotime( $post_updated ) );
}
?>

<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="metadata-icon">
    <path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z" clip-rule="evenodd" />
</svg>
<?php do_action( 'amnesty_before_published_date' ); ?>
Publié le <span class="publishedDate" aria-label="<?php /* translators: [front] */ esc_attr_e( 'Post published timestamp', 'amnesty' ); ?>"><time><?php echo esc_html( $post_published ); ?></time></span>
<?php do_action( 'amnesty_after_published_date' ); ?>
<?php if( $post_updated ): ?>
    | Mis à jour le <span class="updatedDate" aria-label="<?php /* translators: [front] */ esc_attr_e( 'Post updated timestamp', 'amnesty' ); ?>"><time><?php echo esc_html( $post_updated ); ?></time></span>
<?php endif ?>