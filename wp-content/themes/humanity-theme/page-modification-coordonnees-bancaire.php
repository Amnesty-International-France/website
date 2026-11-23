<?php

$SF_membre_data = aif_get_request_salesforce_member();
$sf_user_ID = $SF_membre_data->Id;

$SF_User = aif_require_salesforce_object(get_salesforce_user_data($sf_user_ID), 'contact', 'Id');
$SEPA_mandates = aif_require_salesforce_records(
    get_salesforce_user_SEPA_mandate($sf_user_ID),
    'sepa_mandates'
);

$actifMandate  = null;
$day_of_payment = null;
$has_error = false;

$ibanBlocks = [];
$formattedIban = '';
$current_iban = '';

$actifMandate = get_active_sepa_mandate($SEPA_mandates->records);
$has_valid_mandate = $SF_membre_data->hasMandatActif && null !== $actifMandate;


if ($has_valid_mandate) {
    $day_of_payment = date('d', strtotime($actifMandate->Date_paiement_Avenir__c));
    $ibanBlocks = str_split($actifMandate->Tech_Iban__c, 4);
    $formattedIban = implode(' ', $ibanBlocks);
    $last4IBANDigit = substr($actifMandate->Tech_Iban__c, -4);
    $current_iban = aif_normalize_iban($actifMandate->Tech_Iban__c);
}

$pending_message = 'Votre demande a déjà été envoyée, elle est en cours de traitement.';
$pending_transient_key = aif_iban_request_pending_key($sf_user_ID);

// IBAN affiché au moment de l'envoi de la demande. La demande est en cours tant qu'il n'a pas
// été remplacé par un autre IBAN (un IBAN vide, par exemple pendant un changement de mandat, ne lève pas le blocage).
$iban_at_request = get_transient($pending_transient_key);
$has_pending_request = false !== $iban_at_request
    && ('' === $current_iban || $iban_at_request === $current_iban);

if (false !== $iban_at_request && !$has_pending_request) {
    delete_transient($pending_transient_key);
}

$is_post = isset($_SERVER['REQUEST_METHOD']) && 'POST' === $_SERVER['REQUEST_METHOD'];
$request_sent = !$is_post && isset($_GET['iban_sent']) && '1' === $_GET['iban_sent'] && false !== $iban_at_request;

$user_status = aif_get_user_status($SF_membre_data);

if ($is_post && isset($_POST['iban_nonce']) && isset($_POST['iban'])) {
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['iban_nonce'])), 'iban_form')) {
        die('Invalid nonce.');
    }

    unset($_POST['iban_nonce'], $_POST['_wp_http_referer']);
    $new_iban = aif_normalize_iban(sanitize_text_field(wp_unslash($_POST['iban'])));

    // Si une demande est déjà en cours, rien n'est envoyé : le message « demande en cours » est affiché plus bas.
    if (!$has_pending_request) {
        if ('' === $new_iban) {
            $error_message = 'Veuillez renseigner un IBAN.';
            $has_error = true;
        } elseif ($new_iban === $current_iban) {
            $error_message = 'L’IBAN saisi est identique à votre IBAN actuel. Aucune modification n’a été envoyée.';
            $has_error = true;
        } else {
            // Verrou posé avant l'appel Salesforce pour bloquer un double envoi simultané.
            set_transient($pending_transient_key, $current_iban, DAY_IN_SECONDS);
            $request_result = create_duplicate_update_IBAN_request($sf_user_ID, $new_iban);

            if (is_wp_error($request_result)) {
                delete_transient($pending_transient_key);
                aif_salesforce_service_unavailable($request_result);
            }

            if (is_object($request_result) && !empty($request_result->success)) {
                // Post/Redirect/Get : un rechargement ne renvoie plus le formulaire.
                wp_safe_redirect(add_query_arg('iban_sent', '1', get_permalink()));
                exit;
            }

            delete_transient($pending_transient_key);
            $error_message = 'Un problème technique est survenu. Merci de réessayer plus tard';
        }
    }
}

if ($request_sent) {
    $success_message_title = 'Votre demande de modification a bien été prise en compte';
    $url = esc_url(get_permalink(get_page_by_path('mes-demandes')));
    $success_message = "Les modifications ne sont pas immédiates. Vous pouvez voir le suivi du traitement de vos demandes dans <a class='aif-link--secondary' href='{$url}'> Mes demandes. </a>";
}

$show_pending_message = $has_pending_request && !$request_sent;

?>

<?php get_header(); ?>

<div class="aif-donor-space-layout">
	<?php echo render_block(['blockName' => 'core/pattern', 'attrs' => ['slug' => 'amnesty/my-space-sidebar'] ]); ?>
	<main class="aif-donor-space-content">
		<?php echo render_block(['blockName' => 'core/pattern', 'attrs' => ['slug' => 'amnesty/my-space-header'] ]); ?>
		<section class="aif-container--form">
			<header>
				<h1>Mes informations</h1>
				<h2>Mon iban</h2>
			</header>

			<?php if ($has_valid_mandate) :  ?>
				<p>Vous êtes <span class='aif-text-bold aif-uppercase'> <?php echo esc_html($user_status); ?> </span> d’Amnesty International France sous le numéro : <?php echo esc_html($SF_User->Identifiant_contact__c); ?> en prélèvement automatique avec une périodicité <span class='aif-lowercase'> <?php echo esc_html($actifMandate->Periodicite__c) ?> </span> d'un montant de <?php echo esc_html($actifMandate->Montant__c); ?> € le <?php echo esc_html($day_of_payment); ?> de chaque mois.</p>
			<?php endif ?>

			<?php
            if (!empty($error_message)) {
                $title = 'Une erreur est survenue';
                aif_include_partial('alert', [
                    'title' => $title,
                    'state' => 'error',
                    'content' => $error_message]);
            }

if ($show_pending_message) {
    aif_include_partial('alert', [
        'title' => 'Demande en cours',
        'content' => $pending_message]);
}

if (!empty($success_message)) {
    aif_include_partial('alert', [
        'title' => $success_message_title,
        'content' => $success_message,
        'state' => 'success']);
}
?>

			<form method="post" action="">
				<?php wp_nonce_field('iban_form', 'iban_nonce'); ?>
				<label for="iban">N° IBAN (obligatoire)</label>
				<input placeholder="FR 14 2001 0101 1505 0001 3M02" type="text" id="iban" name="iban"
					aria-labelledby="iban-help-message"
					data-initial-iban="<?php echo esc_attr($current_iban); ?>"
					value="<?php echo esc_attr($formattedIban); ?>"
					class="<?= $has_error ? 'aif-input-error' : '' ?>"
				/>

				<?php
    $url = esc_url(get_permalink(get_page_by_path('mes-demandes')));
$content = "Les modifications ne sont pas immédiates. Vous pouvez voir le suivi du traitement de vos demandes dans <a class='aif-link--secondary' href='{$url}'> Mes demandes. </a>";
aif_include_partial('info-message', [
    'id' => 'iban-help-message',
    'content' => $content]);
?>

				<button class="btn aif-mt1w aif-button--full" type="submit" id="iban-submit"<?php echo $has_pending_request ? ' disabled data-locked="1"' : ''; ?>>Enregistrer</button>
				<button class="btn btn--dark aif-mt1w aif-button--full" type="reset">Annuler</button>
			</form>
		</section>
	</main>
</div>

<?php get_footer(); ?>
