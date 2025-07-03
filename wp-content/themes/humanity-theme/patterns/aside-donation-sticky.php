<?php

/**
 * Title: Aside Donation Sticky Pattern
 * Description: Sticky aside block with image
 * Slug: amnesty/aside-donation-sticky
 * Inserter: no
 */

$image_url      = get_template_directory_uri() . '/assets/images/testator-relations-officers.png';
$icon_phone_url = get_template_directory_uri() . '/assets/images/icon-phone.svg';

$amount_monthly_donation = [
	8  => [
		'id'      => 'radio-don-8',
		'checked' => false,
	],
	10 => [
		'id'      => 'radio-don-10',
		'checked' => false,
	],
	15 => [
		'id'      => 'radio-don-15',
		'checked' => true,
	],
];

$amount_punctual_donation = [
	80  => [
		'id'      => 'radio-don-80',
		'checked' => false,
	],
	100 => [
		'id'      => 'radio-don-100',
		'checked' => false,
	],
	250 => [
		'id'      => 'radio-don-250',
		'checked' => true,
	],
];

?>

<aside class="page-donation-aside">
	<div class="donation-calculator">
		<div id="donation-header" class="donation-header">
			<?php echo file_get_contents( get_template_directory() . '/assets/images/icon-don.svg' ); ?>
			<div class="donation-title">
				<h4 class="subtitle">
					Je calcule le montant de mon don
					<span>après déduction fiscale de 66%</span>
				</h4>
			</div>

		</div>
		<div id="donation-body" class="donation-body">
			<div class="give">
				<h4 class="text">SI JE DONNE</h4>
				<div class="input">
					<input type="text"
							id="input-donation"
							class="input-donation"
							placeholder="0">
					<label id="amount-total" for="input-donation">€<span>/mois</span></label>
				</div>
			</div>
			<div class="cost">
				<h4 class="text">CELA ME COÛTE</h4>
				<div class="input yellow">
					<input type="text"
							id="input-donation-simulated"
							class="input-donation-simulated"
							value="0">
					<label id="amount-total" for="input-donation">€<span>/mois</span></label>
				</div>
			</div>

		</div>
		<div class="donation-calculator-footer">
			<a href="https://soutenir.amnesty.fr/b"
				target="_self"
				class="donation-link">
				<?php echo file_get_contents( get_template_directory() . '/assets/images/icon-arrow.svg' ); ?>
				Faire un don
			</a>
			<p class="legend">Vous avez jusqu’au 31 décembre de l'année en cours pour bénéficier d’une réduction d’impôt
				égale à 66% du montant de votre don.</p>
		</div>
</aside>
