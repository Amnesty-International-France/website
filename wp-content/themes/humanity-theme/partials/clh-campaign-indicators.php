<?php

declare(strict_types=1);

$variant    = $args['variant'] ?? 'banner';
$days       = max(0, (int) ($args['days'] ?? 0));
$span       = max(0, (int) ($args['span'] ?? 0));
$collected  = max(0, (int) ($args['collected'] ?? 0));
$goal       = max(0, (int) ($args['goal'] ?? 0));
$sign_url   = (string) ($args['sign_url'] ?? '');
$countdown  = (bool) ($args['show_countdown'] ?? true);
$button     = (string) ($args['button_variant'] ?? 'bg-yellow');
$percentage = max(0, min(100, (int) ($args['percentage'] ?? 0)));

// L'anneau se vide au fil de la campagne : plein au premier jour, vide à la date de fin.
$ring = $span > 0 ? min($days / $span, 1) : 1;

?>

<div class="clh-indicators clh-indicators--<?php echo esc_attr($variant); ?>">
	<?php if ($countdown) : ?>
	<p
		class="clh-countdown"
		style="--clh-ring: <?php echo esc_attr((string) round($ring, 4)); ?>"
	>
		<svg class="clh-countdown-ring" viewBox="0 0 100 100" aria-hidden="true" focusable="false">
			<circle class="clh-countdown-track" cx="50" cy="50" r="45" pathLength="1"/>
			<circle class="clh-countdown-arc" cx="50" cy="50" r="45" pathLength="1"/>
		</svg>
		<span
			class="clh-countdown-value"
			<?php if ($span > $days) : ?>
				data-clh-count
				data-clh-count-from="<?php echo esc_attr((string) $span); ?>"
				data-clh-count-step
			<?php endif; ?>
			aria-hidden="true"
		><?php echo esc_html(number_format_i18n($days)); ?></span>
		<span class="clh-countdown-label" aria-hidden="true"><?php echo esc_html(_n('jour', 'jours', $days, 'amnesty')); ?></span>
		<span class="screen-reader-text"><?php echo esc_html(sprintf( /* translators: %s: nombre de jours. */ _n('%s jour restant', '%s jours restants', $days, 'amnesty'), number_format_i18n($days))); ?></span>
	</p>
	<?php endif; ?>

	<div class="clh-signatures">
		<p class="clh-signatures-count">
			<span class="screen-reader-text"><?php echo esc_html(number_format_i18n($collected)); ?></span>
			<span class="clh-signatures-collected" data-clh-count aria-hidden="true"><?php echo esc_html(number_format_i18n($collected)); ?></span>
			<span class="clh-signatures-goal">
				<?php
                printf(
                    /* translators: %s: nombre de signatures visé. */
                    esc_html__('sur un objectif de %s signatures', 'amnesty'),
                    esc_html(number_format_i18n($goal))
                );
?>
			</span>
		</p>
		<div
			class="progress-bar-container"
			role="progressbar"
			aria-valuenow="<?php echo esc_attr((string) $percentage); ?>"
			aria-valuemin="0"
			aria-valuemax="100"
			aria-label="<?php esc_attr_e('Signatures recueillies sur l\'objectif de la campagne', 'amnesty'); ?>"
		>
			<div class="progress-bar" style="width: <?php echo esc_attr((string) $percentage); ?>%;"></div>
		</div>
		<p class="clh-signatures-percent" data-clh-count aria-hidden="true">
			<?php echo esc_html($percentage . ' %'); ?>
		</p>
	</div>

	<?php if ($sign_url) : ?>
		<div class="clh-indicators-action">
			<div class="custom-button-block">
				<a href="<?php echo esc_url($sign_url); ?>" class="custom-button">
					<div class="content <?php echo esc_attr($button); ?> medium">
						<div class="button-label"><?php esc_html_e('Je signe', 'amnesty'); ?></div>
					</div>
				</a>
			</div>
		</div>
	<?php endif; ?>
</div>
