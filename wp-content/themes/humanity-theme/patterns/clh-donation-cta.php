<?php

/**
 * Title: CLH Donation CTA
 * Description: End of page donation call to action for the Change Their History page.
 * Slug: amnesty/clh-donation-cta
 * Inserter: no
 */

declare(strict_types=1);

?>

<section class="clh-section clh-donation">
	<div class="clh-donation-inner">
		<div class="clh-donation-text">
			<h2 class="clh-donation-title"><?php esc_html_e('SOUTENEZ NOS ACTIONS', 'amnesty'); ?></h2>
			<p><?php esc_html_e("Face à l'injustice et à l'impunité, ces personnes refusent de se taire. Grâce à vous, nous avons les moyens de les défendre et d'agir à leurs côtés. Chaque don nous donne la force de résister en toute indépendance. Aidez-nous à changer leur histoire.", 'amnesty'); ?></p>
		</div>
		<div class="custom-button-block" data-clh-reveal="full">
			<a href="<?php echo esc_url(amnesty_get_clh_donation_url()); ?>" class="custom-button" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('JE FAIS UN DON (nouvel onglet)', 'amnesty'); ?>">
				<div class="content bg-yellow medium">
					<div class="button-label"><?php esc_html_e('JE FAIS UN DON', 'amnesty'); ?></div>
				</div>
			</a>
		</div>
	</div>
</section>
