<?php

/**
 * Title: Post Reading Time
 * Description: Output reading time for a post
 * Slug: amnesty/post-reading-time
 * Inserter: no
 */

// prevent weird output in the site editor
if (! get_the_ID()) {
    return;
}

$reading_time = calculate_reading_time();

?>

<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="metadata-icon">
	<path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
</svg>
<div>
	Temps de lecture estimé : <?php echo esc_html($reading_time === 1.0 ? "$reading_time minute" : "$reading_time minutes")?>
</div>
