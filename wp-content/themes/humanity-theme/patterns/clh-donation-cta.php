<?php

/**
 * Title: CLH Donation CTA
 * Description: End of page donation call to action for the Change Their History page.
 * Slug: amnesty/clh-donation-cta
 * Inserter: no
 */

declare( strict_types = 1 );

?>

<section class="clh-section clh-donation">
	<div class="clh-donation-inner">
		<div class="clh-donation-text">
			<h2 class="clh-donation-title"><?php esc_html_e( 'Soutenez Changez leur histoire', 'amnesty' ); ?></h2>
			<p><?php esc_html_e( 'Amnesty International France ne reçoit aucune subvention publique. La campagne vit des dons de ses soutiens : chaque don finance les enquêtes, les actions et les lettres qui portent la voix des personnes défendues par la campagne.', 'amnesty' ); ?></p>
		</div>
		<div class="custom-button-block" data-clh-reveal="full">
			<a href="<?php echo esc_url( home_url( '/faire-un-don/' ) ); ?>" class="custom-button">
				<div class="content bg-yellow medium">
					<div class="button-label"><?php esc_html_e( 'Je donne', 'amnesty' ); ?></div>
				</div>
			</a>
		</div>
	</div>
</section>
