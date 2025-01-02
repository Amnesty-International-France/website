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

    <nav class="aif-flex aif-mr1w aif-justify-end" aria-label="menu retour a l'espace don">
        <a class="" href="<?= get_permalink(get_page_by_path('espace-donateur')) ?>">

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

            <?php foreach ($groupped as $year => $records): ?>

            <h3> <?=  $year ?> </h3>
            <?php foreach ($records as $record): ?>
            <?php $name = $record->Name  ?>

            <div class="aif-mb1w">
                <div id="aif-success-message-<?=$name?>" class="aif-bg-grey--lighter aif-p1w aif-mb1w aif-hide">
                    <div class="aif-flex aif-gap-single">
                        <div class="aif-text-green">
                            <svg aria-hidden="true" aria-hidden="true" width="4" height="14" viewBox="0 0 4 14"
                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M2 14C1.45 14 0.979167 13.8477 0.5875 13.5431C0.195833 13.2384 0 12.8722 0 12.4444C0 12.0167 0.195833 11.6505 0.5875 11.3458C0.979167 11.0412 1.45 10.8889 2 10.8889C2.55 10.8889 3.02083 11.0412 3.4125 11.3458C3.80417 11.6505 4 12.0167 4 12.4444C4 12.8722 3.80417 13.2384 3.4125 13.5431C3.02083 13.8477 2.55 14 2 14ZM0 9.33333V0H4V9.33333H0Z"
                                    fill="currentColor" />
                            </svg>
                        </div>

                        <p class="aif-text-green aif-mb0 aif-text-bold">Votre demande de duplicata a bien été
                            prise en compte. Vous le receverez d'ici quelques heures dans votre boite email</p>
                    </div>
                </div>
                <p class="aif-m0 aif-p0">Du <?=  date("d/m/Y", strtotime($record->Debut__c));  ?> au
                    <?=  date("d/m/Y", strtotime($record->Fin__c));  ?> -
                    <span class="aif-text-bold">
                        <?= $record->Montant_recu__c ?> € </span>
                </p>

                <p>
                    Numéro du reçu fiscal : <?= $name ?>
                </p>


                <button data-id="get-duplicate-tax-receipt-button"
                    onclick="createDuplicateTaxReceiptDemand('<?=$name?>');"
                    aria-label='Demander votre duplicata pour le reçu fiscal numéro <?= $name?>' class="btn btn--large">
                    Demander votre duplicata de reçu fiscal
                </button>
            </div>
            <?php endforeach ?>


            <?php endforeach ?>

            <?php else: ?>
            <p>Vous n'avez pas encore de reçu fiscaux</p>
            <?php endif; ?>
        </section>

    </main>

    <div>
        <!-- Leave Empty -->
    </div>
</div>


<?php

get_footer();
