<?php
/**
 * Title: Page Chapo
 * Description: Outputs chapo block for a page
 * Slug: amnesty/chapo
 * Inserter: no
 */

$chapo_text = get_field('chapo_text', get_the_ID()) ?? '';

?>

<div class="chapo">
	<p class="text"><?php echo nl2br(esc_html($chapo_text)); ?></p>
</div>
