<?php

/**
 * Post single partial, recipients
 *
 * @package Amnesty\Partials
 */

$recipients = get_letter_recipients();

if ( ! $recipients ) {
	return;
}

?>

<details class="article-recipients">
	<summary><?php /* translators: [front] */ esc_html_e( 'View Recipients', 'aibrand' ); ?></summary>
	<div><?php array_map( 'display_letter_recipient', $recipients ); ?></div>
</details>
