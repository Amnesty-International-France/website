<?php
/**
 * Title: Page Newsletter Content Pattern
 * Description: Page Newsletter content pattern for the theme
 * Slug: amnesty/page-nl-content
 * Inserter: no
 */

declare(strict_types=1);

$options_theme_newsletter = [];
$email_provided = '';
$inscription_nl_status = '';
$inscription_nl_success = false;
$user = null;
$firstname = null;
$lastname = null;
$phone = null;
$salutation = null;
$postal_code = null;

if (!is_admin() && (!defined('REST_REQUEST') || !REST_REQUEST)) {

    $options_theme_newsletter = [
        'hebdo' => 'L\'Hebdo (newsletter hebdomadaire)',
        'refugees' => 'Réfugiés et migrants',
        'punishment' => 'Torture et peine de mort',
        'expression' => 'Liberté d\'expression',
        'crisis' => 'Crises et conflits armés',
        'discrimination' => 'Discriminations',
        'impunity' => 'Impunité des États et des entreprises',
    ];

    $email_provided = $_GET['email'] ?? '';
    if (empty($email_provided)) {
        wp_redirect(home_url('/').'#newsletter-lead-form');
        exit;
    }

    $inscription_nl_status = $_GET['inscription__nl'] ?? '';
    $inscription_nl_success = $inscription_nl_status === 'success';

    $get_salesforce_user = get_salesforce_user_with_email($email_provided);
    $user = $get_salesforce_user['totalSize'] >= 1 ? $get_salesforce_user['records'][0] : null;
    $firstname = $user['FirstName'] ?? null;
    $lastname = $user['LastName'] ?? null;
    $phone = $user['MobilePhone'] ?? null;
    $salutation = $user['Salutation'] ?? null;
    $postal_code = $user['Code_Postal__c'] ?? null;

    if (isset($_POST['sign_newsletter'])) {
        if (!isset($_POST['newsletter_form_nonce']) ||
            !wp_verify_nonce($_POST['newsletter_form_nonce'], 'newsletter_form_action')) {
            wp_die('Échec de sécurité, veuillez réessayer.');
        }

        $themes = array_map('sanitize_text_field', $_POST['theme'] ?? []);
        $email = $user ? $email_provided : sanitize_email($_POST['newsletter'] ?? '');
        $civility = $salutation ?? sanitize_text_field($_POST['civility'] ?? '');
        $lastname = $lastname ?? sanitize_text_field($_POST['lastname'] ?? '');
        $firstname = $firstname ?? sanitize_text_field($_POST['firstname'] ?? '');
        $phone = $phone ?? sanitize_text_field($_POST['phone'] ?? '');
        $address = sanitize_text_field($_POST['address'] ?? '');
        $address2 = sanitize_text_field($_POST['additional-address'] ?? '');
        $zipcode = $postal_code ?? sanitize_text_field($_POST['zipcode'] ?? '');
        $city = sanitize_text_field($_POST['city'] ?? '');

        $data_to_sf = [
            'Salutation' => $civility,
            'FirstName' => $firstname,
            'LastName' => $lastname,
            'Email' => $email,
            'MobilePhone' => $phone,
            'Ville__c' => $city,
            'Code_Postal__c' => $zipcode,
            'Adresse_Ligne_4__c' => $address,
            'Adresse_Ligne_5__c' => $address2,
            'Optin_Actionaute_Newsletter_mensuelle__c' => true,
            'Optin_Refugies_et_migrants__c' => \in_array('refugees', $themes, true),
            'Optin_torture_et_peine_de_mort__c' => \in_array('punishment', $themes, true),
            'Optin_Liberte_expression__c' => \in_array('expression', $themes, true),
            'Optin_Crises_et_conflits_armes__c' => \in_array('crisis', $themes, true),
            'Optin_Discriminations__c' => \in_array('discrimination', $themes, true),
            'Optin_Impunites_des_etats__c' => \in_array('impunity', $themes, true),
        ];
        $is_salesforce_user = $get_salesforce_user['totalSize'] > 0;

        if (!$is_salesforce_user) {
            $data = [
                ...$data_to_sf,
                'Statut_espace_connecte__c' => 'Non inscrit amnesty.fr',
                'Origine__c' => getenv('AIF_SALESFORCE_ORIGINE__C'),
            ];

            register_salesforce_newsletter($data);
        } else {
            $data = [
                ...$data_to_sf,
                'Optout_toute_communication__c' => false,
            ];

            update_salesforce_users($get_salesforce_user['records'][0]['Id'], $data);
        }

        $lead_on_sf = get_salesforce_nl_lead($email);

        if ($lead_on_sf['totalSize'] > 0) {
            deleting_lead_on_salesforce($lead_on_sf['records'][0]['Id']);
        }

        wp_redirect(add_query_arg([
            'email' => urlencode($email),
            'inscription__nl' => 'success',
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
				<?php
                wp_nonce_field('newsletter_form_action', 'newsletter_form_nonce'); ?>

				<div class="form-mess hidden"></div>
				<div class="form-row theme-newsletter">
					<h3>Choisir les thèmes qui vous intéressent</h3>
					<?php
                    foreach ($options_theme_newsletter as $key => $value) : ?>
						<div class="form-group" data-theme="<?php
                        echo $key; ?>">
							<input type="checkbox" class="hidden" value="<?php
                            echo $key; ?>" name="theme[]">
							<div class="checkbox <?php
                            echo $key; ?> <?php
                            if ($key === 'hebdo') : ?>checked <?php
                            endif; ?>"></div>
							<label><?php
                                echo $value; ?></label>
						</div>
					<?php
                    endforeach; ?>
				</div>
				<?php if (isset($user)) : ?>
					<p>Nous avons detecté un compte connu à l'adresse <?= $email_provided ?>, vos informations seront automatiquement reprises pour votre inscription à notre newsletter.</p>
				<?php else: ?>
					<input type="text"
						   name="newsletter"
						   id="newsletter"
						   placeholder="Email"
						   required
						   value="<?= esc_attr($email_provided); ?>"
					>
				<?php endif; ?>
				<?php if (!$salutation): ?>
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
				<?php if (!$lastname): ?>
				<div class="form-group">
					<input type="text" id="lastname" name="lastname" placeholder="Nom" value="<?= $lastname ?>" required>
				</div>
				<?php endif; ?>
				<?php if (!$firstname): ?>
				<div class="form-group">
					<input type="text" id="firstname" name="firstname" placeholder="Prénom" value="<?= $firstname ?>" required>
				</div>
				<?php endif; ?>
				<?php if (!$phone): ?>
				<div class="form-group">
					<input type="tel" id="phone" name="phone" placeholder="Téléphone" value="<?= $phone ?>">
				</div>
				<?php endif; ?>
				<?php if (!$postal_code): ?>
				<div class="form-group">
					<input type="text" id="address" name="address" placeholder="Adresse">
				</div>
				<div class="form-group">
					<input type="text" id="additional-address" name="additional-address"
						   placeholder="Complément d'adresse">
				</div>
				<div class="form-row">
					<div class="form-group">
						<input type="text" id="zipcode" name="zipcode" placeholder="Code Postal" required>
					</div>
					<div class="form-group">
						<input type="text" id="city" name="city" placeholder="Ville">
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
