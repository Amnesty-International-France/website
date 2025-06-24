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
			<div class="donation-title">
				<?php echo file_get_contents( get_template_directory() . '/assets/images/icon-don.svg' ); ?>
				<h4 class="subtitle">
					Je calcule le montant de mon don
					<span>après déduction fiscale de 66%</span>
				</h4>
			</div>
			<!--<div class="donation-tabs">
				<div id="punctual" class="punctual hidden">
					Don Ponctuel
				</div>
				<div id="monthly" class="monthly active">
					Don Mensuel
				</div>
			</div>-->
		</div>
		<div id="donation-body" class="donation-calculator-amount">
			<div class="give">
				<div class="text">
					<p>SI JE DONNE</p>
				</div>
				<div class="input">
					<input type="text"
							id="input-donation"
							class="input-donation"
							value="0">
					<label id="amount-total" for="input-donation">€<span>/mois</span></label>
				</div>
			</div>
			<div class="cost">
				<div class="text">
					<p>CELA ME COÛTE</p>
				</div>
			</div>
			<!--<div class="amount-punctual hidden">
				<div id="amount-choices" class="radio-choices">
					<?php /*foreach ( $amount_punctual_donation as $key => $value ) : */ ?>
						<div class="don-radio
						<?php
						/*
									if ( $value['checked'] ) :
						*/
						?>
							active
							<?php
							/*
									endif;
							*/
							?>
						">
							<label for="<?php /*echo esc_attr( $value['id'] ); */ ?>">
								<?php /*echo esc_attr( $key ); */ ?>€
							</label>
							<input type="radio"
									id="<?php /*echo esc_attr( $value['id'] ); */ ?>"
								<?php
								/*
											if ( $value['checked'] ) :
								*/
								?>
									checked
									<?php
									/*
											endif;
									*/
									?>
									value="<?php /*echo esc_attr( $key ); */ ?>">
						</div>
					<?php /*endforeach; */ ?>
				</div>
				<div class="amount-free">
					<p>ou montant libre :</p>
					<div class="input-container">
						<input type="text" id="input-don-free-punctual" class="input-don-free"
								placeholder="montant du don">
						<label id="amount-total" for="input-don-free-punctual">€</label>
					</div>
				</div>
			</div>
			<div class="amount-monthly active">
				<div id="amount-choices" class="radio-choices">
					<?php /*foreach ( $amount_monthly_donation as $key => $value ) : */ ?>
						<div class="don-radio
						<?php
						/*
									if ( $value['checked'] ) :
						*/
						?>
							active
							<?php
							/*
									endif;
							*/
							?>
						">
							<label for="<?php /*echo esc_attr( $value['id'] ); */ ?>">
								<?php /*echo esc_attr( $key ); */ ?>€
							</label>
							<input type="radio"
									id="<?php /*echo esc_attr( $value['id'] ); */ ?>"
								<?php
								/*
											if ( $value['checked'] ) :
								*/
								?>
									checked
									<?php
									/*
											endif;
									*/
									?>
									value="<?php /*echo esc_attr( $key ); */ ?>">
						</div>
					<?php /*endforeach; */ ?>
				</div>
				<div class="amount-free">
					<p>ou montant libre :</p>
					<div class="input-container">
						<input type="text" id="input-don-free-monthly" class="input-don-free"
								placeholder="montant du don">
						<label id="amount-total" for="input-don-free-monthly">€<span>/mois</span></label>
					</div>
				</div>
			</div>-->
		</div>
		<div class="donation-calculator-simulation">
			<p>Grâce à la réduction d'impôts de 66%, votre don ne vous coûtera que</p>
			<p id="price-simulated" class="price-simulated"> €<span>/mois</span></p>
			<a href="#" class="donation-link">
				<?php echo file_get_contents( get_template_directory() . '/assets/images/icon-arrow.svg' ); ?>
				Faire un don
			</a>
		</div>
</aside>
