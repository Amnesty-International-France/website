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
                            <svg width="16" height="15" viewBox="0 0 16 15" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M7.25 11.25H8.75V6.75H7.25V11.25ZM8 5.25C8.2125 5.25 8.39063 5.17813 8.53438 5.03438C8.67813 4.89062 8.75 4.7125 8.75 4.5C8.75 4.2875 8.67813 4.10938 8.53438 3.96563C8.39063 3.82188 8.2125 3.75 8 3.75C7.7875 3.75 7.60938 3.82188 7.46563 3.96563C7.32188 4.10938 7.25 4.2875 7.25 4.5C7.25 4.7125 7.32188 4.89062 7.46563 5.03438C7.60938 5.17813 7.7875 5.25 8 5.25ZM8 15C6.9625 15 5.9875 14.8031 5.075 14.4094C4.1625 14.0156 3.36875 13.4813 2.69375 12.8063C2.01875 12.1313 1.48438 11.3375 1.09063 10.425C0.696875 9.5125 0.5 8.5375 0.5 7.5C0.5 6.4625 0.696875 5.4875 1.09063 4.575C1.48438 3.6625 2.01875 2.86875 2.69375 2.19375C3.36875 1.51875 4.1625 0.984375 5.075 0.590625C5.9875 0.196875 6.9625 0 8 0C9.0375 0 10.0125 0.196875 10.925 0.590625C11.8375 0.984375 12.6313 1.51875 13.3063 2.19375C13.9813 2.86875 14.5156 3.6625 14.9094 4.575C15.3031 5.4875 15.5 6.4625 15.5 7.5C15.5 8.5375 15.3031 9.5125 14.9094 10.425C14.5156 11.3375 13.9813 12.1313 13.3063 12.8063C12.6313 13.4813 11.8375 14.0156 10.925 14.4094C10.0125 14.8031 9.0375 15 8 15ZM8 13.5C9.675 13.5 11.0938 12.9188 12.2563 11.7563C13.4188 10.5938 14 9.175 14 7.5C14 5.825 13.4188 4.40625 12.2563 3.24375C11.0938 2.08125 9.675 1.5 8 1.5C6.325 1.5 4.90625 2.08125 3.74375 3.24375C2.58125 4.40625 2 5.825 2 7.5C2 9.175 2.58125 10.5938 3.74375 11.7563C4.90625 12.9188 6.325 13.5 8 13.5Z"
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
