<?php

function aif_account_creation_result($error_message = '', $no_access = false)
{
    return [
        'error_message' => $error_message,
        'no_access' => $no_access,
    ];
}

function aif_account_creation_salesforce_error_result(WP_Error $error)
{
    aif_log_salesforce_error($error);

    return aif_account_creation_result(AIF_SALESFORCE_SERVICE_UNAVAILABLE_MESSAGE);
}

function aif_handle_account_creation_request()
{
    if ('POST' !== $_SERVER['REQUEST_METHOD']) {
        return aif_account_creation_result();
    }

    $turnstile_error = verify_turnstile();
    if (null !== $turnstile_error) {
        return aif_account_creation_result(turnstile_friendly_error($turnstile_error));
    }

    $email = sanitize_email($_POST['email'] ?? '');

    if (empty($email)) {
        return aif_account_creation_result('Veuillez renseigner votre email');
    }

    if (!is_email($email)) {
        return aif_account_creation_result("L'email renseigné est invalide");
    }

    $sf_member = aif_validate_salesforce_member(get_salesforce_member_data($email));

    if (is_wp_error($sf_member)) {
        return aif_account_creation_salesforce_error_result($sf_member);
    }

    $verification_url = add_query_arg([
        'user' => rawurlencode($email),
    ], get_permalink(get_page_by_path('verifier-votre-email')));

    // Addresses without donor space access and verified or staff accounts
    // (below) get the same outcome as a successful creation, so the page
    // reveals neither. Nothing is created and no code is sent for them.
    if (aif_is_salesforce_contact_absent($sf_member) || !has_access_to_donation_space($sf_member)) {
        wp_redirect($verification_url);
        exit;
    }

    $user = get_salesforce_user_data($sf_member->Id);

    if (is_wp_error($user)) {
        return aif_account_creation_salesforce_error_result($user);
    }

    if (!is_object($user)) {
        return aif_account_creation_salesforce_error_result(
            aif_create_salesforce_invalid_response_error('GET', 'contact')
        );
    }

    $user_id = aif_prepare_account_for_verification($email, $user->FirstName ?? '', $user->LastName ?? '');

    if (is_wp_error($user_id)) {
        wp_redirect($verification_url);
        exit;
    }

    if (!aif_send_verification_code($user_id)) {
        return aif_account_creation_result(AIF_VERIFICATION_CODE_SEND_ERROR_MESSAGE);
    }

    wp_redirect($verification_url);
    exit;
}

$account_creation_result = aif_handle_account_creation_request();
$error_message = $account_creation_result['error_message'];
$error_no_access_to_donor_space = $account_creation_result['no_access'];

?>

<?php get_header(); ?>

<main class="aif-container--main">
	<header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
		<h1 class="aif-mb1w">Mon espace</h1>
	</header>

	<div class="aif-container--form">
		<h2> Je crée mon compte </h2>
		<p class="">Votre compte "Mon Espace" vous permet de suivre les actualités de la vie démocratique d'Amnesty
			International France, d'agir et de vous mobiliser pour les droits humains, ainsi que de suivre facilement
			vos dons et votre adhésion (modifier vos coordonnées personnelles, votre RIB et éditer des duplicatas de vos
			reçus fiscaux).</p>
		<?php
        if (!empty($error_message)) {
            $title = 'Une erreur est survenue';
            aif_include_partial('alert', [
                'state' => 'error',
                'title' => $title,
                'content' => $error_message]);
        }

if (!empty($error_no_access_to_donor_space)) {
    $title = 'L’adresse email renseignée ne trouve pas de correspondance dans notre système.';
    $content = 'Devenez donateur en <a href="#" class="aif-link--secondary">réalisant un don </a> ou <a
                                                    class="aif-link--secondary" href="mailto:smd@amnesty.fr">contactez le Service
                                                    membres et donateurs</a> si vous pensez que c’est une erreur.';

    aif_include_partial('alert', [
        'state' => 'error',
        'title' => $title,
        'content' => $content]);
}

?>

		<section>
			<h3 class="aif-sr-only">S'inscrire</h3>
			<form class="aif-form-container" action="" method="POST">
				<div>
					<div class="cf-turnstile"
					     data-callback="aifTurnstileSuccess"
					     data-error-callback="aifTurnstileFailure"
					     data-expired-callback="aifTurnstileFailure"
					     data-timeout-callback="aifTurnstileFailure"
					     data-unsupported-callback="aifTurnstileFailure"
					     data-sitekey="<?php echo esc_attr(aif_turnstile_site_key()); ?>"></div>
					<label for="email">Adresse email (obligatoire) :</label>
					<input type="email" class="aif-input" id="email" name="email" aria-describedby="email-help-message"
					       placeholder="adresse@mail.fr" autocomplete="email" aria-required="true" required>
					<?php
            aif_include_partial('info-message', [
                'id' => 'email-help-message',
                'content' => 'Commencez par renseigner l’adresse e-mail utilisé lors de la réalisation de votre adhésion ou de votre don.']);
?>
				</div>
				<button class="btn  aif-mt1w aif-button--full" type="submit" id="submit-btn">Créer mon compte
				</button>
			</form>

			<p class="aif-mt1w aif-text-small">
				Les données personnelles collectées sur ce formulaire sont traitées par l’association Amnesty
				International France (AIF), responsable du traitement. Ces données vont nous permettre de vous envoyer
				nos propositions d’engagement, qu’elles soient militantes ou financières. Notre politique de
				confidentialité détaille la manière dont Amnesty International France, en sa qualité de responsable de
				traitement, traite et protège vos données personnelles collectées conformément aux dispositions de la
				Loi du 6 janvier 1978 relative à l’informatique, aux fichiers et aux libertés dite Loi « Informatique et
				Libertés », et au Règlement européen du 25 mai 2018 sur la protection des données (« RGPD »). Pour toute
				demande, vous pouvez contacter le service membres et donateurs d’AIF à l’adresse mentionnée ci-dessus,
				par email smd@amnesty.fr. Vous pouvez également introduire une réclamation auprès de la CNIL. Pour plus
				d’information sur le traitement de vos données personnelles, veuillez consulter notre politique de
				confidentialité.
			</p>
		</section>

		<section class="aif-bg-primary aif-p1w aif-mt1w">
			<h3> Vous n’êtes pas encore donateur ? </h3>
			<p>Votre soutien servira à soutenir le travail d’enquête ainsi que l'ensemble des actions d'Amnesty
				International.</p>
			<a href="" class="btn btn--dark aif-button--full"> Nous soutenir</a>
		</section>

		<section class="aif-mt2w aif-mb1w">
			<h3> Vous avez déja un compte ? </h3>
			<a href="<?php echo get_permalink(get_page_by_path('connectez-vous')) ?>"
			   class="btn  btn--white aif-button--full ">Se connecter</a>
		</section>
	</div>
</main>

<?php get_footer(); ?>
