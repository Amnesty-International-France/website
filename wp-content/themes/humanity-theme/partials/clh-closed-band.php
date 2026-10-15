<?php

/**
 * Bandeau hors campagne.
 *
 * Affiche le total des signatures de l'année, à la place du compteur.
 */

declare(strict_types=1);

$campaign_year = (int) ($args['year'] ?? wp_date('Y'));
$signatures    = (int) ($args['signatures'] ?? 0);
$label         = sprintf(
    /* translators: %s: année de la campagne. */
    _n('signature recueillie en %s', 'signatures recueillies en %s', $signatures, 'amnesty'),
    (string) $campaign_year
);

?>

<div class="clh-band-closed-inner">
	<p class="clh-band-closed-figure">
		<span class="screen-reader-text"><?php echo esc_html(number_format_i18n($signatures) . ' ' . $label); ?></span>
		<span class="clh-band-closed-number" data-clh-count aria-hidden="true"><?php echo esc_html(number_format_i18n($signatures)); ?></span>
		<span aria-hidden="true"><?php echo esc_html($label); ?></span>
	</p>
</div>
