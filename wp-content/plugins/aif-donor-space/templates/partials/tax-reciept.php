<?php
$current_user = wp_get_current_user();
$sf_user_ID = get_SF_user_ID($current_user->ID);

$tax_reciept = get_salesforce_user_taxt_reciept($sf_user_ID);
$sorted = sortByDateProp($tax_reciept->records, "Debut__c");
$groupped = groupByYear($sorted, "Debut__c");

print_r($groupped);

?>

<main class="wp-block-group is-layout-flow wp-block-group-is-layout-flow">


    <div class="container">


        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="article-title wp-block-post-title">Mes reçus fiscaux</h1>
        </header>

        <p> Retrouvez dans cet espace tous vos reçus fiscaux. </p>

        <p>Pour information, les reçus fiscaux annuels seront disponibles à la fin du premier trimestre suivant l'année
            de vos dons. </p>

        <p>Pour toutes questions ou modifications sur vos dons et/ou adhésion, <a
                class="aif-text-underline aif-text-underline--orange " href="mailto:smd@amnesty.fr">contactez-nous. </a>
        </p>


        <section>
            <h2> Historique de vos reçus fiscaux </h2>

            <?php foreach ($groupped as $year => $records): ?>
            <h3> <?=  $year ?> </h3>
            <?php foreach ($records as $record): ?>
            <p>Du <?=  $record->Debut__c ?> au <?php  echo $record->Fin__c ?> - <span class="aif-text-bold">
                    <?=   $record->Montant_recu__c ?> € </span>
            </p>
            <?php endforeach ?>
            <?php endforeach ?>

        </section>



    </div>

</main>