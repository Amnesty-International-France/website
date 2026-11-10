<?php

/**
 * Title: CLH Petitions Grid
 * Description: Grid of the Change Their History campaign petitions.
 * Slug: amnesty/clh-petitions-grid
 * Inserter: no
 */

declare(strict_types=1);

$page_id         = (int) get_the_ID();
$active_campaign = amnesty_get_active_clh_campaign_for_page($page_id);
$petitions       = amnesty_get_clh_grid_petitions();

if (! $petitions) {
    return;
}

$end_date     = (string) get_field('end_date_highlight_clh', $page_id);
$edition_year = $end_date ? (int) wp_date('Y', strtotime($end_date)) : (int) wp_date('Y');

if (! $active_campaign) {
    $final_results = amnesty_get_clh_campaign_final_results();
    $edition_year  = $final_results['year'] ?? $edition_year;
}

$heading = $active_campaign
    ? sprintf(
        /* translators: %s: année de la campagne. */
        __('Les situations que nous défendons en %s', 'amnesty'),
        (string) $edition_year
    )
    : sprintf(
        /* translators: 1: nombre de situations, 2: année de la campagne. */
        __('Les %1$s situations de la campagne %2$s', 'amnesty'),
        number_format_i18n(count($petitions)),
        (string) $edition_year
    );

$custom_heading = trim((string) get_field(
    $active_campaign ? 'petitions_heading_active_clh' : 'petitions_heading_closed_clh',
    $page_id
));
$heading = $custom_heading ?: $heading;

?>

<section class="clh-section clh-petitions" id="clh-petitions">
	<h2 class="clh-section-title"><?php echo esc_html($heading); ?></h2>

	<ul class="clh-petitions-grid <?php echo $active_campaign ? '' : 'clh-petitions-grid--closed'; ?>">
		<?php foreach ($petitions as $petition) : ?>
			<li class="clh-petitions-grid-item">
				<?php
                get_template_part(
                    'partials/petition-card-change-their-history',
                    null,
                    [
                        'petition'  => $petition,
                        'direction' => 'portrait',
                        'closed'    => ! $active_campaign || ! amnesty_is_petition_not_expired($petition['id']),
                    ]
                );
		    ?>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
