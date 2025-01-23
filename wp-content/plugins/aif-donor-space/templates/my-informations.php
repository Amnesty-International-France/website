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
$last4IBANDigit = null;

$ibanBlocks = [];

foreach ($SEPA_mandates->records as $mandate) {

    if ($mandate->Statut__c === "Actif") {
        $actifMandate = $mandate;
        $day_of_payment = date("d", strtotime($actifMandate->Date_paiement_Avenir__c));
        $last4IBANDigit = substr($mandate->Tech_Iban__c, -4);
        $ibanBlocks = str_split($mandate->Tech_Iban__c, 4);
    }
}
$user_status = aif_get_user_status($SF_membre_data);

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
            <h1 class="article-title wp-block-post-title">Mes informations</h1>
        </header>

      


        <?php if($actifMandate) :  ?>

        <section>
            
        <h2>Mes informations bancaires</h2>
     

        <p> <?= "Vous êtes <span class='aif-text-bold aif-uppercase'> {$user_status} </span> d’Amnesty International France sous le numéro : {$SF_User->Carte_membre__c} en prélèvement automatique avec une périodicité <span class='aif-lowercase'> {$actifMandate->Periodicite__c} </span> de {$actifMandate->Montant__c} € le {$day_of_payment} de chaque mois." ?>
        </p>

        <p> <?= "Prélèvement automatique sur l’IBAN se terminant par {$last4IBANDigit}" ?>
        </p>

        <p>
        <a href="<?= get_permalink(get_page_by_path('espace-don/modification-coordonnees-bancaire')) ?>" class="aif-text-underline"> Modifier l'IBAN</a>
        </p>
        </section>
        <?php endif ?>
    </main>
    <div>
        <!-- Leave Empty -->
    </div>
</div>


<?php

get_footer();
?>