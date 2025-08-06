<?php
/**
 * Title: The Chronicle Promo Chapo
 * Description: Outputs chapo block for the chronicle promo page
 * Slug: amnesty/the-chronicle-promo-chapo
 * Inserter: no
 */

$chapo_text = get_field( 'chapo_text', $post->ID ) ?? '';

?>

<div class="chapo">
	<p class="text"><?php echo nl2br(stripslashes($chapo_text)); ?></p>
</div>
