<?php

$current_user = wp_get_current_user();
$sf_user_ID = get_SF_user_ID($current_user->ID);
$tax_reciept = get_salesforce_user_tax_reciept($sf_user_ID);

print_r($sf_user_ID);

$sorted = [];
$groupped = [];

if($tax_reciept->totalSize > 0) {
    $sorted = sortByDateProp($tax_reciept->records, "Debut__c");
    $groupped = groupByYear($sorted, "Debut__c");
}

?>

<div class="wp-block-group is-layout-flow wp-block-group-is-layout-flow">


    <div class="aif-grid-container">

        <div>
            <!-- Leave Empty -->

        </div>

        <main class="aif-container">

            <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
                <h1 class="article-title wp-block-post-title">Mes reçus fiscaux</h1>
            </header>

            <p> Retrouvez dans cet espace tous vos reçus fiscaux. </p>

            <p>Pour information, les reçus fiscaux annuels seront disponibles à la fin du premier trimestre suivant
                l'année
                de vos dons. </p>

            <p>Pour toutes questions ou modifications sur vos dons et/ou adhésion, <a
                    class="aif-text-underline aif-text-underline--orange " href="mailto:smd@amnesty.fr">contactez-nous.
                </a>
            </p>


            <section>
                <h2> Historique de vos reçus fiscaux </h2>
                <?php if ($tax_reciept->totalSize > 0): ?>

                <?php foreach ($groupped as $year => $records): ?>

                <h3> <?=  $year ?> </h3>
                <?php foreach ($records as $record): ?>
                <div class="aif-mb1w">
                    <p class="aif-m0 aif-p0">Du <?=  date("d/m/Y", strtotime($record->Debut__c));  ?> au
                        <?=  date("d/m/Y", strtotime($record->Fin__c));  ?> -
                        <span class="aif-text-bold">
                            <?= $record->Montant_recu__c ?> € </span>
                    </p>

                    <p>
                        Numéro du reçu fiscal : <?= $record->Name ?>
                    </p>

                    <button onclick="createDuplicateTaxReceiptDemand(<?= $record->Name ?>);"
                        aria-label='Demander votre duplicatat de reçu fiscal pour le duplicata numéro <?= $record->Name ?>'
                        class="btn btn--large" data-param="<?=  esc_attr($record->Name); ?>">
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