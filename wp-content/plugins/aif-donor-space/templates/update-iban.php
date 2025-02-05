<?php

/* Template Name: Espace Donateur - Home */
get_header();

check_user_page_access();

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

if($actifMandate) {
    $day_of_payment = date("d", strtotime($actifMandate->Date_paiement_Avenir__c));
    $ibanBlocks = str_split($actifMandate->Tech_Iban__c, 4);
    $last4IBANDigit = substr($actifMandate->Tech_Iban__c, -4);
}


$user_status = aif_get_user_status($SF_membre_data);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newIbanBlocks = $_POST['ibanBlock'];
    $newIban = implode('', $newIbanBlocks);

    if(isValidIBAN($newIban)) {
        if(create_duplicate_update_IBAN_request($sf_user_ID, $newIban)) {
            $success_message_title = "Votre demande de modification a bien été prise en compte";
            $success_message = 'Les modifications ne sont pas immédiates. Vous pouvez voir le suivi du traitement de vos demandes dans “Mes demandes”';
        } else {
            $error_message = "Un problème technique est survenu. Merci de réessayer plus tard";

        }

    } else {

        $error_message = "Votre IBAN semble invalide. Avez-vous bien vérifié ?";
        $has_error = true;
    }

}


?>



<main class="aif-container--main">

<section class="aif-container--form">
        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="article-title wp-block-post-title">Modification de mes informations bancaires</h1>
        </header>


        <?php if($actifMandate) :  ?>

        <p> <?= "Vous êtes <span class='aif-text-bold aif-uppercase'> {$user_status} </span> d’Amnesty International France sous le numéro : {$SF_User->Identifiant_contact__c} en prélèvement automatique avec une périodicité <span class='aif-lowercase'> {$actifMandate->Periodicite__c} </span> de {$actifMandate->Montant__c} € le {$day_of_payment} de chaque mois." ?>
        </p>
        <?php endif ?>

        <?php

if (!empty($error_message)) {
    $title = "Une erreur est survenue";
    aif_include_partial("alert", [
        "title" => $title,
        "state" => "error",
    "content" => $error_message]);

}

if (!empty($success_message)) {
    $title = "Une erreur est survenue";
    aif_include_partial("alert", [
        "title" => $success_message_title,
    "content" => $success_message,
"state" => "success"]);

}


?>



        <form method="post" action="">

            <div class="aif-flex aif-gap-single">
    
                <label for="ibanBlock<?php echo $index; ?>"
                    class="aif-sr-only">N° IBAN (obligatoire)
                    <?php echo $index + 1; ?></label>
                <input type="text"
                    id="ibanBlock<?php echo $index; ?>"
                    name="iban"
                    value="<?php echo htmlspecialchars($block); ?>"
                    maxlength="4"
                    class="<?= $has_error ? 'aif-input-error' : '' ?>"
                    aria-label="Bloc <?php echo $index + 1; ?>" />
          
            </div>
            <button class="btn aif-mt1w aif-button--full" type="submit">Enregistrer</button>
            <button class="btn btn--dark aif-mt1w aif-button--full" type="reset">Annuler</button>
        </form>

</section>


    </main>



<?php

get_footer();
?>