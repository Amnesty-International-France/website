<?php
/**
 * Title: Page Discover Chronicle Content Pattern
 * Description: Page discover chronicle content pattern for the theme
 * Slug: amnesty/page-discover-chronicle-content
 * Inserter: no
 */

declare(strict_types=1);

$hero_extra_class = !has_post_thumbnail() ? 'no-featured-image' : '';
$no_chapo = !has_block('amnesty-core/chapo') ? 'no-chapo' : '';

$countries = get_posts(
	[
		'post_type' => 'fiche_pays',
		'posts_per_page' => -1,
		'orderby' => 'title',
		'order' => 'ASC',
	]
);

if (!isset($_POST['sign_discover_chronicle'])) {
	return;
}

if (!isset($_POST['discover_chronicle_nonce']) ||
	!wp_verify_nonce($_POST['discover_chronicle_nonce'], 'discover_chronicle_action')) {
	wp_die('Sécurité : formulaire invalide.');
}

$themes = isset($_POST['theme']) ? array_map('sanitize_text_field', (array) $_POST['theme']) : [];
$discover_chronicle = sanitize_email($_POST['discover-chronicle'] ?? '');
$civility = sanitize_text_field($_POST['civility'] ?? '');
$lastname = sanitize_text_field($_POST['lastname'] ?? '');
$firstname = sanitize_text_field($_POST['firstname'] ?? '');
$street_address = sanitize_text_field($_POST['street-address'] ?? '');
$address_complement = sanitize_text_field($_POST['address_complement'] ?? '');
$address_complement_bis = sanitize_text_field($_POST['address_complement_bis'] ?? '');
$zipcode = sanitize_text_field($_POST['zipcode'] ?? '');
$city = sanitize_text_field($_POST['city'] ?? '');
$country = sanitize_text_field($_POST['country'] ?? '');
$email = sanitize_email($_POST['email'] ?? '');
$phone = sanitize_text_field($_POST['phone'] ?? '');
$additional_address = sanitize_text_field($_POST['additional-address'] ?? '');
$type = sanitize_text_field($_POST['type'] ?? '');

$form_data = [
	'themes' => $themes,
	'discover_chronicle' => $discover_chronicle,
	'civility' => $civility,
	'lastname' => $lastname,
	'firstname' => $firstname,
	'street_address' => $street_address,
	'address_complement' => $address_complement,
	'address_complement_bis' => $address_complement_bis,
	'zipcode' => $zipcode,
	'city' => $city,
	'country' => $country,
	'email' => $email,
	'phone' => $phone,
	'additional_address' => $additional_address,
	'type' => $type,
];

?>

