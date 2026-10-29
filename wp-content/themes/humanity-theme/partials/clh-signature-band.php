<?php

declare(strict_types=1);

$variant         = $args['variant'] ?? 'banner';
$is_sticky       = 'sticky' === $variant;
$is_reminder     = 'reminder' === $variant;
$active_campaign = amnesty_is_clh_petition_tunnel_active();
$final_results   = $active_campaign ? null : amnesty_get_clh_campaign_final_results();

// Une campagne sans bilan complet n'affiche aucun bandeau, même en sticky.
if (! $active_campaign && null === $final_results) {
    return;
}

$figures = $active_campaign ? amnesty_get_clh_campaign_figures() : [];

?>

<div class="<?php echo $is_sticky ? 'clh-sticky-bar' : 'clh-band'; ?>" <?php echo $is_sticky ? 'data-clh-sticky-bar hidden' : ($is_reminder ? 'data-clh-reminder' : 'data-clh-reveal'); ?>>
	<?php if ($active_campaign) : ?>
		<div class="<?php echo $is_sticky ? 'clh-sticky-bar-inner' : 'clh-band-inner'; ?>">
			<?php
            get_template_part(
                'partials/clh-campaign-indicators',
                null,
                [
                    'variant'        => $variant,
                    'button_variant' => $is_sticky ? 'bg-black' : 'outline-black',
                    'days'           => $figures['days_remaining'],
                    'span'           => $figures['span_days'],
                    'collected'      => $figures['signatures'],
                    'goal'           => $figures['target'],
                    'percentage'     => $figures['percentage'],
                    'sign_url'       => amnesty_get_clh_petition_tunnel_url(),
                ]
            );
	    ?>
		</div>
	<?php else : ?>
		<?php get_template_part('partials/clh-closed-band', null, $final_results); ?>
	<?php endif; ?>
</div>
