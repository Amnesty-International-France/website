<?php
/**
 * Title: Page Newsletter Content Pattern
 * Description: Page Newsletter content pattern for the theme
 * Slug: amnesty/page-nl-content
 * Inserter: no
 */

declare(strict_types=1);

$email_provided = '';
$inscription_nl_status = '';
$inscription_nl_success = false;

if (!is_admin() && (!defined('REST_REQUEST') || !REST_REQUEST)) {

    $email_provided = $_GET['email'] ?? '';
    if (empty($email_provided)) {
        wp_redirect(home_url('/').'#newsletter-lead-form');
        exit;
    }

    $inscription_nl_status = $_GET['inscription__nl'] ?? '';
    $inscription_nl_success = $inscription_nl_status === 'success';

    $local_user = get_local_user($email_provided);
    $get_salesforce_user = get_salesforce_user_with_email($email_provided);
    $is_salesforce_user = $get_salesforce_user['totalSize'] > 0;

    if (isset($_POST['sign_newsletter'])) {
        if (!verify_turnstile()) {
            die('Turnstile verification failed.');
        }

        $email = sanitize_email($_POST['newsletter'] ?? '');
        $civility = sanitize_text_field($_POST['civility'] ?? '');
        $lastname = sanitize_text_field($_POST['lastname'] ?? '');
        $firstname = sanitize_text_field($_POST['firstname'] ?? '');
        $zipcode = sanitize_text_field($_POST['zipcode'] ?? '');
        $country = sanitize_text_field($_POST['country'] ?? '');

        $local_user = get_local_user($email);
        $get_salesforce_user = get_salesforce_user_with_email($email);
        $is_salesforce_user = $get_salesforce_user['totalSize'] > 0;

        if ($local_user !== false) {
            $data = [
                'Email' => $local_user->email,
                'Salutation' => $local_user->civility,
                'Code_Postal__c' => $local_user->postal_code,
                'FirstName' => $local_user->firstname,
                'LastName' =>  $local_user->lastname,
                'Pays__c' => $local_user->country,
                'Optin_Actionaute_Newsletter_mensuelle__c' => true,
            ];
            if (!$is_salesforce_user) {
                post_salesforce_users([
                    ...$data,
                    'Origine__c' => getenv('AIF_SALESFORCE_ORIGINE__C'),
                ]);
            } else {
                update_salesforce_users($get_salesforce_user['records'][0]['Id'], [
                    ...$data,
                    'Optout_toute_communication__c' => false,
                ]);
            }
        }

        if (!$local_user) {
            if ($is_salesforce_user) {
                $sf_user = $get_salesforce_user['records'][0];
                insert_user(
                    $sf_user['Civility__c'] ?? null,
                    $sf_user['FirstName'],
                    $sf_user['LastName'],
                    $sf_user['Email'],
                    $sf_user['Pays__c'] ?? null,
                    $sf_user['Code_Postal__c'] ?? null,
                    $sf_user['MobilePhone'] ?? null
                );
            } else {
                insert_user($civility, $firstname, $lastname, $email, $country, $zipcode, null);
                post_salesforce_users(
                    [
                        'Salutation' => $civility,
                        'FirstName' => $firstname,
                        'LastName' => $lastname,
                        'Email' => $email,
                        'Code_Postal__c' => $zipcode,
                        'Pays__c' => $country,
                        'Optin_Actionaute_Newsletter_mensuelle__c' => true,
                    ]
                );
            }
        }

        $lead_on_sf = get_salesforce_nl_lead($email);

        if ($lead_on_sf['totalSize'] > 0) {
            deleting_lead_on_salesforce($lead_on_sf['records'][0]['Id']);
        }

        wp_redirect(add_query_arg([
            'email' => urlencode($email),
            'inscription__nl' => 'success',
            'gtm_type' => 'inscription',
            'gtm_name' => 'newsletter',
        ], home_url('/newsletter')));
        exit;
    }
}
?>

<!-- wp:group {"tagName":"page","className":"page"} -->
<article class="wp-block-group page <?php
print esc_attr($class_name ?? ''); ?>">
	<!-- wp:group {"tagName":"section","className":"page-content"} -->
	<section class="wp-block-group page-content no-featured-image no-chapo">
		<!-- wp:post-content /-->
		<div class="newsletter-form-container">
			<?php
            if ($inscription_nl_status): ?>
				<div class="newsletter-popin <?php
                echo $inscription_nl_status; ?>">
					<?php
                    if ($inscription_nl_success): ?>
						<p>Merci de vous être inscrit·e à la newsletter !</p>
					<?php
                    endif; ?>
				</div>
			<?php else: ?>
			<form id="newsletter-form" class="newsletter-form" action="" method="post" name="newsletter-form">
				<div class="cf-turnstile" data-sitekey="<?php echo esc_attr(TURNSTILE_SITE_KEY); ?>"></div>
				<div class="form-mess hidden"></div>
					<?php if (isset($local_user, $is_salesforce_user) && !$local_user && !$is_salesforce_user) : ?>
					<div class="form-group civility">
						<label class="civility-label">Civilité :</label>
						<div class="civilities">
							<label for="civility_m">M.</label>
							<input type="radio" id="civility_m" name="civility" value="M." checked>
							<label for="civility_mme">Mme</label>
							<input type="radio" id="civility_mme" name="civility" value="Mme">
							<label for="civility_other">Autre</label>
							<input type="radio" id="civility_other" name="civility" value="Autre">
						</div>
						<div class="input-error-civility hidden"></div>
					</div>
					<?php endif; ?>

					<input type="email"
						   name="newsletter"
						   id="newsletter"
						   placeholder="Email"
						   required
						   value="<?= esc_attr($email_provided); ?>"
					>

				<?php if (isset($local_user, $is_salesforce_user) && !$local_user && !$is_salesforce_user) : ?>
				<div class="form-group">
					<input type="text" id="lastname" name="lastname" placeholder="Nom" required>
				</div>

				<div class="form-group">
					<input type="text" id="firstname" name="firstname" placeholder="Prénom" required>
				</div>

				<div class="form-row">
					<div class="form-group">
						<input type="text" id="zipcode" name="zipcode" placeholder="Code Postal" required>
					</div>
					<div class="form-group">
						<select class="country-input " name="country">
							<option value=""><?php _e('Pays*', 'textdomain'); ?></option>
							<?php
                            $countries = get_posts([
                                'post_type' => 'fiche_pays',
                                'posts_per_page' => -1,
                                'orderby' => 'title',
                                'order' => 'ASC',
                            ]);

				    foreach ($countries as $country) :
				        $country_name = get_the_title($country->ID);
				        ?>
								<option value="<?php echo esc_attr($country_name); ?>" <?php if (esc_attr($country_name) === 'France') :?> selected="selected"<?php endif;?>>
									<?php echo esc_html(ucwords(strtolower($country_name))); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<?php endif; ?>

				<button class="newsletter-form-cta" type="submit" name="sign_newsletter">
					<?php
                    echo file_get_contents(get_template_directory() . '/assets/images/icon-letters.svg'); ?>
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
			<?php endif; ?>
		</div>
	</section>
	<!-- /wp:group -->
</article>
<!-- /wp:group -->
