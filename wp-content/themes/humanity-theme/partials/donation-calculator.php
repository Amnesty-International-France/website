<?php

declare(strict_types=1);

$size        = $args['size'] ?? '';
$with_header = $args['with_header'] ?? false;
$with_tabs   = $args['with_tabs'] ?? false;
$with_legend = $args['with_legend'] ?? false;
$href        = $args['href'] ?? '';

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

if ( 'medium' === $size ) {
	$with_header = false;
	$with_legend = false;
}

?>
<div class="donation-calculator
<?php
if ( $size ) {
	echo esc_attr( $size );
}
?>
">
	<?php if ( $with_header ) : ?>
		<div id="donation-header" class="donation-header">
			<?php echo file_get_contents( get_template_directory() . '/assets/images/icon-don.svg' ); ?>
			<div class="donation-title">
				<h4 class="subtitle">
					Je calcule le montant de mon don
					<span>après déduction fiscale de 66%</span>
				</h4>
			</div>
		</div>
	<?php endif ?>
	<?php if ( $with_tabs ) : ?>
		<div class="donation-tabs">
			<div id="punctual" class="punctual hidden">
				Don Ponctuel
			</div>
			<div id="monthly" class="monthly active">
				Don Mensuel
			</div>
		</div>
	<?php endif ?>
	<div id="donation-body" class="donation-body">
		<?php if ( $with_tabs ) : ?>
			<div id="punctual" class="amount-punctual hidden">
				<?php foreach ( $amount_punctual_donation as $key => $value ) : ?>
					<div class="don-radio
						<?php
						if ( $value['checked'] ) :
							?>
							active
							<?php
						endif;
						?>
						">
						<label for="<?php echo esc_attr( $value['id'] ); ?>">
							<?php echo esc_attr( $key ); ?>€
						</label>
						<input type="radio"
								id="<?php echo esc_attr( $value['id'] ); ?>"
							<?php
							if ( $value['checked'] ) :
								?>
								checked
								<?php
							endif;
							?>
								value="<?php echo esc_attr( $key ); ?>">
					</div>
				<?php endforeach; ?>
			</div>
			<div id="monthly" class="amount-monthly active">
				<?php foreach ( $amount_monthly_donation as $key => $value ) : ?>
					<div class="don-radio
						<?php
						if ( $value['checked'] ) :
							?>
							active
							<?php
						endif;
						?>
						">
						<label for="<?php echo esc_attr( $value['id'] ); ?>">
							<?php echo esc_attr( $key ); ?>€
						</label>
						<input type="radio"
								id="<?php echo esc_attr( $value['id'] ); ?>"
							<?php
							if ( $value['checked'] ) :
								?>
								checked
								<?php
							endif;
							?>
								value="<?php echo esc_attr( $key ); ?>">
					</div>
				<?php endforeach; ?>
			</div>
			<div class="amount-free">
				<p>ou montant libre :</p>
				<div class="input-container">
					<input type="text"
							id="input-donation"
							class="input-don-free"
							placeholder="montant du don">
					<label id="amount-total" for="input-donation"></label>
				</div>
			</div>
		<?php else : ?>
			<div class="give">
				<h4 class="text">SI JE DONNE</h4>
				<div class="input">
					<input type="text"
							id="input-donation"
							class="input-donation"
							placeholder="0">
					<label id="amount-total" for="input-donation">€</label>
				</div>
			</div>
			<div class="cost">
				<h4 class="text">CELA ME COÛTE</h4>
				<div class="input yellow">
					<input type="text"
							id="donation-simulated"
							class="input-donation-simulated"
							value="0"
							readonly>
					<label id="amount-total" for="donation-simulated">€</label>
				</div>
			</div>
		<?php endif ?>
	</div>
	<div class="donation-calculator-footer">
		<?php if ( $with_tabs ) : ?>
			<p class="explanation">Grâce à la réduction d'impôts de 66%, votre don ne vous coûtera que</p>
			<h4 id="donation-simulated" class="price-simulated"></h4>
		<?php endif ?>
		<a href="<?php echo esc_url( $href ); ?>"
			target="_self"
			class="donation-link">
			<?php echo file_get_contents( get_template_directory() . '/assets/images/icon-arrow.svg' ); ?>
			Faire un don
		</a>
		<?php if ( $with_legend ) : ?>
			<p class="legend">Vous avez jusqu’au 31 décembre de l'année en cours pour bénéficier d’une réduction d’impôt
				égale à 66% du montant de votre don.</p>
		<?php endif ?>
	</div>
</div>

<div class="parente">
	<Calculette />
</div>
