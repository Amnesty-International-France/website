<?php
/**
 * Title: Content callout
 * Description: Outputs a block that "call" the reader's attention to key information.
 * Slug: amnesty/content-callout
 * Inserter: no
 */

$callout_title = get_field( 'callout_title', get_the_ID() ) ?? '';
$callout_text = get_field( 'callout_text', get_the_ID() ) ?? '';

?>

<div class="content-callout">
	<h3 class="content-callout__title"><?php echo esc_html($callout_title); ?></h3>
	<p class="content-callout__text"><?php echo nl2br(esc_html($callout_text)); ?></p>
</div>
