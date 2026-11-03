<?php

/**
 * Title: Page Change Their History Hero
 * Description: Outputs the Change Their History page hero.
 * Slug: amnesty/page-change-their-history-hero
 * Inserter: no
 */

declare(strict_types=1);

$active_campaign = amnesty_is_clh_petition_tunnel_active();
$figures = $active_campaign ? amnesty_get_clh_campaign_figures() : [];

?>

<section class="clh-hero <?php echo $active_campaign ? 'is-running' : 'is-closed'; ?>">
	<div class="clh-hero-inner">
		<div class="clh-hero-stage">
			<?php if ($active_campaign) : ?>
				<p class="clh-hero-days" data-clh-reveal>
					<span class="clh-hero-days-value" data-clh-count aria-hidden="true"><?php echo esc_html(number_format_i18n($figures['days_remaining'])); ?></span>
					<span aria-hidden="true"><?php echo esc_html(_n('jour pour agir', 'jours pour agir', $figures['days_remaining'], 'amnesty')); ?></span>
					<span class="screen-reader-text"><?php echo esc_html(sprintf(/* translators: %s: nombre de jours. */ _n('%s jour pour agir', '%s jours pour agir', $figures['days_remaining'], 'amnesty'), number_format_i18n($figures['days_remaining']))); ?></span>
				</p>
			<?php endif; ?>
			<div class="clh-hero-statement">
				<h1 class="clh-hero-title">
					<span class="clh-hero-title-line"><?php esc_html_e('Changez leur', 'amnesty'); ?></span>
					<span class="clh-hero-title-line is-marked"><?php esc_html_e('histoire', 'amnesty'); ?></span>
				</h1>

				<p class="clh-hero-standfirst"><?php esc_html_e("Partout dans le monde, des personnes résistent à l'injustice et à l'impunité, souvent elles payent le prix de leur courage. Elles ne doivent pas résister seules. Avec votre signature, soutenez-les.", 'amnesty'); ?></p>
			</div>
		</div>
	</div>
</section>

<?php
get_template_part('partials/clh-signature-band', null, [ 'variant' => 'sticky' ]);

if (! is_admin()) {
    add_filter('the_content', 'amnesty_remove_first_hero_from_content', 0);
}
