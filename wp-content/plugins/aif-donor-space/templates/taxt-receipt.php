<?php

/* Template Name: Espace Donateur - Home */
get_header();

check_user_page_access();

$current_user = wp_get_current_user();
$sf_user_ID = get_SF_user_ID($current_user->ID);
$tax_reciept = get_salesforce_user_tax_reciept($sf_user_ID);

$sorted = [];
$groupped = [];

if ($tax_reciept->totalSize > 0) {
    $sorted = sortByDateProp($tax_reciept->records, "Debut__c");
    $groupped = groupByYear($sorted, "Debut__c");
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
            <h1 class="article-title wp-block-post-title">Mes reçus fiscaux</h1>
        </header>

        <p> Retrouvez dans cet espace tous vos reçus fiscaux. </p>

        <p>Pour information, les reçus fiscaux annuels seront disponibles à la fin du premier trimestre suivant
            l'année
            de vos dons. </p>

        <p>Pour toutes questions ou modifications sur vos dons et/ou adhésion,
            <a class="aif-text-underline aif-text-underline--orange " href="mailto:smd@amnesty.fr">contactez-nous
            </a>
        </p>


        <section>
            <h2> Historique de vos reçus fiscaux </h2>
            <?php if ($tax_reciept->totalSize > 0): ?>
            <?php $index = 0 ?>
   

            <?php foreach ($groupped as $year => $records): ?>
            <details  class="wp-block-details" <?= $index < 2 ? 'open' : '' ?>>
                <summary>
                    <h3> <?=  $year ?> </h3>

                </summary>

                <?php foreach ($records as $record): ?>
                <?php $name = $record->Name  ?>

                <div class="aif-mb1w">
                    <div id="aif-success-message-<?=$name?>"
                        class="aif-hide">
                        <?php
                        aif_include_partial("alert", [
                            "title" => "Votre demande à bien été prise en compte",
                        "content" => "Vous le receverez d'ici quelques heures dans votre boite email",
                    "success" => true]);

                    ?>
                    </div>
                    <p class="aif-m0 aif-p0">Du
                        <?=  date("d/m/Y", strtotime($record->Debut__c));  ?>
                        au
                        <?=  date("d/m/Y", strtotime($record->Fin__c));  ?>
                        -
                        <span class="aif-text-bold">
                            <?= $record->Montant_recu__c ?> € </span>
                    </p>

                    <p>
                        Numéro du reçu fiscal : <?= $name ?>
                    </p>


                    <button data-id="get-duplicate-tax-receipt-button"
                        onclick="createDuplicateTaxReceiptDemand('<?=$name?>');"
                        aria-label='Demander votre duplicata pour le reçu fiscal numéro <?= $name?>'
                        class="btn btn--large">
                        Demander votre duplicata de reçu fiscal
                    </button>
                </div>

                <?php endforeach ?>

            </details>

            <?php $index = $index + 1 ?>
            <?php endforeach ?>

            <?php else: ?>
            <p>Vous n'avez pas encore de reçu fiscaux</p>
            <?php endif; ?>

            <p>Les dons de plus de 5 ans et inférieurs à 7 € ne sont pas affichés dans cet espace. Le montant de votre
                abonnement au magazine la Chronique n’est pas déductible des impôts. Pour toutes autres demandes
                concernant vos reçus fiscaux, <a class="aif-text-underline aif-text-underline--orange"
                    title="contacter le service SMD d'Amnesty France" href="mailto:smd@amnesty.fr">contactez-nous </a>.
            </p>
        </section>

    </main>

    <div>
        <!-- Leave Empty -->
    </div>
</div>


<?php

get_footer();
?>