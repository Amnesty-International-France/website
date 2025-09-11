<?php

$current_user = wp_get_current_user();
$sf_user_ID = get_SF_user_ID($current_user->ID);

$SF_User = get_salesforce_user_data($sf_user_ID);
$SF_membre_data = get_salesforce_member_data($current_user->user_email);
$SEPA_mandates = get_salesforce_user_SEPA_mandate($sf_user_ID);

$actifMandate  = null;
$day_of_payment = null;
$has_error = false;

$ibanBlocks = [];

$actifMandate = get_active_sepa_mandate($SEPA_mandates->records);

if ($actifMandate) {
    $day_of_payment = date('d', strtotime($actifMandate->Date_paiement_Avenir__c));
    $ibanBlocks = str_split($actifMandate->Tech_Iban__c, 4);
    $formattedIban = implode(' ', $ibanBlocks);
    $last4IBANDigit = substr($actifMandate->Tech_Iban__c, -4);
}

$user_status = aif_get_user_status($SF_membre_data);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['iban_nonce']) && isset($_POST['iban'])) {
    if (!isset($_POST['iban_nonce']) || !wp_verify_nonce($_POST['iban_nonce'], 'iban_form')) {
        die('Invalid nonce.');
    }

    $ibandirty = $_POST['iban'];
    $newIban = str_replace(' ', '', $ibandirty);

    if (create_duplicate_update_IBAN_request($sf_user_ID, $newIban)) {
        $success_message_title = 'Votre demande de modification a bien été prise en compte';

        $url = get_permalink(get_page_by_path('mes-demandes'));
        $success_message = "Les modifications ne sont pas immédiates. Vous pouvez voir le suivi du traitement de vos demandes dans <a class='aif-link--secondary' href='{$url}'> Mes demandes. </a>";
    } else {
        $error_message = 'Un problème technique est survenu. Merci de réessayer plus tard';
    }
}

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

			<?php if ($SF_membre_data->hasMandatActif) :  ?>
				<p><?= "Vous êtes <span class='aif-text-bold aif-uppercase'> {$user_status} </span> d’Amnesty International France sous le numéro : {$SF_User->Identifiant_contact__c} en prélèvement automatique avec une périodicité <span class='aif-lowercase'> {$actifMandate->Periodicite__c} </span> d'un montant de {$actifMandate->Montant__c} € le {$day_of_payment} de chaque mois." ?></p>
			<?php endif ?>

			<?php
            if (!empty($error_message)) {
                $title = 'Une erreur est survenue';
                aif_include_partial('alert', [
                    'title' => $title,
                    'state' => 'error',
                    'content' => $error_message]);
            }

if (!empty($success_message)) {
    $title = 'Une erreur est survenue';
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
					value="<?= $formattedIban ?>"
					class="<?= $has_error ? 'aif-input-error' : '' ?>"
				/>

				<?php
    $url = get_permalink(get_page_by_path('mes-demandes'));
$content = "Les modifications ne sont pas immédiates. Vous pouvez voir le suivi du traitement de vos demandes dans <a class='aif-link--secondary' href='{$url}'> Mes demandes. </a>";
aif_include_partial('info-message', [
    'id' => 'iban-help-message',
    'content' => $content]);
?>

				<button class="btn aif-mt1w aif-button--full" type="submit">Enregistrer</button>
				<button class="btn btn--dark aif-mt1w aif-button--full" type="reset">Annuler</button>
			</form>
		</section>
	</main>
</div>

<?php get_footer(); ?>
