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

<div class="aif-grid-container aif-mt1w">

    <nav class="aif-flex aif-mr1w aif-lg-justify-end aif-container aif-mb1w" aria-label="menu retour a l'espace don">
        <a class=""
            href="<?= get_permalink(get_page_by_path('espace-don')) ?>">

            <svg class="" width="13" height="7" viewBox="0 0 13 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g id="Frame">
                    <path id="Vector" d="M3.5 1L3.9 1.4L2.2 3.2H12V3.8H2.2L3.9 5.6L3.5 6L1 3.5L3.5 1Z" fill="#2B2B2B" />
                </g>
            </svg>
            Revenir à mon espace don
        </a>
    </nav>

    <main class="aif-container">
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
                <?php foreach ($ibanBlocks as $index => $block): ?>
                <label for="ibanBlock<?php echo $index; ?>"
                    class="aif-sr-only">Bloc
                    <?php echo $index + 1; ?></label>
                <input type="text"
                    id="ibanBlock<?php echo $index; ?>"
                    name="ibanBlock[]"
                    value="<?php echo htmlspecialchars($block); ?>"
                    maxlength="4"
                    class="<?= $has_error ? 'aif-input-error' : '' ?>"
                    aria-label="Bloc <?php echo $index + 1; ?>" />
                <?php endforeach; ?>
            </div>
            <button class="btn btn--dark aif-mt1w" type="submit">Modifier IBAN</button>
        </form>


    </main>
    <div>
        <!-- Leave Empty -->
    </div>
</div>


<?php

get_footer();
?>