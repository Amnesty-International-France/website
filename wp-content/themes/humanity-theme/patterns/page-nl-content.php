<?php
/**
 * Title: Page Newsletter Content Pattern
 * Description: Page Newsletter content pattern for the theme
 * Slug: amnesty/page-nl-content
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

$email_provided = $_GET['email'] ?? '';

$options_theme_newsletter = [
    'hebdo' => 'L\'Hebdo (newsletter hebdomadaire)',
    'refugees' => 'Réfugiés et migrants',
    'punishment' => 'Torture et peine de mort',
    'expression' => 'Liberté d\'expression',
    'crisis' => 'Crises et conflits armés',
    'discrimination' => 'Discriminations',
    'impunity' => 'Impunité des États et des entreprises',
];

?>

<!-- wp:group {"tagName":"page","className":"page"} -->
<article class="wp-block-group page <?php print esc_attr($class_name ?? ''); ?>">
	<!-- wp:group {"tagName":"section","className":"page-content"} -->
	<section class="wp-block-group page-content <?php echo esc_attr($hero_extra_class); ?> <?php print esc_attr($no_chapo ?? ''); ?>">
		<!-- wp:post-content /-->
		<div class="newsletter-form-container">
			<form id="newsletter-form" class="newsletter-form" action="" method="post">
				<div class="form-mess hidden"></div>
				<div class="form-row theme-newsletter">
					<h3>Choisir les thèmes qui vous intéressent</h3>
					<?php foreach ($options_theme_newsletter as $key => $value) : ?>
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
					   name="newsletter"
					   id="newsletter"
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
				<div class="form-group">
					<label for="phone"></label>
					<input type="tel" id="phone" name="phone"
						   placeholder="Téléphone">
					<div class="input-error hidden"></div>
				</div>
				<div class="form-group">
					<label for="address"></label>
					<input type="text" id="address" name="address"
						   placeholder="Adresse">
					<div class="input-error hidden"></div>
				</div>
				<div class="form-group">
					<label for="additional-address"></label>
					<input type="text" id="additional-address" name="additional-address"
						   placeholder="Complément d'adresse">
					<div class="input-error hidden"></div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="zipcode"></label>
						<input type="text"
							   id="zipcode"
							   name="zipcode"
							   placeholder="Code Postal"
							   required>
						<div class="input-error hidden"></div>
					</div>
					<div class="form-group">
						<label for="city"></label>
						<input type="text"
							   id="city"
							   name="city"
							   placeholder="Ville">
						<div class="input-error hidden"></div>
					</div>
				</div>
				<div class="form-group">
					<input type="hidden" name="type" value="">
				</div>
				<button class="newsletter-form-cta" type="submit" name="sign_newsletter">
					<?php echo file_get_contents(get_template_directory() . '/assets/images/icon-letters.svg'); ?>
					S'abonner
				</button>
			</form>
			<div class="newsletter-legend">
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
