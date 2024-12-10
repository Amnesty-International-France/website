<?php

/**
 * Post single partial, document reference
 *
 * @package Amnesty\Branding\Partials
 */

$doc_reference = get_post_meta( get_the_ID(), 'document_ref', true );

if ( ! $doc_reference ) {
	return;
}

?>

<div class="article-docref">
	<strong><?php /* translators: [front] */ esc_html_e( 'Document Reference: ', 'aibrand' ); ?></strong>
	<span><?php echo esc_html( $doc_reference ); ?></span>
</div>