<!-- wp:group {"tagName":"page","className":"page"} -->
<article class="wp-block-group page <?php print esc_attr($class_name ?? ''); ?>">
	<!-- wp:group {"tagName":"section","className":"page-content"} -->
	<section class="wp-block-group page-content <?php echo esc_attr($hero_extra_class); ?> <?php print esc_attr($no_chapo ?? ''); ?>">
		<!-- wp:post-content /-->
		<div class="discover-chronicle-form-container">
			<form id="discover-chronicle-form" class="discover-chronicle-form" action="" method="post">
				<div class="form-mess hidden"></div>
				<div class="form-row theme-discover-chronicle">
					<h3>Choisir les thèmes qui vous intéressent</h3>
					<?php foreach ($options_theme_discover-chronicle as $key => $value) : ?>
						<div class="form-group" data-theme="<?php echo $key; ?>">
							<input type="checkbox" class="hidden" value="<?php echo $key; ?>" name="theme">
							<div class="checkbox <?php echo $key; ?> <?php if ($key === 'hebdo') :?>checked <?php endif;?>"
								 <?php if ($key === 'hebdo') :?>checked <?php endif;?>
							></div>
							<label><?php echo $value; ?></label>
						</div>
					<?php endforeach; ?>
				</div>
				<input type="text"
					   name="discover-chronicle"
					   id="discover-chronicle"
					   placeholder="Email"
					   required
					   value="<?php echo $email_provided; ?>"
				>
				<div class="form-group civility">
					<label class="civility-label">Civilité :</label>
					<div class="civilities">
						<label for="civility_m">M.</label>
						<input type="radio" id="civility_m"
							   name="civility"
							   value="M."
							   checked>
						<label for="civility_mme">Mme</label>
						<input type="radio" id="civility_mme"
							   name="civility"
							   value="Mme">
						<label for="civility_other">Autre</label>
						<input type="radio" id="civility_other"
							   name="civility"
							   value="Autre">
					</div>
					<div class="input-error-civility hidden"></div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="lastname"></label>
						<input type="text" id="lastname" name="lastname"
							   placeholder="Nom" required>
						<div class="input-error hidden"></div>
					</div>
					<div class="form-group">
						<label for="firstname"></label>
						<input type="text" id="firstname" name="firstname"
							   placeholder="Prénom" required>
						<div class="input-error hidden"></div>
					</div>
				</div>
				<div class="form-group">
					<label for="street-address"></label>
					<input type="text" id="street-address" name="street-address"
						   placeholder="N° et libellé de voie" required>
					<div class="input-error hidden"></div>
				</div>
				<div class="form-group">
					<label for="address_complement"></label>
					<input type="text" id="address_complement" name="address_complement"
						   placeholder="Complément d'adresse">
					<div class="input-error hidden"></div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="address_complement_bis"></label>
						<input type="text" id="address_complement_bis" name="address_complement_bis"
							   placeholder="Lieu dit">
						<div class="input-error hidden"></div>
					</div>
					<div class="form-group">
						<label for="zipcode"></label>
						<input type="text"
							   id="zipcode"
							   name="zipcode"
							   placeholder="Code Postal"
							   required>
						<div class="input-error hidden"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="city"></label>
						<input type="text"
							   id="city"
							   name="city"
							   placeholder="Ville"
							   required>
						<div class="input-error hidden"></div>
					</div>
					<div class="form-group country-selection">
						<select class="country-input " name="country">
							<option value=""><?php _e('Pays*', 'textdomain'); ?></option>
							<?php
							foreach ($countries as $country) :
								$country_name = get_the_title($country->ID);
								?>
								<option value="<?php echo esc_attr($country_name); ?>"
									<?php
									if (esc_attr($country_name) === 'France') :
										?>
										selected="selected"
									<?php
									endif;
									?>
								>
									<?php echo esc_html(ucwords(strtolower($country_name))); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="email"></label>
						<input type="email" id="email" name="email"
							   placeholder="Adresse" required>
						<div class="input-error hidden"></div>
					</div>
					<div class="form-group">
						<label for="phone"></label>
						<input type="tel" id="phone" name="phone"
							   placeholder="Téléphone">
						<div class="input-error hidden"></div>
					</div>
				</div>
				<div class="form-group">
					<label for="additional-address"></label>
					<input type="text" id="additional-address" name="additional-address"
						   placeholder="Complément d'adresse">
					<div class="input-error hidden"></div>
				</div>
				<div class="form-group">
					<input type="hidden" name="type" value="">
				</div>
				<?php wp_nonce_field('discover_chronicle_action', 'discover_chronicle_nonce'); ?>
				<button class="discover-chronicle-form-cta" type="submit" name="sign_discover_chronicle">
					Envoyer ma demande
				</button>
			</form>
			<div class="discover-chronicle-legend">
				<p>Les données personnelles collectées sur ce formulaire sont traitées par l’association Amnesty
					International France (AIF), responsable du traitement. Ces données vont nous permettre de vous
					envoyer nos propositions d’engagement, qu’elles soient militantes ou financières. Notre politique de
					confidentialité détaille la manière dont Amnesty International France, en sa qualité de responsable
					de traitement, traite et protège vos données personnelles collectées conformément aux dispositions
					de la Loi du 6 janvier 1978 relative à l’informatique, aux fichiers et aux libertés dite Loi «
					Informatique et Libertés », et au Règlement européen du 25 mai 2018 sur la protection des données («
					RGPD »). Pour toute demande, vous pouvez contacter le service membres et donateurs d’AIF à l’adresse
					mentionnée ci-dessus, par email smd@amnesty.fr ou par téléphone 01 53 38 65 80. Vous pouvez
					également introduire une réclamation auprès de la CNIL. Pour plus d’information sur le traitement de
					vos données personnelles, veuillez consulter notre politique de confidentialité.</p>
			</div>
		</div>
	</section>
	<!-- /wp:group -->
</article>
<!-- /wp:group -->
